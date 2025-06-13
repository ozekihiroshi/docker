<?php
// lib/db_common.php

function get_database_connection() {
    $db_host = 'nextcloud-db';
    $db_user = 'root';
    $db_pass = 'rootpassword';
    $db_name = 'self_service';

    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

    try {
        return new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        die("DB connection failed: " . $e->getMessage());
    }
}
