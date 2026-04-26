<?php
/**
 * ToolBox — Cron Cleanup
 * Deletes old files from uploads directory
 * Recommended: Run every 30 minutes
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$uploadDir = __DIR__ . '/../uploads/';
$expiryMinutes = 30;

// 1. Delete files from filesystem
$files = glob($uploadDir . '*');
$count = 0;
foreach ($files as $file) {
    if (is_file($file) && basename($file) !== '.htaccess' && basename($file) !== 'index.html') {
        if (time() - filemtime($file) > ($expiryMinutes * 60)) {
            unlink($file);
            $count++;
        }
    }
}

// 2. Update database status for expired jobs
$stmt = $pdo->prepare("UPDATE tool_jobs SET status = 'expired' WHERE status = 'done' AND expires_at <= NOW()");
$stmt->execute();
$dbCount = $stmt->rowCount();

echo "Cleanup complete. Deleted $count files. Expired $dbCount jobs in database.";
