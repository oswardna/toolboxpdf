<?php
/**
 * ToolBox — API: Process Image
 * Handles server-side image tool requests
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/FileProcessor.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!validateCsrf()) {
    echo json_encode(['error' => 'Invalid security token. Please refresh.']);
    exit;
}

$tool = $_POST['tool'] ?? '';

// Check tool status in DB
$stmt = $pdo->prepare("SELECT is_premium, is_active FROM tools WHERE slug = ?");
$stmt->execute([$tool]);
$dbTool = $stmt->fetch();

$isPremium = $dbTool ? (bool)$dbTool['is_premium'] : true;
$isActive  = $dbTool ? (bool)$dbTool['is_active'] : true;

if (!$isActive && !$auth->isAdmin()) {
    echo json_encode(['error' => 'This tool is currently disabled.']);
    exit;
}

if ($isPremium && !$auth->isPro()) {
    echo json_encode(['error' => 'Pro subscription required']);
    exit;
}
$file = $_FILES['file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

// Enforce File Size Limits
$maxSizeMB = $auth->isPro() ? 500 : 50;
$maxSizeBytes = $maxSizeMB * 1024 * 1024;
if ($file['size'] > $maxSizeBytes) {
    echo json_encode(['error' => "File exceeds maximum allowed size of {$maxSizeMB}MB"]);
    exit;
}

$processor = new FileProcessor($pdo);
$uploadDir = __DIR__ . '/../uploads/';
$jobId = $processor->createJob($auth->userId(), $tool, $file['name']);

$inputPath = $uploadDir . bin2hex(random_bytes(8)) . '_' . basename($file['name']);
if (!move_uploaded_file($file['tmp_name'], $inputPath)) {
    $processor->updateJob($jobId, 'failed', null, 'Failed to move upload');
    echo json_encode(['error' => 'Failed to save upload']);
    exit;
}

$outputExt = 'png'; // Default
$success = false;

try {
    switch ($tool) {
        case 'img-convert':
            $outputExt = $_POST['format'] ?? 'jpg';
            $outputPath = $uploadDir . bin2hex(random_bytes(8)) . '_result.' . $outputExt;
            $success = $processor->convertImg($inputPath, $outputPath, $outputExt);
            break;
        case 'img-to-pdf':
            $outputExt = 'pdf';
            $outputPath = $uploadDir . bin2hex(random_bytes(8)) . '_result.pdf';
            $success = $processor->imgToPdf([$inputPath], $outputPath);
            break;
        default:
            if (isset($inputPath) && file_exists($inputPath)) @unlink($inputPath);
            $processor->updateJob($jobId, 'failed', null, 'Unsupported tool');
            echo json_encode(['error' => 'Tool not supported for server-side processing.']);
            exit;
    }
} catch (Exception $e) {
    if (isset($inputPath) && file_exists($inputPath)) @unlink($inputPath);
    $processor->updateJob($jobId, 'failed', null, $e->getMessage());
    echo json_encode(['error' => 'Processing error: ' . $e->getMessage()]);
    exit;
}

if ($success) {
    $processor->updateJob($jobId, 'done', basename($outputPath));
    echo json_encode([
        'success' => true,
        'job_id' => $jobId,
        'download_url' => BASE_URL . '/api/download.php?job=' . $jobId
    ]);
} else {
    $processor->updateJob($jobId, 'failed', null, 'Binary execution failed');
    echo json_encode(['error' => 'Processing failed. Check your file.']);
}

// Immediately delete the original uploaded file to save disk space
if (isset($inputPath) && file_exists($inputPath)) {
    @unlink($inputPath);
}
