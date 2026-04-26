<?php
/**
 * ToolBox — Admin Header
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Subscription.php';
require_once __DIR__ . '/../classes/Tool.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth($pdo);
$auth->requireAdmin();

$pageTitle = $pageTitle ?? 'Admin';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/sidebar.php';
?>
<div class="tb-admin-main">
    <div class="container-fluid">
