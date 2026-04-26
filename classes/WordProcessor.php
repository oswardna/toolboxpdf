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
            $vendorDir = __DIR__ . '/../vendor_lite/';
            
            // Map prefixes to possible folder patterns
            $map = [
                'PhpOffice\\PhpWord\\' => 'PHPWord*',
                'PhpOffice\\Common\\'  => 'Common*',
                'Dompdf\\'             => 'dompdf*',
            ];

            foreach ($map as $prefix => $pattern) {
                if (strpos($class, $prefix) === 0) {
                    $matches = glob($vendorDir . $pattern, GLOB_ONLYDIR);
                    if (!$matches) continue;
                    
                    $baseDir = $matches[0] . ($prefix === 'Dompdf\\' ? '/src/' : '/src/' . str_replace('\\', '/', $prefix));
                    // For PHPWord and Common, the path structure is slightly different in the source zips
                    if ($prefix === 'PhpOffice\\PhpWord\\') $baseDir = $matches[0] . '/src/PhpWord/';
                    if ($prefix === 'PhpOffice\\Common\\') $baseDir = $matches[0] . '/src/Common/';

                    $relativeClass = substr($class, strlen($prefix));
                    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                    
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        });
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
