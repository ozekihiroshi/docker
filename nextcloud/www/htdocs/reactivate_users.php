<?php
session_start();
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../lib/ldap_common.php");
require_once(__DIR__ . "/../lib/db_common.php");
require_once(__DIR__ . "/../lib/session_common.php");
require_once(__DIR__ . "/../vendor/smarty/smarty/libs/Smarty.class.php");

$excluded_usernames = include(__DIR__ . '/../lib/excluded_users.php');

check_session_timeout();
validate_csrf_token($_POST['csrf_token'] ?? '');

$version = "1.0.0";
$disabled_users = [];
$error = '';

$days = isset($_POST['days']) ? intval($_POST['days']) : 180;
$user_type = $_POST['user_type'] ?? 'staff';

try {
    $ldap_conn = ldap_connect_server($user_type);
    ldap_bind_admin($ldap_conn);

    $base_dn = $ldap_servers[$user_type]['base_dn'];
    $windowsTime = time() - ($days * 86400);
    $windowsTime = ($windowsTime + 11644473600) * 10000000;

    // LDAP filter for disabled users (userAccountControl:ACCOUNTDISABLE bit set)
    $filter = "(&(objectClass=user)(userAccountControl:1.2.840.113556.1.4.803:=2))";
    $attributes = ['sAMAccountName', 'dn', 'whenCreated', 'userAccountControl', 'lastLogonTimestamp'];
    $result = ldap_search($ldap_conn, $base_dn, $filter, $attributes);
    $entries = ldap_get_entries($ldap_conn, $result);

    //$excluded_usernames = $excluded_usernames_config[$user_type] ?? [];

    for ($i = 0; $i < $entries['count']; $i++) {
        $username = $entries[$i]['samaccountname'][0] ?? '';
        $dn = $entries[$i]['dn'] ?? '';

        if (str_ends_with($username, '$') || in_array(strtolower($username), $excluded_usernames)) {
            continue;
        }

        $whenCreatedRaw = $entries[$i]['whencreated'][0] ?? '';
        $created_local = '';
        if ($whenCreatedRaw !== '') {
            $dt = DateTime::createFromFormat('YmdHis.0Z', $whenCreatedRaw, new DateTimeZone('UTC'));
            if ($dt !== false) {
                $dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
                $created_local = $dt->format('Y-m-d H:i:s');
            }
        }

        $disabled_users[] = [
		'username' => $username,
                'dn' => $dn,
                'user_type' => $user_type,
                'disabled_at' => '', 
                'created_local' => $created_local,
        ];
    }

    ldap_close($ldap_conn);
} catch (Exception $e) {
    $error = $e->getMessage();
}

$smarty = new Smarty\Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

$smarty->assign("version", $version);
$smarty->assign("days", $days);
$smarty->assign("user_type", $user_type);
$smarty->assign("disabled_users", $disabled_users);
$smarty->assign("csrf_token", $_SESSION['csrf_token']);
$smarty->assign("admin_user", $_SESSION['admin_user'] ?? '');
$smarty->assign("loggedIn", true);
$smarty->assign("error", $error);

$smarty->display("reactivate_users.tpl");

