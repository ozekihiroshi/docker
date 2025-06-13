<?php
session_start();
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../lib/ldap_common.php");
require_once(__DIR__ . "/../lib/session_common.php");
require_once(__DIR__ . '/../vendor/smarty/smarty/libs/Smarty.class.php');

check_session_timeout();
validate_csrf_token($_POST['csrf_token'] ?? '');

$version = "1.0.0";
$success_enabled = [];
$failed_enabled = [];

$dns = $_POST['dns'] ?? [];
$user_type = $_POST['user_type'] ?? 'staff';

try {
    $ldap_conn = ldap_connect_server($user_type);
    ldap_bind_admin($ldap_conn);

    foreach ($dns as $dn) {
        $search = ldap_read($ldap_conn, $dn, '(objectClass=*)');
        $entries = ldap_get_entries($ldap_conn, $search);

        if ($entries['count'] > 0) {
            $entry = $entries[0];
            $username = $entry['samaccountname'][0] ?? '(unknown)';
            $mods = [];
            if (isset($entry['useraccountcontrol'][0])) {
                $currentFlags = (int)$entry['useraccountcontrol'][0];
                $newFlags = $currentFlags & ~2; // remove DISABLED flag (0x2)
                $mods['userAccountControl'] = [$newFlags];
            } else {
                $mods['userAccountControl'] = [512]; // NORMAL_ACCOUNT
            }

            if (ldap_modify($ldap_conn, $dn, $mods)) {
                $success_enabled[] = $dn;
            } else {
                $failed_enabled[] = ["dn" => $dn, "error" => ldap_error($ldap_conn)];
            }
        } else {
            $failed_enabled[] = ["dn" => $dn, "error" => "Entry not found in LDAP."];
        }
    }

    ldap_close($ldap_conn);
} catch (Exception $e) {
    $failed_enabled[] = ["dn" => "(general)", "error" => $e->getMessage()];
}

$smarty = new Smarty\Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

$smarty->assign("success_enabled", $success_enabled);
$smarty->assign("failed_enabled", $failed_enabled);
$smarty->assign("version", $version);
$smarty->display("enable_users_result.tpl");

