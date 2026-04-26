<?php
/**
 * ToolBox — Logout
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth($pdo);
$auth->logout();
header('Location: ' . BASE_URL . '/');
exit;
