<?php
/**
 * ToolBox — Vendor Check
 * Verify if libraries were installed correctly.
 */
header('Content-Type: text/plain');
echo "Checking vendor_lite directory...\n\n";

$vendorDir = __DIR__ . '/vendor_lite/';
if (!is_dir($vendorDir)) {
    die("vendor_lite directory DOES NOT EXIST. Did you run setup_phpword.php?\n");
}

$items = scandir($vendorDir);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    echo "[FOUND]: $item\n";
    if (is_dir($vendorDir . $item)) {
        $sub = scandir($vendorDir . $item);
        echo "   Contains: " . implode(', ', array_slice($sub, 2, 5)) . "...\n";
    }
}
