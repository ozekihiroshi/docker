<?php
session_start();
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../lib/ldap_common.php");
require_once(__DIR__ . "/../lib/db_common.php");
require_once(__DIR__ . "/../lib/session_common.php");
require_once(__DIR__ . '/../vendor/smarty/smarty/libs/Smarty.class.php');

check_session_timeout();

$version = "1.0.0";
$success_deleted = [];
$failed_deleted = [];

$dns = $_POST['dns'] ?? [];
$usernames = $_POST['usernames'] ?? [];
$user_type = $_POST['user_type'] ?? 'staff';
$confirm = $_POST['confirm'] ?? '';

$smarty = new Smarty\Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

function sanitize_ldap_entry($entry) {
    $clean = [];
    foreach ($entry as $key => $value) {
        if (is_array($value)) {
            $clean[$key] = [];
            foreach ($value as $k => $v) {
                if (is_string($v) || is_numeric($v)) {
                    $clean[$key][$k] = $v;
                } else {
                    $clean[$key][$k] = base64_encode($v); // binary-safe
                }
            }
        } elseif (is_string($value) || is_numeric($value)) {
            $clean[$key] = $value;
        } else {
            $clean[$key] = base64_encode((string)$value);
        }
    }
    return $clean;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $confirm === 'yes' && !empty($dns)) {
    validate_csrf_token($_POST['csrf_token'] ?? '');

    try {
        $ldap_conn = ldap_connect_server($user_type);
        ldap_bind_admin($ldap_conn);
        $pdo = get_database_connection();

        foreach ($dns as $dn) {
            $search = ldap_read($ldap_conn, $dn, '(objectClass=*)');
            $entries = ldap_get_entries($ldap_conn, $search);

            if ($entries['count'] > 0) {
                $entry = $entries[0];
                $username = $entry['samaccountname'][0] ?? $usernames[$dn] ?? '(unknown)';
                $clean_entry = sanitize_ldap_entry($entry);
		$attributes = json_encode($entry, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_IGNORE);
                if ($attributes === false || !json_validate($attributes)) {
                    $attributes = json_encode(['error' => 'Invalid LDAP data']);
                }

                if (ldap_delete($ldap_conn, $dn)) {
                    $stmt = $pdo->prepare("INSERT INTO deleted_users (username, dn, user_type, deleted_by, attributes) VALUES (?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $username,
                        $dn,
                        $user_type,
                        $_SESSION['admin_user'] ?? 'unknown',
                        $attributes
                    ]);
                    if (!$result) {
                        $failed_deleted[] = ["dn" => $dn, "error" => implode(', ', $stmt->errorInfo())];
                    } else {
                        $success_deleted[] = $dn;
                    }
                } else {
                    $failed_deleted[] = ["dn" => $dn, "error" => ldap_error($ldap_conn)];
                }
            } else {
                $failed_deleted[] = ["dn" => $dn, "error" => "Entry not found in LDAP."];
            }
        }

        ldap_close($ldap_conn);
    } catch (Exception $e) {
        $failed_deleted[] = ["dn" => "(general)", "error" => $e->getMessage()];
    }

    $smarty->assign("success_deleted", $success_deleted);
    $smarty->assign("failed_deleted", $failed_deleted);
    $smarty->assign("version", $version);
    $smarty->display("delete_users_result.tpl");
    exit;
}

// 初回アクセス：モーダル表示の準備
$delete_candidates = [];
foreach ($dns as $dn) {
    $delete_candidates[] = [
        'dn' => $dn,
        'username' => $usernames[$dn] ?? '(unknown)',
        'user_type' => $user_type,
    ];
}

$smarty->assign("delete_candidates", $delete_candidates);
$smarty->assign("dns", $dns);
$smarty->assign("usernames", $usernames);
$smarty->assign("user_type", $user_type);
$smarty->assign("csrf_token", $_SESSION['csrf_token']);
$smarty->assign("version", $version);
$smarty->display("delete_users_confirm.tpl");

function json_validate($string) {
    json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
}
