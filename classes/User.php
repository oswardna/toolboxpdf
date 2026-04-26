<?php
/**
 * ToolBox — User Class
 */
class User {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get user by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT id, name, email, password, role, avatar, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get user by email
     */
    public function getByEmail(string $email): ?array {
        $stmt = $this->db->prepare('SELECT id, name, email, role, avatar, created_at FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Update user profile
     */
    public function updateProfile(int $id, string $name, string $email): bool {
        $stmt = $this->db->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        return $stmt->execute([$name, $email, $id]);
    }

    /**
     * Change password
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): array {
        $stmt = $this->db->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'Current password is incorrect.'];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);

        return ['success' => true];
    }

    /**
     * Get all users (admin)
     */
    public function getAll(int $limit = 50, int $offset = 0, string $search = ''): array {
        $sql = 'SELECT u.id, u.name, u.email, u.role, u.created_at,
                       (SELECT COUNT(*) FROM subscriptions s WHERE s.user_id = u.id AND s.status="active" AND s.expires_at > NOW()) as is_pro
                FROM users u';
        $params = [];
        
        if ($search) {
            $sql .= ' WHERE u.name LIKE ? OR u.email LIKE ?';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sql .= ' ORDER BY u.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count all users
     */
    public function count(string $search = ''): int {
        $sql = 'SELECT COUNT(*) FROM users';
        $params = [];
        if ($search) {
            $sql .= ' WHERE name LIKE ? OR email LIKE ?';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Delete user by ID
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Create password reset token
     */
    public function createPasswordReset(string $email): ?string {
        $user = $this->getByEmail($email);
        if (!$user) return null;

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->db->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$user['id'], $token, $expires]);

        return $token;
    }

    /**
     * Verify password reset token
     */
    public function verifyResetToken(string $token): ?array {
        $stmt = $this->db->prepare(
            'SELECT pr.*, u.email, u.name FROM password_resets pr 
             JOIN users u ON pr.user_id = u.id 
             WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0 
             LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): bool {
        $reset = $this->verifyResetToken($token);
        if (!$reset) return false;

        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $reset['user_id']]);
        $this->db->prepare('UPDATE password_resets SET used = 1 WHERE id = ?')->execute([$reset['id']]);

        return true;
    }
}
