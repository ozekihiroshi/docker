<?php
session_start();
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../lib/ldap_common.php");
require_once(__DIR__ . "/../lib/session_common.php");
require_once(__DIR__ . '/../vendor/smarty/smarty/libs/Smarty.class.php');

$excluded_usernames = include(__DIR__ . '/../lib/excluded_users.php');

check_session_timeout();
validate_csrf_token($_POST['csrf_token'] ?? '');

$version = "1.0.0";
$inactive_users = [];

$days = isset($_POST['days']) ? intval($_POST['days']) : 180;
$user_type = $_POST['user_type'] ?? 'staff';

try {
    $ldap_conn = ldap_connect_server($user_type);
    ldap_bind_admin($ldap_conn);

    $base_dn = $ldap_servers[$user_type]['base_dn'];
    $windowsTime = time() - ($days * 86400);
    $windowsTime = ($windowsTime + 11644473600) * 10000000;

    $filter = "(&(objectClass=user)(lastLogonTimestamp<=$windowsTime)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";

    $attributes = ['sAMAccountName', 'dn', 'lastLogonTimestamp', 'whenCreated'];
    $result = ldap_search($ldap_conn, $base_dn, $filter, $attributes);
    $entries = ldap_get_entries($ldap_conn, $result);
    $count = $entries['count'] ?? 0;

    for ($i = 0; $i < $count; $i++) {
        $username = $entries[$i]['samaccountname'][0] ?? '';
        if (str_ends_with($username, '$')) continue;
	if (in_array(strtolower($username), array_map('strtolower', $excluded_usernames))) {
        continue;
        }
        $dn = $entries[$i]['dn'];
        $lastLogonTimestamp = $entries[$i]['lastlogontimestamp'][0] ?? 0;
        $lastLogonLocal = $lastLogonTimestamp > 0
            ? gmdate("Y-m-d H:i:s", intval($lastLogonTimestamp / 10000000 - 11644473600))
            : 'Never';

        $whenCreatedRaw = $entries[$i]['whencreated'][0] ?? '';
        $whenCreatedLocal = '';
        if ($whenCreatedRaw !== '') {
            $dt = DateTime::createFromFormat('YmdHis.0Z', $whenCreatedRaw, new DateTimeZone('UTC'));
            if ($dt !== false) {
                $dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
                $whenCreatedLocal = $dt->format('Y-m-d H:i:s');
            }
        }

        $inactive_users[] = [
            'username' => $username,
            'dn' => $dn,
            'last_logon_windows' => $lastLogonTimestamp,
            'last_logon_local' => $lastLogonLocal,
            'created_local' => $whenCreatedLocal,
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
$smarty->assign("inactive_users", $inactive_users);
$smarty->assign("csrf_token", $_SESSION['csrf_token']);
$smarty->assign("admin_user", $_SESSION['admin_user'] ?? '');
$smarty->assign("loggedIn", true);
$smarty->assign("error", $error ?? '');

$smarty->display("inactive_users.tpl");

