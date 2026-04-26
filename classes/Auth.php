<?php
/**
 * ToolBox — Authentication Class
 */
class Auth {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Register a new user
     */
    public function register(string $name, string $email, string $password): array {
        // Validate
        $errors = [];
        if (strlen($name) < 2)        $errors[] = 'Name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (strlen($password) < 6)     $errors[] = 'Password must be at least 6 characters.';

        // Check duplicate
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'Email already registered.';

        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);

        $userId = (int) $this->db->lastInsertId();

        // Auto-login
        $this->setSession($userId, 'user', $name, $email);

        return ['success' => true, 'user_id' => $userId];
    }

    /**
     * Log in a user
     */
    public function login(string $email, string $password): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $this->setSession($user['id'], $user['role'], $user['name'], $user['email']);
            return $user;
        }
        return null;
    }

    /**
     * Log out
     */
    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if current user has active Pro subscription
     */
    public function isPro(): bool {
        if (!$this->isLoggedIn()) return false;
        
        // Admins are always considered Pro
        if ($this->isAdmin()) return true;

        $stmt = $this->db->prepare(
            "SELECT id FROM subscriptions 
             WHERE user_id = ? AND status = 'active' AND expires_at > NOW() 
             LIMIT 1"
        );
        $stmt->execute([$_SESSION['user_id']]);
        return (bool) $stmt->fetch();
    }

    /**
     * Require Pro — returns JSON error if not Pro
     */
    public function requirePro(): void {
        if (!$this->isPro()) {
            http_response_code(403);
            header('Content-Type: application/json');
            exit(json_encode([
                'error' => 'Pro subscription required',
                'upgrade_url' => BASE_URL . '/dashboard/billing.php'
            ]));
        }
    }

    /**
     * Require login — redirect to login page if not logged in
     */
    public function requireLogin(string $redirect = ''): void {
        if (!$this->isLoggedIn()) {
            $returnTo = $redirect ?: ($_SERVER['REQUEST_URI'] ?? '');
            header('Location: ' . BASE_URL . '/auth/login.php?return=' . urlencode($returnTo));
            exit;
        }
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin(): bool {
        return $this->isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    /**
     * Require admin role
     */
    public function requireAdmin(): void {
        if (!$this->isAdmin()) {
            http_response_code(403);
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }

    /**
     * Get current user ID
     */
    public function userId(): ?int {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    /**
     * Get current user data from session
     */
    public function user(): array {
        return [
            'id'    => $_SESSION['user_id'] ?? null,
            'name'  => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role'  => $_SESSION['user_role'] ?? 'user',
        ];
    }

    /**
     * Generate a CSRF token
     */
    public function csrfToken(): string {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrf(string $token): bool {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Set session variables
     */
    private function setSession(int $id, string $role, string $name, string $email): void {
        $_SESSION['user_id']    = $id;
        $_SESSION['user_role']  = $role;
        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;
    }
}
