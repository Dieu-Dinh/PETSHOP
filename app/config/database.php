<?php
// Database configuration
$host = "localhost";
$dbname = "petshop";
$username = "root";
$password = "";

global $pdo;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('DB connection error: ' . $e->getMessage());
    $pdo = null;
}
