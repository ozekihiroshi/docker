<?php
session_start();
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../lib/ldap_common.php");
require_once(__DIR__ . "/../lib/session_common.php");
require_once(__DIR__ . '/../vendor/smarty/smarty/libs/Smarty.class.php');

check_session_timeout();
validate_csrf_token($_POST['csrf_token'] ?? '');

$disabled = [];
$errors = [];

if (!empty($_POST['disable_dns']) && is_array($_POST['disable_dns'])) {
    foreach ($_POST['disable_dns'] as $dn) {
        try {
            $user_type = detect_user_type_from_dn($dn);
            $ldap_conn = ldap_connect_server($user_type);
            ldap_bind_admin($ldap_conn);

            $search = ldap_read($ldap_conn, $dn, "(objectClass=*)", ['userAccountControl']);
            $entries = ldap_get_entries($ldap_conn, $search);

            if ($entries['count'] === 0) {
                $errors[] = "No entries returned for $dn";
                continue;
            }

            $current_uac = (int)$entries[0]['useraccountcontrol'][0];
            $disabled_uac = $current_uac | 2;

            $success = ldap_modify($ldap_conn, $dn, ["userAccountControl" => [$disabled_uac]]);
            if ($success) {
                $disabled[] = $dn;
            } else {
                $errors[] = "Failed to disable $dn";
            }

            ldap_close($ldap_conn);
        } catch (Exception $e) {
            $errors[] = "Error processing $dn: " . $e->getMessage();
        }
    }
}

$smarty = new Smarty\Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

$smarty->assign("disabled", $disabled);
$smarty->assign("errors", $errors);
$smarty->display("disable_users.tpl");

function detect_user_type_from_dn($dn) {
    return (strpos(strtolower($dn), 'dc=students') !== false) ? 'students' : 'staff';
}

