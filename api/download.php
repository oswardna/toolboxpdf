<?php
/**
 * ToolBox — API: Secure Download
 * Handles downloading of processed files
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth($pdo);
$jobId = (int)($_GET['job'] ?? 0);

if (!$jobId) {
    die('Invalid job ID');
}

$stmt = $pdo->prepare('SELECT * FROM tool_jobs WHERE id = ?');
$stmt->execute([$jobId]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    die('Job not found');
}

// Security: Check ownership
if ($job['user_id'] && $job['user_id'] !== $auth->userId() && !$auth->isAdmin()) {
    die('Unauthorized');
}

// Check status
if ($job['status'] !== 'done') {
    die('File not ready or job failed');
}

// Check expiry
if (strtotime($job['expires_at']) < time()) {
    die('Download link expired');
}

$filePath = __DIR__ . '/../uploads/' . $job['output_file'];

if (!file_exists($filePath)) {
    die('File no longer exists on server');
}

// Serve file
$originalInfo = pathinfo($job['input_file']);
$safeBaseName = str_replace([' ', '.'], '_', $originalInfo['filename']);
$outputExt = pathinfo($filePath, PATHINFO_EXTENSION);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $safeBaseName . '_toolbox_result.' . $outputExt . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;
