<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../conf/ldap_config.php");
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . '/../lib/ldap_common.php');

$error = '';

if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error = "Your session has timed out. Please log in again.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $ldap_conn = ldap_connect("ldaps://staffdc2.gtc.ce.ac.bw:636");
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        if (!$ldap_conn) {
            throw new Exception("Failed to connect to LDAP server.");
        }

        $admin_binddn = "CN=Administrator,CN=Users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
        $admin_password = "Password1";

        if (!@ldap_bind($ldap_conn, $admin_binddn, $admin_password)) {
            throw new Exception("Admin LDAP bind failed: " . ldap_error($ldap_conn));
        }

        $base_dn = "DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
        $search_filter = "(sAMAccountName=$username)";
        $search_result = ldap_search($ldap_conn, $base_dn, $search_filter);

        if (!$search_result) {
            throw new Exception("LDAP search failed: " . ldap_error($ldap_conn));
        }

        $entries = ldap_get_entries($ldap_conn, $search_result);

        if ($entries["count"] > 0) {
            $user_dn = $entries[0]["dn"];

            // 本人パスワードで認証試行
            if (@ldap_bind($ldap_conn, $user_dn, $password)) {

                // 【ここでAdminsグループ所属を確認する】
                if (isset($entries[0]['memberof'])) {
                    $memberof = $entries[0]['memberof'];

                    $isAdmin = false;
                    for ($i = 0; $i < $memberof['count']; $i++) {
                        if (stripos($memberof[$i], "CN=ADMINS,OU=ADMINS") !== false) {
                            $isAdmin = true;
                            break;
                        }
                    }

                    if ($isAdmin) {
                        $_SESSION['admin_user'] = $username;
                        header("Location: admin_dashboard.php");
                        exit();
                    } else {
                        $error = "Access denied. You are not an administrator.";
                    }

                } else {
                    $error = "Access denied. No group membership information.";
                }

            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "User not found.";
        }

    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    } finally {
        if (isset($ldap_conn) && $ldap_conn) {
            ldap_close($ldap_conn);
        }
    }
}

// エラーメッセージ表示
$smarty = new Smarty\Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->assign('error', $error ?? '');
$smarty->display('admin_login.tpl');
