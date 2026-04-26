<?php
/**
 * ToolBox — Subscription Class
 */
class Subscription {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get active subscription for a user
     */
    public function getActive(int $userId): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM subscriptions 
             WHERE user_id = ? AND status = 'active' AND expires_at > NOW() 
             ORDER BY expires_at DESC LIMIT 1"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Check if user has active subscription
     */
    public function isActive(int $userId): bool {
        return $this->getActive($userId) !== null;
    }

    /**
     * Create a new subscription
     */
    public function create(int $userId, string $plan, string $paymentId, string $gateway): int {
        $expiresAt = $plan === 'yearly'
            ? date('Y-m-d H:i:s', strtotime('+1 year'))
            : date('Y-m-d H:i:s', strtotime('+1 month'));

        $stmt = $this->db->prepare(
            'INSERT INTO subscriptions (user_id, plan, status, started_at, expires_at, payment_id, gateway) 
             VALUES (?, ?, ?, NOW(), ?, ?, ?)'
        );
        $stmt->execute([$userId, $plan, 'active', $expiresAt, $paymentId, $gateway]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Cancel a subscription
     */
    public function cancel(int $subscriptionId): bool {
        $stmt = $this->db->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE id = ?");
        return $stmt->execute([$subscriptionId]);
    }

    /**
     * Cancel by payment ID (webhook use)
     */
    public function cancelByPaymentId(string $paymentId): bool {
        $stmt = $this->db->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE payment_id = ?");
        return $stmt->execute([$paymentId]);
    }

    /**
     * Get all subscriptions for a user (history)
     */
    public function getHistory(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM subscriptions WHERE user_id = ? ORDER BY started_at DESC LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get subscription stats (admin)
     */
    public function getStats(): array {
        $stats = [];

        $stmt = $this->db->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active' AND expires_at > NOW()");
        $stats['active'] = (int) $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM subscriptions WHERE plan = 'monthly' AND status = 'active'");
        $stats['monthly'] = (int) $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM subscriptions WHERE plan = 'yearly' AND status = 'active'");
        $stats['yearly'] = (int) $stmt->fetchColumn();

        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(CASE WHEN plan='monthly' THEN 1.00 WHEN plan='yearly' THEN 9.00 END), 0) 
             FROM subscriptions WHERE status = 'active'"
        );
        $stats['revenue'] = (float) $stmt->fetchColumn();

        return $stats;
    }

    /**
     * Manually grant Pro (admin use / testing)
     */
    public function grantPro(int $userId, string $plan = 'monthly', ?string $expiresAt = null): int {
        if ($expiresAt === null) {
            $expiresAt = $plan === 'yearly'
                ? date('Y-m-d H:i:s', strtotime('+1 year'))
                : date('Y-m-d H:i:s', strtotime('+1 month'));
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO subscriptions (user_id, plan, status, started_at, expires_at, payment_id, gateway) 
             VALUES (?, ?, 'active', NOW(), ?, ?, 'manual')"
        );
        $stmt->execute([$userId, $plan, $expiresAt, 'manual_' . uniqid()]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Revoke Pro access for a user
     */
    public function revokePro(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE user_id = ? AND status = 'active'");
        return $stmt->execute([$userId]);
    }

    /**
     * Expire all overdue subscriptions
     */
    public function expireOverdue(): int {
        $stmt = $this->db->prepare(
            "UPDATE subscriptions SET status = 'expired' 
             WHERE status = 'active' AND expires_at <= NOW()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }
}
