<?php
// app/config/database.php

$host = "103.139.203.43";
$dbname = "sql_nhom68_itimi";
$username = "sql_nhom68_itimi";
$password = "ead471f910f7";

// $host = "localhost";
// $dbname = "petshop";
// $username = "root";
// $password = "";

global $pdo;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // ✅ Cho phép toàn bộ project dùng chung PDO
    $GLOBALS['pdo'] = $pdo;

} catch (PDOException $e) {
    error_log('DB connection error: ' . $e->getMessage());
    $pdo = null;
    $GLOBALS['pdo'] = null;
}

