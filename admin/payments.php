<?php
/**
 * ToolBox — Admin Payment Management
 */
$pageTitle = 'Payments & Subscriptions';
require_once __DIR__ . '/header.php';

$stmt = $pdo->query("
    SELECT s.*, u.name as user_name, u.email as user_email 
    FROM subscriptions s
    LEFT JOIN users u ON s.user_id = u.id
    ORDER BY s.created_at DESC
");
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$revenueStats = $pdo->query("
    SELECT 
        SUM(CASE WHEN plan='monthly' THEN 1.00 WHEN plan='yearly' THEN 9.00 ELSE 0 END) as total_rev,
        COUNT(*) as total_count
    FROM subscriptions 
    WHERE status = 'active'
")->fetch(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Payments &amp; Subscriptions</h3>
        <p class="text-muted small mb-0">View transaction history and revenue stats</p>
    </div>
    <div class="tb-dash-card py-2 px-3">
        <span class="small text-muted me-2">Total Revenue:</span>
        <span class="fw-bold text-success">$<?= number_format($revenueStats['total_rev'] ?? 0, 2) ?></span>
    </div>
</div>

<div class="tb-dash-card">
    <div class="table-responsive">
        <table class="table tb-table align-middle">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Gateway</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Payment ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?= e($p['user_name'] ?? 'Deleted User') ?></div>
                        <div class="small text-muted"><?= e($p['user_email'] ?? '') ?></div>
                    </td>
                    <td><span class="badge bg-dark text-uppercase"><?= e($p['plan']) ?></span></td>
                    <td><?= ucfirst($p['gateway']) ?></td>
                    <td class="fw-bold"><?= e($currency ?? 'USD') ?> <?= $p['plan'] === 'yearly' ? number_format((float)getSetting('price_yearly', '9.00'), 2) : number_format((float)getSetting('price_monthly', '1.00'), 2) ?></td>
                    <td>
                        <?php
                        echo match($p['status']) {
                            'active'    => '<span class="badge bg-success">Active</span>',
                            'cancelled' => '<span class="badge bg-warning text-dark">Cancelled</span>',
                            'expired'   => '<span class="badge bg-secondary">Expired</span>',
                            default     => '<span class="badge bg-secondary">' . e($p['status']) . '</span>',
                        };
                        ?>
                    </td>
                    <td class="small"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                    <td class="small font-monospace text-muted"><?= e($p['payment_id']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No payments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
