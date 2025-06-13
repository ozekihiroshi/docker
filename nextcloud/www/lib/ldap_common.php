<?php
// lib/ldap_common.php

require_once(__DIR__ . '/../conf/ldap_config.php');

/**
 * LDAPサーバーへ接続する
 */
function ldap_connect_server($user_type = 'staff') {
    global $ldap_servers;

    if (!isset($ldap_servers[$user_type])) {
        throw new Exception("Invalid user type: $user_type");
    }

    $ldap_conn = ldap_connect($ldap_servers[$user_type]['url']);
    if (!$ldap_conn) {
        throw new Exception("Failed to connect to LDAP server.");
    }

    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

    return $ldap_conn;
}

/**
 * 管理者アカウントでバインドする
 */

function ldap_bind_admin($ldap_conn, $user_type = 'staff') {
    global $ldap_servers;

    $bind_dn = $ldap_servers[$user_type]['bind_dn'];
    $bind_pw = $ldap_servers[$user_type]['bind_pw'];

    if (!@ldap_bind($ldap_conn, $bind_dn, $bind_pw)) {
        throw new Exception("LDAP admin bind failed: " . ldap_error($ldap_conn));
    }
}

/**
 * ユーザーのDNを取得する
 */
function ldap_get_user_dn($ldap_conn, $base_dn, $username) {
    $search_filter = "(sAMAccountName=$username)";
    $result = ldap_search($ldap_conn, $base_dn, $search_filter);

    if (!$result) {
        throw new Exception("LDAP search failed: " . ldap_error($ldap_conn));
    }

    $entries = ldap_get_entries($ldap_conn, $result);

    if ($entries['count'] > 0) {
        return $entries[0]['dn'];
    } else {
        throw new Exception("User not found in LDAP.");
    }
}

/**
 * パスワードを変更する
 */
function ldap_change_password($ldap_conn, $user_dn, $new_password) {
    $new_password_utf16 = iconv('UTF-8', 'UTF-16LE', '"' . $new_password . '"');
    $password_entry = ["unicodePwd" => $new_password_utf16];

    if (!ldap_modify($ldap_conn, $user_dn, $password_entry)) {
        throw new Exception("Failed to change password: " . ldap_error($ldap_conn));
    }
}

/**
 * Windows time（100ナノ秒単位）→ UNIXタイムスタンプ → 日付文字列 に変換
 */
function convert_windows_time($winTime, $toLocal = true) {
    if (!$winTime || !is_numeric($winTime)) {
        return null;
    }

    // Windows Time → Unix Timestamp（100ナノ秒単位 → 秒に）
    $unixTimestamp = (int)(($winTime - 116444736000000000) / 10000000);

    if ($toLocal) {
        date_default_timezone_set('Africa/Gaborone');  // 念のためここでも
    }

    return date("Y-m-d H:i:s", $unixTimestamp);
}
