<?php
/**
 * ToolBox — PHPWord Auto-Installer
 * Downloads and sets up PHPWord and its dependencies for cPanel.
 */
header('Content-Type: text/plain');
echo "ToolBox: Installing PHPWord...\n";

$libs = [
    'PHPWord' => 'https://github.com/PHPOffice/PHPWord/archive/refs/tags/v1.1.0.zip',
    'Common'  => 'https://github.com/PHPOffice/Common/archive/refs/tags/v0.2.10.zip',
    'DomPDF'  => 'https://github.com/dompdf/dompdf/releases/download/v2.0.3/dompdf_2-0-3.zip',
];

$vendorDir = __DIR__ . '/vendor_lite/';
if (!is_dir($vendorDir)) mkdir($vendorDir, 0777, true);

foreach ($libs as $name => $url) {
    echo "Downloading $name...\n";
    $zipFile = $vendorDir . $name . '.zip';
    file_put_contents($zipFile, file_get_contents($url));
    
    echo "Extracting $name...\n";
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo($vendorDir);
        $zip->close();
        unlink($zipFile);
        echo "$name installed.\n";
    } else {
        echo "Failed to extract $name.\n";
    }
}

echo "\nPHPWord setup complete. Libraries are in /vendor_lite/\n";
echo "Note: You also need a PDF renderer like DomPDF or mPDF.\n";
