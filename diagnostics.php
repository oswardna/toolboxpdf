<?php
/**
 * ToolBox — Diagnostics Script
 * Run this on your live server to check for tool dependencies.
 */
require_once __DIR__ . '/config/app.php';

header('Content-Type: text/plain');

echo "ToolBox Diagnostics\n";
echo "===================\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n\n";

$binaries = [
    'Ghostscript' => BIN_GHOSTSCRIPT,
    'ImageMagick' => BIN_IMAGEMAGICK,
    'LibreOffice' => BIN_LIBREOFFICE,
    'qpdf'        => BIN_QPDF,
    'Tesseract'   => BIN_TESSERACT,
    'mutool'      => BIN_MUPDF
];

echo "Binary Checks:\n";
echo "--------------\n";
foreach ($binaries as $name => $cmd) {
    $path = shell_exec("which $cmd 2>&1") ?: "Not found in PATH";
    $version = "N/A";
    if (strpos($path, '/') !== false) {
        $version = shell_exec("$cmd --version 2>&1") ?: shell_exec("$cmd -version 2>&1") ?: "Unknown version";
    }
    echo "[$name] ($cmd): $path\n";
    echo "   Version: " . trim($version) . "\n\n";
}

echo "Function Checks:\n";
echo "----------------\n";
$functions = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open'];
foreach ($functions as $func) {
    $disabled = strpos(ini_get('disable_functions'), $func) !== false;
    echo "[$func]: " . ($disabled ? "DISABLED" : "Enabled") . "\n";
}

echo "\nDirectory Permissions:\n";
echo "----------------------\n";
$dirs = [
    'uploads/' => __DIR__ . '/uploads/',
    'api/'     => __DIR__ . '/api/',
];
foreach ($dirs as $label => $path) {
    echo "[$label]: " . (is_writable($path) ? "Writable" : "NOT WRITABLE") . " (" . substr(sprintf('%o', fileperms($path)), -4) . ")\n";
}

echo "\nEnvironment Variables:\n";
echo "----------------------\n";
echo "HOME: " . getenv('HOME') . "\n";
echo "PATH: " . getenv('PATH') . "\n";
