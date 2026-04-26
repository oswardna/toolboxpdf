<?php
/**
 * ToolBox — Server-Side File Processing Engine
 * 
 * Handles execution of system binaries (Ghostscript, ImageMagick, qpdf, etc.)
 * for premium tool functionality.
 */

class FileProcessor {
    private PDO $pdo;
    private $binaries = [
        'gs'         => 'gswin64c', // Ghostscript (Windows)
        'magick'     => 'magick',   // ImageMagick 7+
        'qpdf'       => 'qpdf',     // qpdf
        'libreoffice'=> 'soffice',  // LibreOffice (soffice)
        'tesseract'  => 'tesseract' // Tesseract OCR
    ];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        // Use constants defined in config/app.php
        if (defined('BIN_GHOSTSCRIPT')) $this->binaries['gs'] = BIN_GHOSTSCRIPT;
        if (defined('BIN_IMAGEMAGICK')) $this->binaries['magick'] = BIN_IMAGEMAGICK;
        if (defined('BIN_LIBREOFFICE')) $this->binaries['libreoffice'] = BIN_LIBREOFFICE;
        if (defined('BIN_QPDF')) $this->binaries['qpdf'] = BIN_QPDF;
        if (defined('BIN_TESSERACT')) $this->binaries['tesseract'] = BIN_TESSERACT;
    }

    /**
     * Compress PDF using Ghostscript
     */
    public function compressPdf(string $input, string $output, string $quality = '1'): bool {
        $settings = [
            '0' => '/screen',   // 72 dpi
            '1' => '/ebook',    // 150 dpi
            '2' => '/printer',  // 300 dpi
            '3' => '/prepress'  // 300 dpi (color preserved)
        ];
        $pdfSettings = $settings[$quality] ?? '/ebook';

        $cmd = sprintf(
            '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=%s -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
            escapeshellarg($this->binaries['gs']),
            escapeshellarg($pdfSettings),
            escapeshellarg($output),
            escapeshellarg($input)
        );

        exec($cmd, $out, $ret);
        return $ret === 0;
    }

    /**
     * PDF to JPG conversion
     */
    public function pdfToJpg(string $input, string $outputPattern, int $dpi = 150): bool {
        $cmd = sprintf(
            '%s -sDEVICE=jpeg -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -r%d -dNOPAUSE -dBATCH -sOutputFile=%s %s',
            escapeshellarg($this->binaries['gs']),
            $dpi,
            escapeshellarg($outputPattern),
            escapeshellarg($input)
        );

        exec($cmd, $out, $ret);
        return $ret === 0;
    }

    /**
     * Image to PDF conversion using ImageMagick
     */
    public function imgToPdf(array $inputs, string $output): bool {
        $inputFiles = array_map('escapeshellarg', $inputs);
        $cmd = sprintf(
            '%s %s %s',
            escapeshellarg($this->binaries['magick']),
            implode(' ', $inputFiles),
            escapeshellarg($output)
        );

        exec($cmd, $out, $ret);
        return $ret === 0;
    }

    /**
     * Word/Excel/PPT to PDF using LibreOffice
     */
    public function officeToPdf(string $input, string $outputDir): bool {
        // Use a temporary user profile to avoid permission issues with the home directory
        $profileDir = $outputDir . 'profile';
        if (!is_dir($profileDir)) mkdir($profileDir, 0777, true);

        $cmd = sprintf(
            '%s -env:UserInstallation=%s --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($this->binaries['libreoffice']),
            escapeshellarg('file://' . str_replace('\\', '/', $profileDir)),
            escapeshellarg($outputDir),
            escapeshellarg($input)
        );

        exec($cmd, $out, $ret);
        
        if ($ret !== 0) {
            error_log("LibreOffice failed with exit code $ret. Output: " . implode("\n", $out));
            return false;
        }
        return true;
    }

    /**
     * PDF to Word using LibreOffice
     */
    public function pdfToWord(string $input, string $outputDir): bool {
        // Use writer_pdf_import filter to force opening in Writer instead of Draw
        $cmd = sprintf(
            '%s --infilter="writer_pdf_import" --headless --convert-to docx:"MS Word 2007 XML" --outdir %s %s',
            escapeshellarg($this->binaries['libreoffice']),
            escapeshellarg($outputDir),
            escapeshellarg($input)
        );

        exec($cmd, $out, $ret);
        return $ret === 0;
    }

    /**
     * Protect PDF with password using qpdf
     */
    public function protectPdf(string $input, string $output, string $userPass, string $ownerPass = ''): bool {
        $cmd = sprintf(
            '%s --encrypt %s %s 256 -- %s %s',
            escapeshellarg($this->binaries['qpdf']),
            escapeshellarg($userPass),
            $ownerPass ? escapeshellarg($ownerPass) : escapeshellarg($userPass),
            escapeshellarg($input),
            escapeshellarg($output)
        );

        exec($cmd, $out, $ret);
        return $ret === 0;
    }

    /**
     * Unlock PDF using qpdf
     */
    public function unlockPdf(string $input, string $output, string $password): bool {
        $cmd = sprintf(
            '%s --password=%s --decrypt %s %s',
            escapeshellarg($this->binaries['qpdf']),
            escapeshellarg($password),
            escapeshellarg($input),
            escapeshellarg($output)
        );

        exec($cmd, $out, $ret);
        return $ret === 0;
    }

    /**
     * OCR PDF using Tesseract
     * Note: This usually involves converting PDF to images first
     */
    public function ocrPdf(string $input, string $outputBase, string $lang = 'eng'): bool {
        // Tesseract directly can handle some images, for PDF it's better to use a wrapper
        // or convert PDF -> PNG -> OCR -> Searchable PDF.
        // Simplest version: convert to text
        $cmd = sprintf(
            '%s %s %s -l %s pdf',
            escapeshellarg($this->binaries['tesseract']),
            escapeshellarg($input),
            escapeshellarg($outputBase),
            escapeshellarg($lang)
        );

        exec($cmd, $out, $ret);
        return $ret === 0;
    }

    /**
     * Repair PDF using Ghostscript (rewrites the PDF stream to fix corruption)
     */
    public function repairPdf(string $input, string $output): bool {
        // Ghostscript reprocesses the PDF through its writer, effectively repairing it
        $cmd = sprintf(
            '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
            escapeshellarg($this->binaries['gs']),
            escapeshellarg($output),
            escapeshellarg($input)
        );

        exec($cmd, $out, $ret);
        return $ret === 0;
    }

    /**
     * Image Format Conversion
     */
    public function convertImg(string $input, string $output, string $format): bool {
        $cmd = sprintf(
            '%s %s %s',
            escapeshellarg($this->binaries['magick']),
            escapeshellarg($input),
            escapeshellarg($output)
        );

        exec($cmd, $out, $ret);
        return $ret === 0;
    }

    /**
     * Create a Tool Job record
     */
    public function createJob(int $userId, string $toolSlug, string $inputFile): int {
        $stmt = $this->pdo->prepare('INSERT INTO tool_jobs (user_id, tool_slug, input_file, status, expires_at) VALUES (?, ?, ?, "processing", DATE_ADD(NOW(), INTERVAL 30 MINUTE))');
        $stmt->execute([$userId, $toolSlug, $inputFile]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update Tool Job record
     */
    public function updateJob(int $jobId, string $status, ?string $outputFile = null, ?string $error = null): void {
        $stmt = $this->pdo->prepare('UPDATE tool_jobs SET status = ?, output_file = ?, error_msg = ? WHERE id = ?');
        $stmt->execute([$status, $outputFile, $error, $jobId]);
    }
}
