<?php
session_start();
require_once(__DIR__ . "/conf/config.inc.php");
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../conf/ldap_config.php");
require_once(__DIR__ . "/../lib/session_common.php");
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../lib/ldap_common.php");

use PhpOffice\PhpSpreadsheet\IOFactory;

check_session_timeout();

$results = [];
$excel_data = [];

// --- 共通マッピング読み込み関数 ---
function load_mappings($csv_path)
{
    $map = [];
    if (!file_exists($csv_path)) return $map;

    if (($handle = fopen($csv_path, "r")) !== false) {
        $header = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== false) {
            list($ou, $subOu, $shareFolder, $group) = array_map('trim', $data);
            $key = strtoupper($ou . ($subOu ? "|" . $subOu : ""));
            $map[$key] = [
                'shareFolder' => $shareFolder,
                'group' => $group
            ];
        }
        fclose($handle);
    }
    return $map;
}

// --- グループ名取得関数 ---
function getGroupName($map, $department, $subDepartment = '')
{
    $key = strtoupper($department . ($subDepartment ? "|" . $subDepartment : ""));
    return $map[$key]['group'] ?? null;
}

// --- 共有フォルダ取得関数 ---
function getShareFolder($map, $department, $subDepartment = '')
{
    $key = strtoupper($department . ($subDepartment ? "|" . $subDepartment : ""));
    return $map[$key]['shareFolder'] ?? null;
}

