<?php
/**
 * ToolBox — Advanced Diagnostics
 * Searches for binaries in common non-standard paths.
 */
header('Content-Type: text/plain');
echo "ToolBox Advanced Diagnostics\n";
echo "============================\n\n";

$searchPaths = [
    '/usr/bin/',
    '/usr/local/bin/',
    '/opt/libreoffice/program/',
    '/usr/lib64/libreoffice/program/',
    '/usr/lib/libreoffice/program/',
    '/usr/local/libreoffice/program/',
    '/bin/'
];

$binaries = ['libreoffice', 'soffice', 'gs', 'convert', 'qpdf', 'tesseract', 'unoconv', 'pandoc'];

echo "Searching for Binaries:\n";
foreach ($binaries as $bin) {
    echo "[$bin]: ";
    $found = false;
    foreach ($searchPaths as $path) {
        $fullPath = $path . $bin;
        if (@file_exists($fullPath) && @is_executable($fullPath)) {
            echo "FOUND at $fullPath\n";
            $version = shell_exec("$fullPath --version 2>&1") ?: "Version unknown";
            echo "   Version: " . trim($version) . "\n";
            $found = true;
            break;
        }
    }
    if (!$found) {
        $which = shell_exec("which $bin 2>&1");
        if ($which && strpos($which, '/') !== false) {
            echo "FOUND via which: " . trim($which) . "\n";
        } else {
            echo "NOT FOUND\n";
        }
    }
    echo "\n";
}

echo "PHP Extensions:\n";
$extensions = ['zip', 'gd', 'imagick', 'dom', 'xml', 'mbstring', 'curl'];
foreach ($extensions as $ext) {
    echo "[$ext]: " . (extension_loaded($ext) ? "Installed" : "MISSING") . "\n";
}

echo "\nDisabled Functions:\n";
echo ini_get('disable_functions') ?: "None";
echo "\n";
