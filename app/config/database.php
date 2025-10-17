<?php
// Database configuration: include this file to get a $pdo PDO instance.
$host = "localhost";
$dbname = "petshop";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If DB is not available, set $pdo to null and log the error.
    error_log('DB connection error: ' . $e->getMessage());
    $pdo = null;
}
