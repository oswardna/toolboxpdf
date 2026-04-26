<?php
/**
 * ToolBox — Word/PDF Processing using PHPWord & DomPDF
 */

class WordProcessor {
    
    public function __construct() {
        $this->setupAutoloader();
    }

    private function setupAutoloader() {
        spl_autoload_register(function ($class) {
            $prefixes = [
                'PhpOffice\\PhpWord\\' => __DIR__ . '/../vendor_lite/PHPWord-1.1.0/src/PhpWord/',
                'PhpOffice\\Common\\'  => __DIR__ . '/../vendor_lite/Common-0.2.10/src/Common/',
                'Dompdf\\'             => __DIR__ . '/../vendor_lite/dompdf/src/',
            ];

            foreach ($prefixes as $prefix => $baseDir) {
                if (strpos($class, $prefix) === 0) {
                    $relativeClass = substr($class, strlen($prefix));
                    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        });
        
        // DomPDF also needs fontlib and svg-lib (usually bundled)
        // If missing, fidelity will be lower.
    }

    /**
     * Convert Word to PDF using PHPWord + DomPDF
     */
    public function wordToPdf(string $inputPath, string $outputPath): bool {
        try {
            // Set renderer
            \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF);
            \PhpOffice\PhpWord\Settings::setPdfRendererPath(__DIR__ . '/../vendor_lite/dompdf');

            $phpWord = \PhpOffice\PhpWord\IOFactory::load($inputPath);
            $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
            $xmlWriter->save($outputPath);
            
            return file_exists($outputPath);
        } catch (Exception $e) {
            error_log("PHPWord Error: " . $e->getMessage());
            return false;
        }
    }
}
