<?php
session_start();
//require '../conf/ldap_config.php';  // LDAP設定ファイル
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

#==============================================================================
# MariaDB 接続設定
#==============================================================================
$mysqli = new mysqli($db_host,$db_user,$db_pass,$db_name);


if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
} else {
    echo "Database connection successful.";
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $request_id = $_POST["request_id"];
    $username = $_POST["username"];
    $user_type = $_POST["user_type"];
    $new_password = $_POST["new_password"];


    // **LDAP サーバの設定**
    $ldap_servers = [
        "staff" => ["url" => "ldaps://staffdc2.gtc.ce.ac.bw", "base_dn" => "DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"],
        "students" => ["url" => "ldaps://studentsdc2.gtc.ce.ac.bw", "base_dn" => "DC=students,DC=gtc,DC=ce,DC=ac,DC=bw"]
    ];

    // LDAP 接続
    //$ldap_connection = ldap_connect("ldaps://staffdc2.gtc.ce.ac.bw");
    $ldap_connection = ldap_connect($ldap_servers[$user_type]["url"]);

    ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0);

    // バインド (管理者権限)
    $ldap_binddn = "CN=Administrator,CN=Users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw";
    $ldap_bindpw = "Password1";  // 管理者のパスワード

    if (@ldap_bind($ldap_connection, $ldap_binddn, $ldap_bindpw)) {
        // ユーザーの DN を検索
	$base_dn = $ldap_servers[$user_type]["base_dn"];
        $search_filter = "(sAMAccountName={$username})";
        $search_result = ldap_search($ldap_connection, $base_dn, $search_filter);
        $entries = ldap_get_entries($ldap_connection, $search_result);

        if ($entries["count"] > 0) {
            $user_dn = $entries[0]["dn"];
	        echo "User DN: $user_dn<br>";  // ログ出力
            $new_password_utf16 = iconv('UTF-8', 'UTF-16LE', '"' . $new_password . '"');
            $password_entry = ["unicodePwd" => $new_password_utf16];

            if (ldap_modify($ldap_connection, $user_dn, $password_entry)) {
                echo "Password changed successfully!<br>";

             // 次回ログイン時にパスワード変更を強制
               $expire_entry = ["pwdLastSet" => "0"];
               if (ldap_modify($ldap_connection, $user_dn, $expire_entry)) {
                   echo "User will be required to change password at next login.<br>";
             // ステータスを "completed" に更新
                $stmt = $mysqli->prepare("UPDATE password_reset_requests SET status = 'completed' WHERE id = ?");
                if (!$stmt) {
                   die("Prepare failed: " . $mysqli->error);
                }
		        $stmt->bind_param("s", $request_id);
                if ($stmt->execute()) {
                   echo "Password reset successful!";
                } else {
                   echo "Password reset successful, but status update failed: " . $stmt->error;
                }
                // リソースを解放
                $stmt->close();
                $mysqli->close();
               } else {
                   echo "Warning: Failed to enforce password change at next login.<br>";
               }
            } else {
                echo "Failed to reset password.";
            }
        } else {
            echo "User not found.";
        }
    } else {
        echo "LDAP bind failed.";
    }
}
?>

