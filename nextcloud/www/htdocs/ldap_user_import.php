<?php
<?php
session_start();
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/vendor/autoload.php");
use PhpOffice\PhpSpreadsheet\IOFactory;

// **OU・セキュリティグループ・共有フォルダ情報**
$ou_mappings = [
    "Admins" => ["OU=Admins,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw", "CN=Admins,OU=Admins,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"],
    "BUSINESS" => ["OU=BUSINESS,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw", "CN=BUSINESS,OU=BUSINESS,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"],
    "ELECTRICAL" => ["OU=ELECTRICAL,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw", "CN=ELECTRICAL AND ELECTRONICS ENGINEERING,OU=ELECTRICAL,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"],
    "ICT" => ["OU=ICT,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw", "CN=INFORMATION COMMUNICATIONS TECHNOLOGY,OU=ICT,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"],
    "HOSPITALITY" => ["OU=HOSPITALITY,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw", "CN=HOSPITALITY AND TOURISM,OU=HOSPITALITY,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"],
    "SPECIAL NEEDS" => ["OU=SPECIAL NEEDS,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw", "CN=SPECIAL NEEDS,OU=SPECIAL NEEDS,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"]
];

// **LDAP接続情報**
$ldap_url = "ldaps://staffdc2.gtc.ce.ac.bw:636";
$ldap_binddn = "CN=Administrator,CN=Users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
$ldap_bindpw = "Password1";

$ldap_conn = ldap_connect($ldap_url);
ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

if (!@ldap_bind($ldap_conn, $ldap_binddn, $ldap_bindpw)) {
    die("LDAP bind failed: " . ldap_error($ldap_conn));
}

// **Excelファイルの処理**
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["excel_file"])) {
    $file = $_FILES["excel_file"]["tmp_name"];
    if (!is_uploaded_file($file)) {
        die("File upload failed.");
    }

    // **Excelを読み込む**
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true); // A~F列のデータ取得

    $results = [];
    foreach ($rows as $index => $data) {
        if ($index == 1) continue; // **ヘッダーをスキップ**

        $sAMAccountName = trim($data['A']);
        $givenName = trim($data['B']);
        $sn = trim($data['C']);
        $mail = trim($data['D']);
        $department = trim($data['E']);
        $password = trim($data['F']);

        if (!$sAMAccountName || !$givenName || !$sn || !$mail || !$password) {
            $results[] = ["username" => $sAMAccountName, "status" => "Failed", "message" => "Missing required fields"];
            continue;
        }

        // **OU・セキュリティグループを決定**
        if (!isset($ou_mappings[$department])) {
            $results[] = ["username" => $sAMAccountName, "status" => "Failed", "message" => "Invalid department"];
            continue;
        }
        [$ou, $securityGroup] = $ou_mappings[$department];

        // **ユーザーDN作成**
        $user_dn = "CN=$givenName $sn,$ou";

        // **ユーザー属性設定**
        $entry = [
            "cn" => "$givenName $sn",
            "givenName" => $givenName,
            "sn" => $sn,
            "sAMAccountName" => $sAMAccountName,
            "userPrincipalName" => "$sAMAccountName@staff.gtc.ce.ac.bw",
            "mail" => $mail,
            "department" => $department,
            "objectClass" => ["top", "person", "organizationalPerson", "user"],
            "userAccountControl" => "544", // **パスワード変更必須**
            "memberOf" => $securityGroup, // **セキュリティグループ追加**
            "homeDirectory" => "\\\\Nas\\$department\\$sAMAccountName",
            "homeDrive" => "P:"
        ];

        // **パスワード設定（UTF-16LE変換）**
        $entry["unicodePwd"] = iconv("UTF-8", "UTF-16LE", '"' . $password . '"');

        // **LDAPに追加**
        if (@ldap_add($ldap_conn, $user_dn, $entry)) {
            $results[] = ["username" => $sAMAccountName, "status" => "Success", "message" => "User added."];
        } else {
            $results[] = ["username" => $sAMAccountName, "status" => "Failed", "message" => ldap_error($ldap_conn)];
        }
    }

    ldap_close($ldap_conn);
}

// **Smartyで結果を表示**
require_once(SMARTY);
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

$smarty->assign('results', $results);
$smarty->display('ldap_user_import.tpl');
?>
