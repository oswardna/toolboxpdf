<?php
/**
 * ToolBox — System Initialization
 * 
 * Bootstraps the application: sessions, config, database, and core classes.
 * This should be included BEFORE any HTML output to allow for redirects.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Subscription.php';
require_once __DIR__ . '/../classes/Tool.php';
require_once __DIR__ . '/functions.php';

// Global instances
$auth = new Auth($pdo);
