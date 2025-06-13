<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . '/../conf/ldap_config.php');
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../lib/ldap_common.php");
require_once(__DIR__ . "/../lib/session_common.php");

check_session_timeout();

//=========================
// 管理者ログインチェック
//=========================
if (!isset($_SESSION['admin_user'])) {
    // 未ログインなら管理者ログインページへリダイレクト
    header("Location: admin_login.php");
    exit();
}

//=========================
// 基本変数初期化
//=========================
$version = "1.7.2";
$error = "";
$requests = [];

//=========================
// データベース接続
//=========================
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

//=========================
// パスワードリセットリクエスト一覧を取得
//=========================
$query = "SELECT id, user_type, department, status, created_at, userAccount FROM password_reset_requests ORDER BY created_at DESC";
$result = $mysqli->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $result->free();
}
$mysqli->close();

//=========================
// Smarty テンプレート描画
//=========================
$smarty = new Smarty\Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

// Smartyに変数割り当て
$smarty->assign('csrf_token', generate_csrf_token());
$smarty->assign('version', $version);
$smarty->assign('requests', $requests);
$smarty->assign('admin_user', $_SESSION['admin_user']);
$smarty->display("update_password.tpl");
?>

