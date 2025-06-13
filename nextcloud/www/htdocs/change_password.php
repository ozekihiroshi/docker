<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . '/../lib/ldap_common.php');  
require_once(__DIR__ . "/../lib/session_common.php");

#==============================================================================#
# Variables                                                                   #
#==============================================================================#
$version = "1.7.2"; // システムのバージョン
require_once(__DIR__ . '/../conf/ldap_config.php');

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token. Request rejected.");
    }

    $username = $_POST["username"];
    $user_type = $_POST["user_type"];
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // パスワード一致チェック
    if ($new_password !== $confirm_password) {
        $error = "Error: The new passwords do not match.";
    } else {

        $ldap_servers = [
           "staff" => ["url" => "ldaps://staffdc2.gtc.ce.ac.bw", "base_dn" => "DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"],
           "students" => ["url" => "ldaps://studentsdc2.gtc.ce.ac.bw", "base_dn" => "DC=students,DC=gtc,DC=ce,DC=ac,DC=bw"]];

        try {
            $ldap_connection = ldap_connect_server($user_type);  // staff or students
            ldap_bind_admin($ldap_connection);

            $base_dn = $ldap_servers[$user_type]['base_dn'];  // LDAPベースDN
            $user_dn = ldap_get_user_dn($ldap_connection, $base_dn, $username);

            ldap_change_password($ldap_connection, $user_dn, $new_password);
            $success = "Password changed successfully!";
        } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        } finally {
            if (isset($ldap_connection) && $ldap_connection) {
                ldap_close($ldap_connection);
            }
        }

    }
}

#==============================================================================#
# Smarty Setup                                                                #
#==============================================================================#

$compile_dir = isset($smarty_compile_dir) ? $smarty_compile_dir : "../templates_c/";
$cache_dir = isset($smarty_cache_dir) ? $smarty_cache_dir : "../cache/";

$smarty = new Smarty\Smarty();
$smarty->escape_html = true;
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir($compile_dir);
$smarty->setCacheDir($cache_dir);
//$smarty->debugging = $smarty_debug;

/*
if ($smarty_debug) {
    $smarty->error_reporting = E_ALL;
} else {
    $smarty->error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING;
}
*/

#==============================================================================#
# Assign Smarty Variables                                                      #
#==============================================================================#
$smarty->assign('csrf_token', generate_csrf_token());
$smarty->assign('version', $version);
//$smarty->assign('lang', $lang);
$smarty->assign('custom_css', isset($custom_css) ? $custom_css : '');
$smarty->assign('background_image', isset($background_image) ? $background_image : '');
$smarty->assign('display_footer', isset($display_footer) ? $display_footer : true);
$smarty->assign('captcha_css', isset($captcha_css) ? $captcha_css : '');
$smarty->assign('captcha_js', isset($captcha_js) ? $captcha_js : '');
$smarty->assign('questions_count', isset($questions_count) ? $questions_count : 0);

#==============================================================================#
# Display Template                                                             #
#==============================================================================#

// Smarty にメッセージを渡して表示
$smarty->assign("error", $error);
$smarty->assign("success", $success);
$smarty->display("change_password.tpl");

