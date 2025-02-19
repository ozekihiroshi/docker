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

#==============================================================================#
# Language                                                                    #
#==============================================================================#
$files = glob(__DIR__ . "/../lang/*.php");
$languages = str_replace(".inc.php", "", $files);
$languages = str_replace(__DIR__ . "/../lang/", "", $languages);
$lang = \Ltb\Language::detect_language($lang, $allowed_lang ? array_intersect($languages, $allowed_lang) : $languages);
require_once(__DIR__ . "/../lang/$lang.inc.php");

if (file_exists(__DIR__ . "/../conf/$lang.inc.php")) {
    require_once(__DIR__ . "/../conf/$lang.inc.php");
}


#==============================================================================#
# LDAP Connection (Optional)                                                   #
#==============================================================================#
$success = "";
$error = "";

$ldapInstance = ldap_connect($ldap_url);
ldap_set_option($ldapInstance, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapInstance, LDAP_OPT_REFERRALS, 0);
ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);  // デバッグ有効化

if (!$ldapInstance) {
    die("LDAP connection failed.");
}

$bind = ldap_bind($ldapInstance, "CN=Administrator,CN=Users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw", "Password1");

if (!$bind) {
    die("LDAP bind failed: " . ldap_error($ldapInstance));
}
echo "LDAP connection and bind successful.";

# LDAP チェック

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_type = isset($_POST["user_type"]) ? trim($_POST["user_type"]) : "";
    $department = isset($_POST["department"]) ? trim($_POST["department"]) : "";
    $userAccount = isset($_POST["userAccount"]) ? trim($_POST["userAccount"]) : "";

    if (empty($user_type) || empty($department) || empty($userAccount)) {
        $error = "All fields are required.";
    } elseif (!in_array($user_type, ["student", "staff"])) {
        $error = "Invalid user type.";
    } else {
        $search = ldap_search(
        $ldapInstance, 
        "DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw", 
        "(sAMAccountName={$userAccount})"
        );

        $entries = ldap_get_entries($ldapInstance, $search);

        if ($entries["count"] > 0) {
           $success = "User account found in LDAP.";
            echo "User {$userAccount} exists in LDAP.";
        } else {
            $error = "User account does not exist in LDAP.";
            echo "User {$userAccount} does not exist.";
        }

    }
}

#==============================================================================#
# Fetch Data                                                                  #
#==============================================================================#
$requests = [
    ["id" => 1, "username" => "user1", "status" => "pending", "created_at" => "2024-01-01"],
    ["id" => 2, "username" => "user2", "status" => "completed", "created_at" => "2024-01-02"],
];


// 部署リスト（デフォルト）
$departments = [
    "IT", "HR", "Finance", "Administration", "Library", "Student Affairs"
];

#============================================================================== 
# MariaDB 接続設定
#============================================================================== 
$mysqli = new mysqli($db_host,$db_user,$db_pass,$db_name);


if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
} else {
    echo "Database connection successful.";
}

#============================================================================== 
# フォーム処理
#============================================================================== 
if (empty($error) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $user_type = isset($_POST["user_type"]) ? trim($_POST["user_type"]) : "";
    $department = isset($_POST["department"]) ? trim($_POST["department"]) : "";
    $userAccount = isset($_POST["userAccount"]) ? trim($_POST["userAccount"]) : "";

    if (empty($user_type) || empty($department) || empty($userAccount)) {
        $error = "All fields are required.";
    } elseif (!in_array($user_type, ["student", "staff"])) {
        $error = "Invalid user type.";
    } else {
        // 1時間以内のリクエストがあるか確認
        $stmt = $mysqli->prepare("SELECT created_at FROM password_reset_requests WHERE userAccount = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $userAccount);
        $stmt->execute();
        $stmt->bind_result($last_created_at);
        $stmt->fetch();
        $stmt->close();

        if ($last_created_at) {
            $last_request_time = strtotime($last_created_at);
            $current_time = time();

            // 1時間以内のリクエストを拒否
            if (($current_time - $last_request_time) < 3600) {
                $error = "You have already submitted a request for this account within the last hour. Please try again later.";
            }
        }

        if (empty($error)) {
            // リクエストIDを生成
            $request_id = strtoupper(substr(md5($userAccount . time()), 0, 8));

            // SQLインジェクション防止のためprepareを使用
            $stmt = $mysqli->prepare("INSERT INTO password_reset_requests (id, user_type, department, userAccount, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("ssss", $request_id, $user_type, $department, $userAccount);

            if ($stmt->execute()) {
                header("Location: reset_request.php?success=1");
                exit();
            } else {
                $error = "Failed to submit request.";
            }

            $stmt->close();
        }
    }
}

#============================================================================== 
# 既存のリクエスト取得
#============================================================================== 
$requests = [];
$result = $mysqli->query("SELECT id, user_type, department, status, created_at FROM password_reset_requests ORDER BY created_at DESC");

while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$mysqli->close();

# Check if request was successful
$success_message = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Your request has been successfully submitted.";
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

$smarty->assign('error', $error);

$smarty->assign('departments', $departments);
$smarty->assign('success_message', $success_message);

#==============================================================================#
# Assign Smarty Variables                                                      #
#==============================================================================#
$smarty->assign('version', $version);
$smarty->assign('lang', $lang);
$smarty->assign('requests', $requests);
$smarty->assign('custom_css', isset($custom_css) ? $custom_css : '');
$smarty->assign('background_image', isset($background_image) ? $background_image : '');
$smarty->assign('display_footer', isset($display_footer) ? $display_footer : true);
$smarty->assign('captcha_css', isset($captcha_css) ? $captcha_css : '');
$smarty->assign('captcha_js', isset($captcha_js) ? $captcha_js : '');
$smarty->assign('questions_count', isset($questions_count) ? $questions_count : 0);

#==============================================================================#
# Display Template                                                             #
#==============================================================================#
$smarty->display('reset_request.tpl');

