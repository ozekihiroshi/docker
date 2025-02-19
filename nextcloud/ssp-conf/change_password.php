<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
                                                                                                                        #==============================================================================#
# Configuration                                                               #
#==============================================================================#
require_once(__DIR__ . "/../conf/config.inc.php");

#==============================================================================#
# Includes                                                                    #
#==============================================================================#
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../lib/functions.inc.php");

#==============================================================================#
# Variables                                                                   #
#==============================================================================#
$version = "1.7.2"; // システムのバージョン
session_start();
require '../conf/ldap_config.php';  // LDAP設定ファイル

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
        // LDAP サーバーへ接続
	$ldap_connection = ldap_connect($ldap_servers[$user_type]["url"]);
        //$ldap_connection = ldap_connect("ldaps://staffdc2.gtc.ce.ac.bw");
        ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0);

    // バインド (管理者権限)
    $ldap_binddn = "CN=Administrator,CN=Users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
    $ldap_bindpw = "Password1";  // 管理者のパスワード
    $base_dn = $ldap_servers[$user_type]["base_dn"];
    if (@ldap_bind($ldap_connection, $ldap_binddn, $ldap_bindpw)) {
        // ユーザーの DN を検索
        $search_filter = "(sAMAccountName={$username})";
        $search_result = ldap_search($ldap_connection, $base_dn, $search_filter);
        $entries = ldap_get_entries($ldap_connection, $search_result);
        if ($entries["count"] > 0) {
            $user_dn = $entries[0]["dn"];
             echo "User DN: $user_dn<br>";  // ログ出力
            $new_password_utf16 = iconv('UTF-8', 'UTF-16LE', '"' . $new_password . '"');
            $password_entry = ["unicodePwd" => $new_password_utf16];

            if (ldap_modify($ldap_connection, $user_dn, $password_entry)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password.";
            }
        } else {
            $error = "Current password is incorrect.";
        }

        ldap_close($ldap_connection);
    }
}
}

#==============================================================================#
# Smarty Setup                                                                #
#==============================================================================#
require_once(SMARTY);

$compile_dir = isset($smarty_compile_dir) ? $smarty_compile_dir : "../templates_c/";
$cache_dir = isset($smarty_cache_dir) ? $smarty_cache_dir : "../cache/";

$smarty = new Smarty();
$smarty->escape_html = true;
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir($compile_dir);
$smarty->setCacheDir($cache_dir);
$smarty->debugging = $smarty_debug;

if ($smarty_debug) {
    $smarty->error_reporting = E_ALL;
} else {
    $smarty->error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING;
}

#==============================================================================#
# Assign Smarty Variables                                                      #
#==============================================================================#
$smarty->assign('version', $version);
$smarty->assign('lang', $lang);
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

