<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../conf/ldap_config.php");
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . '/../vendor/smarty/smarty/libs/Smarty.class.php');
require_once(__DIR__ . "/../lib/session_common.php");

check_session_timeout();

// LDAPS接続設定（staff固定）
$ldap_url = $ldap_servers['staff']['url'];
$ldap_binddn = $ldap_servers['staff']['bind_dn'];
$ldap_bindpw = $ldap_servers['staff']['bind_pw'];
$base_dn = "OU=Admins," . $ldap_servers['staff']['base_dn'];  // 固定OU部分だけ追加
$search_filter = "(sAMAccountName=hozeki)";

// LDAPS接続を試行
$ldap_connection = ldap_connect($ldap_url);
if (!$ldap_connection) {
    die("Failed to connect to LDAP server.");
}

// LDAPSオプション設定
ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0);
//ldap_set_option(NULL, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_HARD);
//ldap_set_option(NULL, LDAP_OPT_X_TLS_CACERTFILE, "/usr/local/share/ca-certificates/staff-STAFFDC2-CA.crt");

// バインド処理
if (@ldap_bind($ldap_connection, $ldap_binddn, $ldap_bindpw)) {
    //echo "LDAPS Authentication Successful!<br>";
} else {
    die("LDAPS Authentication Failed: " . ldap_error($ldap_connection));
}

$loggedIn = isset($_SESSION['admin_user']);

if (!isset($loggedIn)) {
//if (!isset($_SESSION['admin_user'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
	$ldap_admin_dn = $ldap_servers['staff']['bind_dn'];
        $ldap_admin_password = $ldap_servers['staff']['bind_pw'];
        // **LDAP 接続**
        $ldap_conn = ldap_connect($ldap_url);
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        if (!$ldap_conn) {
            die("Failed to connect to LDAP server.<br>");
        }

        // **管理者権限でバインド**
        if (!@ldap_bind($ldap_conn, $ldap_admin_dn, $ldap_admin_password)) {
            die("Admin LDAP bind failed: " . ldap_error($ldap_conn) . "<br>");
        }
        echo "LDAPS Authentication Successful!<br>";

        // **sAMAccountName でユーザー検索**
        $search_filter = "(sAMAccountName=$username)";
        $search_result = ldap_search($ldap_conn, $base_dn, $search_filter);

        if (!$search_result) {
            die("LDAP search failed: " . ldap_error($ldap_conn) . "<br>");
        }

        $entries = ldap_get_entries($ldap_conn, $search_result);

        if ($entries["count"] > 0) {
            $user_dn = $entries[0]["dn"]; // **検索結果から DN を取得**
            echo "User DN: $user_dn<br>";

            // **パスワードでバインドして認証**
            if (@ldap_bind($ldap_conn, $user_dn, $password)) {
                echo "User authentication successful!<br>";

                // **管理者グループのチェック**
                if (in_array("CN=ADMINS,OU=ADMINS,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw", $entries[0]["memberof"])) {
                    echo "User is in ADMINS group!<br>";
                    $_SESSION['admin_user'] = $username;
                    header("Location: admin_dashboard.php");
                    exit;
                } else {
                    echo "User is not in ADMINS group.<br>";
                }
            } else {
                echo "User authentication failed: " . ldap_error($ldap_conn) . "<br>";
            }
        } else {
            echo "User not found.<br>";
        }

        ldap_close($ldap_conn);
    }

    // ログインフォームを表示
// ログインフォームを表示
// ログインフォームを表示
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #2c3e50;
            color: #ecf0f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
            flex-direction: column;
        }
        .nav-links {
            position: absolute;
            top: 20px;
            text-align: center;
        }
        .nav-links a {
            color: #ecf0f1;
            font-weight: bold;
            text-decoration: none;
            margin: 0 15px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .nav-links a:hover {
            background: #16a085;
            color: white;
        }
        .login-container {
            background: #34495e;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            background: #2c3e50;
            border: 1px solid #95a5a6;
            color: #ecf0f1;
        }
        .form-control:focus {
            border-color: #1abc9c;
            box-shadow: 0 0 5px #1abc9c;
        }
        .btn-login {
            background: #1abc9c;
            color: white;
            font-weight: bold;
            border: none;
            width: 100%;
            padding: 10px;
            transition: background 0.3s ease;
        }
        .btn-login:hover {
            background: #16a085;
        }
    </style>
</head>
<body>
    <div class="nav-links">
        <a href="index.php">Change Password</a>
        <a href="reset_request.php">Request Password Reset</a>
    </div>

    <div class="login-container">
        <h2>Admin Login</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </div>
</body>
</html>
HTML;
    if (isset($error)) {
        echo "<p style='color: red;'>$error</p>";
    }
    exit;
}

// データベース接続処理
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// パスワードリセットリクエストの取得
$stmt = $mysqli->prepare("SELECT id, userAccount, user_type, department, status, created_at FROM password_reset_requests ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$stmt->close();
$mysqli->close();

$smarty = new Smarty\Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->assign('requests', $requests);
#==============================================================================#
# Variables                                                                   #
#==============================================================================#
$version = "1.7.2"; // システムのバージョン


#==============================================================================#
# Assign Smarty Variables                                                      #
#==============================================================================#
$smarty->assign('csrf_token', generate_csrf_token());
$smarty->assign('loggedIn', $loggedIn);
$smarty->assign('admin_user', $_SESSION['admin_user'] ?? '');

$smarty->assign('version', $version);
//$smarty->assign('lang', $lang);
$smarty->assign('requests', $requests);
$smarty->assign('custom_css', isset($custom_css) ? $custom_css : '');
$smarty->assign('background_image', isset($background_image) ? $background_image : '');
$smarty->assign('display_footer', isset($display_footer) ? $display_footer : true);
$smarty->assign('captcha_css', isset($captcha_css) ? $captcha_css : '');
$smarty->assign('captcha_js', isset($captcha_js) ? $captcha_js : '');
$smarty->assign('questions_count', isset($questions_count) ? $questions_count : 0);


$smarty->display('admin_dashboard.tpl');
