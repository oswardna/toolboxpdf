<?php
/**
 * ToolBox — Global Helper Functions
 */

/**
 * Check free usage limit for a tool
 */
function checkFreeLimit(PDO $db, string $toolSlug, int $dailyLimit = FREE_DAILY_LIMIT): bool {
    $identifier = getFreeIdentifier();
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM free_usage 
         WHERE identifier = ? AND tool_slug = ? AND used_at > DATE_SUB(NOW(), INTERVAL 1 DAY)"
    );
    $stmt->execute([$identifier, $toolSlug]);
    return $stmt->fetchColumn() < $dailyLimit;
}

/**
 * Log free tool usage
 */
function logFreeUsage(PDO $db, string $toolSlug): void {
    $identifier = getFreeIdentifier();
    $db->prepare('INSERT INTO free_usage (identifier, tool_slug) VALUES (?, ?)')
       ->execute([$identifier, $toolSlug]);
}

/**
 * Get remaining free uses for a tool
 */
function getRemainingFreeUses(PDO $db, string $toolSlug): int {
    $identifier = getFreeIdentifier();
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM free_usage 
         WHERE identifier = ? AND tool_slug = ? AND used_at > DATE_SUB(NOW(), INTERVAL 1 DAY)"
    );
    $stmt->execute([$identifier, $toolSlug]);
    $used = (int) $stmt->fetchColumn();
    return max(0, FREE_DAILY_LIMIT - $used);
}

/**
 * Generate free usage identifier (IP + UA hash or user ID)
 */
function getFreeIdentifier(): string {
    if (isset($_SESSION['user_id'])) {
        return 'u_' . $_SESSION['user_id'];
    }
    return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
}

/**
 * Get or generate CSRF token
 */
function getCsrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Generate CSRF hidden input
 */
function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars(getCsrfToken()) . '">';
}

/**
 * Validate CSRF token from POST
 */
function validateCsrf(): bool {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Set a flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash messages
 */
function getFlashes(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/**
 * Render flash messages as Bootstrap alerts
 */
function renderFlashes(): string {
    $html = '';
    foreach (getFlashes() as $flash) {
        $type = htmlspecialchars($flash['type']);
        $msg  = htmlspecialchars($flash['message']);
        $html .= "<div class=\"alert alert-{$type} alert-dismissible fade show\" role=\"alert\">
                    {$msg}
                    <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
                  </div>";
    }
    return $html;
}

/**
 * Sanitize output
 */
function e($str): string {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * JSON response helper
 */
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get the tool URL from its slug and category
 */
function toolUrl(array $tool): string {
    return BASE_URL . '/tools/' . $tool['category'] . '/' . $tool['slug'] . '.php';
}

/**
 * Format file size
 */
function formatFileSize(int $bytes): string {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

/**
 * Time ago helper
 */
function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

/**
 * Validate file upload (MIME + extension + size)
 */
function validateUpload(array $file, array $allowedMimes, int $maxSizeMB = MAX_FILE_MB): array {
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed (error code: ' . $file['error'] . ').';
        return $errors;
    }

    // Size check
    if ($file['size'] > $maxSizeMB * 1024 * 1024) {
        $errors[] = "File exceeds maximum size of {$maxSizeMB}MB.";
    }

    // MIME check
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedMimes)) {
        $errors[] = "Invalid file type: {$mime}";
    }

    return $errors;
}

/**
 * Generate a secure temporary filename in uploads dir
 */
function tempUploadPath(string $ext): string {
    $name = 'tb_' . uniqid('', true) . '.' . $ext;
    return UPLOAD_DIR . $name;
}

/**
 * Get a setting from the database
 */
function getSetting(string $key, $default = null) {
    global $pdo;
    static $settings = [];
    if (empty($settings)) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) { return $default; }
    }
    return $settings[$key] ?? $default;
}

/**
 * Update a setting in the database
 */
function updateSetting(string $key, string $value): bool {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

