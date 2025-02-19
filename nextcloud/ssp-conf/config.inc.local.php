<?php
#============================================================================== 
# Local configuration file for Self Service Password
# This file overrides default settings in config.inc.php
#============================================================================== 

# Debug mode (set to true for testing, false for production)
$debug = false;

# MySQL Database settings for Self Service Password requests
$db_type = "mysql";                   # Database type (mysql, pgsql, sqlite, etc.)
$db_host = "nextcloud-db";            # MariaDB container (MySQL server)
$db_name = "self_service";            # Database name created for SSP
$db_user = "ssp_user";                # Database user for SSP
$db_pass = "ssp_securepassword";  # Password for ssp_user


$keyphrase = "BJ7Rfh0KAULAwBpZcAsi9u1kGrl1QJ9bSJAW1h2TAFg="; // ランダムな文字列を設定
// LDAP 接続情報
//$ldap_url = "ldap://staffdc2.gtc.ce.ac.bw:389"; // LDAP サーバーのアドレス
$ldap_url = "ldaps://staffdc2.gtc.ce.ac.bw:636"; // LDAP サーバーのアドレス
$ldap_starttls = false;
$ldap_binddn = "CN=administrator,CN=users,DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"; // バインドユーザー
$ldap_bindpw = "Password1"; // バインドユーザーのパスワード
$ldap_base = "DC=staff,DC=gtc,DC=ce,DC=ac,DC=bw"; // ユーザー検索のベース DN
$ldap_login_attribute = "sAMAccountName"; // ログイン時に使用する属性
$ldap_fullname_attribute = "cn"; // ユーザーのフルネーム属性
$ldap_filter = "(objectClass=user)"; // 検索フィルター

// パスワードポリシー
$pwd_min_length = 8;
$pwd_max_length = 20;
$pwd_complexity = 3; // 大文字、小文字、数字、特殊文字を含む
$pwd_no_reuse = true; // 以前のパスワードを再利用しない

// メール設定
$mail_from = "noreply@gtc.ce.ac.bw";
$mail_smtp_server = "mail.gtc.ce.ac.bw";
$mail_smtp_port = 25;
$mail_smtp_user = ""; // 認証が不要なら空のまま
$mail_smtp_pass = ""; // 認証が不要なら空のまま
$mail_subject = "Password Reset Request";
$mail_content = "Hello %fullname%,\n\nPlease click the following link to reset your password:\n\n%link%";

// パスワードリセット URL
$reset_url = "https://ssp.gtc.ce.ac.bw"; // SSP の公開 URL

// 秘密の質問 (Q&A) 認証の有効化
$use_questions = true;
$questions = [
    "What is your favorite color?",
    "What is your pet's name?",
    "What was the name of your first school?"
];

// セキュリティ設定
$show_help = true; // ヘルプボタンを表示
$show_contact = true; // 連絡先ボタンを表示
$contact_link = "http://helpdesk.gtc.ce.ac.bw/reset-request"; // リクエストページへのリンク
