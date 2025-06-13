<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../conf/config.inc.php");
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../lib/ldap_common.php");
require_once(__DIR__ . "/../conf/ldap_config.php");
require_once(__DIR__ . "/../lib/session_common.php");

check_session_timeout();

$error = "";
$success = "";

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $request_id = $_POST["request_id"];
    $username = $_POST["username"];
    $user_type = $_POST["user_type"];
    $new_password = $_POST["new_password"];

    try {
        // LDAP接続とバインド
        $ldap_connection = ldap_connect_server($user_type);
        ldap_bind_admin($ldap_connection);

        // ユーザーのDN取得
        $base_dn = $ldap_servers[$user_type]['base_dn'];
        $user_dn = ldap_get_user_dn($ldap_connection, $base_dn, $username);

        // パスワードリセット
        ldap_change_password($ldap_connection, $user_dn, $new_password);

        // 次回ログイン時にパスワード変更を要求
        $expire_entry = ["pwdLastSet" => ["0"]];
        if (!ldap_modify($ldap_connection, $user_dn, $expire_entry)) {
            throw new Exception("Failed to enforce password change at next login.");
        }

        // リクエストのステータスを completed に更新
        $stmt = $mysqli->prepare("UPDATE password_reset_requests SET status = 'completed' WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }
        $stmt->bind_param("s", $request_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update request status: " . $stmt->error);
        }

        echo "Password reset successful!";

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        if (isset($ldap_connection) && $ldap_connection) {
            ldap_close($ldap_connection);
        }
        if (isset($mysqli) && $mysqli) {
            $mysqli->close();
        }
    }
}
?>

