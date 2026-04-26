<?php
/**
 * ToolBox — API: Process PDF
 * Handles server-side PDF tool requests
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/FileProcessor.php';
require_once __DIR__ . '/../classes/WordProcessor.php';
require_once __DIR__ . '/../includes/functions.php';

// Fatal Error Handler for debugging
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        if (!headers_sent()) header('Content-Type: application/json');
        echo json_encode(['error' => 'Critical Server Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']]);
        exit;
    }
});

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

// Security: Validate file type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowedMimes = [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.ms-powerpoint',
    'text/html'
];

if (!in_array($mime, $allowedMimes)) {
    echo json_encode(['error' => 'Invalid file type: ' . $mime]);
    exit;
}

$processor = new FileProcessor($pdo);
$uploadDir = __DIR__ . '/../uploads/';
$jobId = $processor->createJob($auth->userId(), $tool, $file['name']);

// Move uploaded file to a unique location
$inputPath = $uploadDir . bin2hex(random_bytes(8)) . '_' . basename($file['name']);
if (!move_uploaded_file($file['tmp_name'], $inputPath)) {
    $processor->updateJob($jobId, 'failed', null, 'Failed to move upload');
    echo json_encode(['error' => 'Failed to save upload']);
    exit;
}

$outputPath = $uploadDir . bin2hex(random_bytes(8)) . '_result.pdf';
$success = false;

try {
    switch ($tool) {
        case 'pdf-compress':
            $quality = $_POST['quality'] ?? '1';
            $success = $processor->compressPdf($inputPath, $outputPath, $quality);
            break;
        case 'word-to-pdf':
            $wp = new WordProcessor();
            if ($wp->wordToPdf($inputPath, $outputPath)) {
                $success = true;
            } else {
                // Fallback to LibreOffice if it somehow exists
                $tempDir = $uploadDir . 'tmp_' . bin2hex(random_bytes(4)) . '/';
                mkdir($tempDir);
                if ($processor->officeToPdf($inputPath, $tempDir)) {
                    $files = glob($tempDir . '*.pdf');
                    if (!empty($files)) {
                        rename($files[0], $outputPath);
                        $success = true;
                    }
                }
                @rmdir($tempDir);
            }
            break;
        case 'excel-to-pdf':
        case 'ppt-to-pdf':
            // These still need LibreOffice
            $tempDir = $uploadDir . 'tmp_' . bin2hex(random_bytes(4)) . '/';
            if (!mkdir($tempDir, 0777, true)) {
                $processor->updateJob($jobId, 'failed', null, 'Failed to create temp directory');
                echo json_encode(['error' => 'Server error: Failed to create processing directory.']);
                exit;
            }
            if ($processor->officeToPdf($inputPath, $tempDir)) {
                $files = glob($tempDir . '*.pdf');
                if (!empty($files)) {
                    rename($files[0], $outputPath);
                    $success = true;
                }
            }
            // Cleanup temp dir safely to avoid PHP warnings leaking into JSON
            $cleanupFiles = glob($tempDir . '*');
            if (is_array($cleanupFiles)) {
                foreach ($cleanupFiles as $cf) {
                    if (is_file($cf)) @unlink($cf);
                }
            }
            @rmdir($tempDir);
            break;
        case 'pdf-to-word':
            $tempDir = $uploadDir . 'tmp_' . bin2hex(random_bytes(4)) . '/';
            @mkdir($tempDir);
            if ($processor->pdfToWord($inputPath, $tempDir)) {
                $files = glob($tempDir . '*.docx');
                if (!empty($files)) {
                    $outputPath = str_replace('.pdf', '.docx', $outputPath);
                    @rename($files[0], $outputPath);
                    $success = true;
                }
            }
            // Cleanup temp dir safely
            $cleanupFiles = glob($tempDir . '*');
            if (is_array($cleanupFiles)) {
                foreach ($cleanupFiles as $cf) {
                    if (is_file($cf)) @unlink($cf);
                }
            }
            @rmdir($tempDir);
            break;
        case 'pdf-protect':
            $userPass = $_POST['user_password'] ?? '';
            $ownerPass = $_POST['owner_password'] ?? '';
            $success = $processor->protectPdf($inputPath, $outputPath, $userPass, $ownerPass);
            break;
        case 'pdf-unlock':
            $pass = $_POST['password'] ?? '';
            $success = $processor->unlockPdf($inputPath, $outputPath, $pass);
            break;
        case 'pdf-repair':
            $success = $processor->repairPdf($inputPath, $outputPath);
            break;
        case 'pdf-ocr':
            $lang = $_POST['lang'] ?? 'eng';
            $outBase = str_replace('.pdf', '', $outputPath);
            if ($processor->ocrPdf($inputPath, $outBase, $lang)) {
                if (file_exists($outBase . '.pdf')) {
                    $success = true;
                    $outputPath = $outBase . '.pdf';
                }
            }
            break;
        default:
            if (isset($inputPath) && file_exists($inputPath)) @unlink($inputPath);
            $processor->updateJob($jobId, 'failed', null, 'Unsupported tool');
            echo json_encode(['error' => 'Tool implementation pending or unsupported']);
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
