<?php
/**
 * ToolBox — Tool Class
 */
class Tool {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all active tools
     */
    public function getAll(): array {
        $stmt = $this->db->query('SELECT * FROM tools WHERE is_active = 1 ORDER BY sort_order ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tools by category
     */
    public function getByCategory(string $category): array {
        $stmt = $this->db->prepare('SELECT * FROM tools WHERE category = ? AND is_active = 1 ORDER BY sort_order ASC');
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tool by slug
     */
    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare('SELECT * FROM tools WHERE slug = ?');
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Check if a tool is free
     */
    public function isFree(string $slug): bool {
        $tool = $this->getBySlug($slug);
        return $tool && !$tool['is_premium'];
    }

    /**
     * Get all tools including inactive (admin)
     */
    public function getAllAdmin(): array {
        $stmt = $this->db->query('SELECT * FROM tools ORDER BY category, sort_order ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Toggle tool active status
     */
    public function toggleActive(int $id): bool {
        $stmt = $this->db->prepare('UPDATE tools SET is_active = NOT is_active WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Toggle tool premium status
     */
    public function togglePremium(int $id): bool {
        $stmt = $this->db->prepare('UPDATE tools SET is_premium = NOT is_premium WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Update tool details
     */
    public function update(int $id, array $data): bool {
        $allowed = ['name', 'description', 'icon', 'is_premium', 'is_active', 'sort_order'];
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $sets[] = "`{$key}` = ?";
                $params[] = $value;
            }
        }
        if (empty($sets)) return false;
        $params[] = $id;
        $stmt = $this->db->prepare('UPDATE tools SET ' . implode(', ', $sets) . ' WHERE id = ?');
        return $stmt->execute($params);
    }

    /**
     * Get tool usage stats
     */
    public function getUsageStats(int $days = 30): array {
        $stmt = $this->db->prepare(
            "SELECT t.id, t.slug, t.name, t.category, t.icon, COUNT(tj.id) as usage_count
             FROM tools t
             LEFT JOIN tool_jobs tj ON tj.tool_slug = t.slug AND tj.created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY t.id
             ORDER BY usage_count DESC"
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get counts by category
     */
    public function getCounts(): array {
        $stmt = $this->db->query(
            "SELECT category, COUNT(*) as total, 
                    SUM(is_premium = 0) as free_count, 
                    SUM(is_premium = 1) as premium_count 
             FROM tools WHERE is_active = 1 GROUP BY category"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
