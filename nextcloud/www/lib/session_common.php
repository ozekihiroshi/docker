<?php
// セッションタイムアウトを秒単位で定義（ここでは30分）
date_default_timezone_set('Africa/Gaborone');
define('SESSION_TIMEOUT_DURATION', 1800); // 1800秒 = 30分

function check_session_timeout() {
    // 未ログインなら即リダイレクト（念のため）
    if (!isset($_SESSION['admin_user'])) {
        header("Location: admin_login.php");
        exit();
    }

    // セッションタイムアウトチェック
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT_DURATION) {
        // セッションを破棄し、タイムアウトメッセージ付きでログイン画面へ
        session_unset();
        session_destroy();
        header("Location: admin_login.php?timeout=1");
        exit();
    }

    // 最後の操作時刻を更新
    $_SESSION['last_activity'] = time();
}

// ====================
// CSRF トークン生成
// ====================
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// ====================
// CSRF トークン検証
// ====================
function validate_csrf_token($token) {
    if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}
