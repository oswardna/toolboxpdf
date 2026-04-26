<?php
/**
 * ToolBox — User Dashboard
 */
// Set page meta first so header.php renders the correct <title>
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

// $auth and $pdo are provided by header.php
$auth->requireLogin();

$userModel = new User($pdo);
$subModel  = new Subscription($pdo);
$userData  = $userModel->getById($auth->userId());
$activeSub = $subModel->getActive($auth->userId());
$isPro     = $auth->isPro();

// Recent tool jobs
$stmt = $pdo->prepare('SELECT * FROM tool_jobs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
$stmt->execute([$auth->userId()]);
$recentJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Quick stats
$stmt = $pdo->prepare('SELECT COUNT(*) FROM tool_jobs WHERE user_id = ?');
$stmt->execute([$auth->userId()]);
$totalJobs = (int)$stmt->fetchColumn();
?>

<div class="container py-4">
    <!-- Welcome -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Welcome, <?= e($userData['name']) ?> 👋</h3>
            <p class="text-muted mb-0 small">Manage your tools, usage, and subscription.</p>
        </div>
        <?php if (!$isPro): ?>
        <a href="<?= BASE_URL ?>/dashboard/billing.php" class="btn tb-btn-primary">
            <i class="bi bi-rocket-takeoff me-2"></i>Upgrade to Pro
        </a>
        <?php endif; ?>
    </div>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="tb-dash-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="tb-feature-icon"><i class="bi bi-person"></i></div>
                    <div>
                        <div class="small text-muted">Plan</div>
                        <div class="fw-bold"><?= $isPro ? 'Pro (' . ucfirst($activeSub['plan'] ?? 'Lifetime') . ')' : 'Free' ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="tb-dash-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="tb-feature-icon"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="small text-muted">Total Jobs</div>
                        <div class="fw-bold"><?= $totalJobs ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="tb-dash-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="tb-feature-icon"><i class="bi bi-calendar"></i></div>
                    <div>
                        <div class="small text-muted"><?= $isPro ? 'Expires' : 'Member Since' ?></div>
                        <div class="fw-bold"><?= $isPro ? ($activeSub ? date('M d, Y', strtotime($activeSub['expires_at'])) : 'Never') : date('M d, Y', strtotime($userData['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="tb-dash-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="tb-feature-icon"><i class="bi bi-<?= $isPro ? 'star-fill' : 'star' ?>"></i></div>
                    <div>
                        <div class="small text-muted">Status</div>
                        <div class="fw-bold">
                            <?php if ($isPro): ?>
                                <span class="text-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                            <?php else: ?>
                                <span class="text-muted">Free Tier</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Activity -->
        <div class="col-lg-8">
            <div class="tb-dash-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Recent Activity</h6>
                </div>
                <?php if (empty($recentJobs)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size:2rem"></i>
                        <p class="text-muted small mt-2 mb-0">No activity yet. Try a tool!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table tb-table mb-0">
                            <thead>
                                <tr>
                                    <th>Tool</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentJobs as $job): ?>
                                <tr>
                                    <td><span class="fw-semibold"><?= e($job['tool_slug']) ?></span></td>
                                    <td>
                                        <?php
                                        $statusBadge = match($job['status']) {
                                            'done'       => '<span class="badge bg-success">Done</span>',
                                            'processing' => '<span class="badge bg-info">Processing</span>',
                                            'failed'     => '<span class="badge bg-danger">Failed</span>',
                                            'expired'    => '<span class="badge bg-secondary">Expired</span>',
                                            default      => '<span class="badge bg-secondary">Pending</span>',
                                        };
                                        echo $statusBadge;
                                        ?>
                                    </td>
                                    <td class="text-muted small"><?= timeAgo($job['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Access -->
        <div class="col-lg-4">
            <div class="tb-dash-card mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-lightning me-2"></i>Quick Access</h6>
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/tools/pdf/pdf-split.php" class="btn tb-btn-outline btn-sm text-start"><i class="bi bi-scissors me-2"></i>Split PDF</a>
                    <a href="<?= BASE_URL ?>/tools/pdf/pdf-merge.php" class="btn tb-btn-outline btn-sm text-start"><i class="bi bi-union me-2"></i>Merge PDF</a>
                    <a href="<?= BASE_URL ?>/tools/img/img-compress.php" class="btn tb-btn-outline btn-sm text-start"><i class="bi bi-file-earmark-zip me-2"></i>Compress Image</a>
                    <a href="<?= BASE_URL ?>/" class="btn tb-btn-outline btn-sm text-start"><i class="bi bi-grid me-2"></i>All Tools</a>
                </div>
            </div>

            <?php if (!$isPro): ?>
            <div class="tb-dash-card" style="border-color: var(--tb-primary); background: rgba(124,58,237,0.05);">
                <h6 class="fw-bold mb-2"><i class="bi bi-star-fill text-warning me-2"></i>Upgrade to Pro</h6>
                <p class="text-muted small mb-3">Unlock all 30+ tools with unlimited usage from just $1/month.</p>
                <a href="<?= BASE_URL ?>/dashboard/billing.php" class="btn tb-btn-primary btn-sm w-100">View Plans</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
