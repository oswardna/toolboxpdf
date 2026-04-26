<?php
/**
 * ToolBox — Database Connection (PDO)
 */
require_once __DIR__ . '/app.php';

$dbHost = 'localhost';
$dbName = 'toolbox';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // During install the DB might not exist yet — try without dbname
    try {
        $pdo = new PDO("mysql:host={$dbHost};charset={$dbCharset}", $dbUser, $dbPass, $options);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");
    } catch (PDOException $e2) {
        die('Database connection failed: ' . $e2->getMessage());
    }
}