// Excelファイル処理
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["excel_file"])) {
    $file = $_FILES["excel_file"]["tmp_name"];
    if (!is_uploaded_file($file)) {
        die("File upload failed.");
    }

    $userType = strtolower(trim($_POST['userType'] ?? 'students'));

    $mapping_csv = $mapping_csv_files[$userType] ?? null;

    if (!$mapping_csv || !file_exists($mapping_csv)) {
        die("Mapping CSV file not found for user type: $userType");
    }

    $mapping = load_mappings($mapping_csv);

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    // LDAP接続
    $server = $ldap_servers[$userType] ?? null;
    if (!$server) {
        die("Invalid user type or missing LDAP config for: $userType");
    }

    $ldap_url    = $server['url'];
    $ldap_binddn = $server['bind_dn'];
    $ldap_bindpw = $server['bind_pw'];
    $search_base = $server['base_dn'];

//    if ($userType === 'staff') {
//        $ldap_url = "ldaps://staffdc2.gtc.ce.ac.bw:636";
//        $ldap_binddn = "CN=Administrator,CN=Users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
//        $search_base = "DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
//    } else {
//        $ldap_url = "ldaps://studentsdc2.gtc.ce.ac.bw:636";
//        $ldap_binddn = "CN=Administrator,CN=Users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
//        $search_base = "DC=students,DC=gtc,DC=ce,DC=ac,DC=bw";
//    }

    $ldap_conn = ldap_connect($ldap_url);
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    if (!@ldap_bind($ldap_conn, $ldap_binddn, $ldap_bindpw)) {
        die("LDAP bind failed: " . ldap_error($ldap_conn));
    }

    foreach ($rows as $index => $data) {
        if ($index === 1) continue; // ヘッダー行

        $sAMAccountName = trim($data['A'] ?? '');
        $givenName      = trim($data['B'] ?? '');
        $sn             = trim($data['C'] ?? '');
        $department     = trim($data['D'] ?? '');
        $subDepartment  = trim($data['E'] ?? '');
        $password       = trim($data['F'] ?? '');

        if (empty($sAMAccountName)) {
            $base_name = strtolower(substr($givenName, 0, 1) . $sn);
        } else {
            $base_name = strtolower($sAMAccountName);
        }

        $final_name = $base_name;
        $count = 0;
        do {
            $check_name = ($count === 0) ? $final_name : $base_name . $count;
            $filter = "(sAMAccountName=$check_name)";
            $search = @ldap_search($ldap_conn, $search_base, $filter);
            $entries = @ldap_get_entries($ldap_conn, $search);
            if ($entries === false || $entries['count'] === 0) break;
            $count++;
        } while (true);

        $sAMAccountName = ($count === 0) ? $final_name : $base_name . $count;

        if (!$sAMAccountName || !$givenName || !$sn || !$password || !$department) {
            $results[] = ["username" => $sAMAccountName, "status" => "Failed", "message" => "Missing fields"];
            continue;
        }

        $shareFolder = getShareFolder($mapping, $department, $subDepartment);
        $group_cn = getGroupName($mapping, $department, $subDepartment);

        if ($userType === 'staff') {
            $ou_dn = "OU=$department,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
            $homeDirectory = "\\\\Nas\\$shareFolder\\$sAMAccountName";
            $mail = "$sAMAccountName@gtc.ce.ac.bw";
        } else {
            $ou_dn = $subDepartment ? "OU=$subDepartment,OU=$department,DC=students,DC=gtc,DC=ce,DC=ac,DC=bw"
                                    : "OU=$department,DC=students,DC=gtc,DC=ce,DC=ac,DC=bw";
            $homeDirectory = "\\\\Nas\\$shareFolder\\$sAMAccountName";
            $mail = "$sAMAccountName@students.gtc.ce.ac.bw";
        }

        $userPrincipalName = $mail;
        $user_dn = "CN=$givenName $sn,$ou_dn";

        $entry = [
            "cn" => "$givenName $sn",
            "givenName" => $givenName,
            "sn" => $sn,
            "sAMAccountName" => $sAMAccountName,
            "userPrincipalName" => $userPrincipalName,
            "mail" => $mail,
            "department" => $department,
            "objectClass" => ["top", "person", "organizationalPerson", "user"],
            "userAccountControl" => "544",
            "pwdLastSet" => 0,
            "homeDirectory" => $homeDirectory,
            "homeDrive" => "P:",
            "unicodePwd" => iconv("UTF-8", "UTF-16LE", '"' . $password . '"')
        ];

        if (@ldap_add($ldap_conn, $user_dn, $entry)) {
            $results[] = ["username" => $sAMAccountName, "status" => "Success", "message" => "User added"];
        } else {
            $results[] = ["username" => $sAMAccountName, "status" => "Failed", "message" => ldap_error($ldap_conn)];
            continue;
        }

        // グループ追加
        if (!empty($group_cn)) {
            $group_dn = $subDepartment
                ? "CN=$group_cn,OU=$subDepartment,OU=$department,DC=$userType,DC=gtc,DC=ce,DC=ac,DC=bw"
                : "CN=$group_cn,OU=$department,DC=$userType,DC=gtc,DC=ce,DC=ac,DC=bw";

            $member_entry = ["member" => $user_dn];
            if (!@ldap_mod_add($ldap_conn, $group_dn, $member_entry)) {
                $results[] = ["username" => $sAMAccountName, "status" => "Warning", "message" => "Group add failed: " . ldap_error($ldap_conn)];
            }
        }

        $excel_data[] = [
            'username' => $sAMAccountName,
            'givenName' => $givenName,
            'sn' => $sn,
            'department' => $department,
            'subDepartment' => $subDepartment,
            'sAMAccountName' => $sAMAccountName,
            'status' => 'Success',
            'message' => 'User added & activated.'
        ];
    }

    ldap_close($ldap_conn);

    $timestamp = date('Ymd_His');
    $csv_filename = "created_accounts_{$timestamp}.csv";
    $csvPath = __DIR__ . "/exports/{$csv_filename}";
    $csvFile = fopen($csvPath, 'w');
    fputcsv($csvFile, ['Username', 'Given Name', 'Surname', 'Department', 'SubDepartment', 'Account', 'Status', 'Message']);
    foreach ($excel_data as $res) {
        fputcsv($csvFile, [
            $res['username'],
            $res['givenName'],
            $res['sn'],
            $res['department'],
            $res['subDepartment'],
            $res['sAMAccountName'],
            $res['status'],
            $res['message']
        ]);
    }
    fclose($csvFile);
    $csv_download_link = "exports/{$csv_filename}";
}

$smarty = new Smarty\Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->assign('csrf_token', generate_csrf_token());
$smarty->assign('results', $results);
$smarty->assign('csv_download_link', $csv_download_link ?? '');
$smarty->assign('admin_user', $_SESSION['admin_user'] ?? '');
$smarty->assign('loggedIn', $loggedIn ?? true);
$smarty->display('ldap_user_import.tpl');

