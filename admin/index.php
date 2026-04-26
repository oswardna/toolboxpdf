<?php
/**
 * ToolBox — Admin Dashboard
 */
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/header.php';

$userModel = new User($pdo);
$subModel  = new Subscription($pdo);
$toolModel = new Tool($pdo);

$totalUsers = $userModel->count();
$subStats   = $subModel->getStats();
$toolUsage  = $toolModel->getUsageStats(30);

// Recent signups
$recentUsers = $userModel->getAll(5);
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h3 class="fw-bold mb-1">Overview Dashboard</h3>
        <p class="text-muted small mb-0">Real-time platform analytics and performance metrics</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="bi bi-display me-2"></i>Live Site</a>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-5">
    <div class="col-sm-6 col-xl-3">
        <div class="tb-dash-card border-0 shadow-sm position-relative overflow-hidden h-100" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
            </div>
            <div class="tb-stat-label text-muted fw-bold text-uppercase tracking-wider mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Total Users</div>
            <div class="tb-stat-number text-dark" style="font-size: 2.5rem; font-weight: 800;"><?= number_format($totalUsers) ?></div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="tb-dash-card border-0 shadow-sm position-relative overflow-hidden h-100" style="background: linear-gradient(135deg, #ebfbee 0%, #d3f9d8 100%);">
            <div class="position-absolute top-0 end-0 p-3 opacity-25 text-success">
                <i class="bi bi-star-fill" style="font-size: 3rem;"></i>
            </div>
            <div class="tb-stat-label text-success fw-bold text-uppercase tracking-wider mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Pro Subscribers</div>
            <div class="tb-stat-number text-success" style="font-size: 2.5rem; font-weight: 800;"><?= number_format($subStats['active']) ?></div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="tb-dash-card border-0 shadow-sm position-relative overflow-hidden h-100" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);">
            <div class="position-absolute top-0 end-0 p-3 opacity-25 text-warning">
                <i class="bi bi-wallet-fill" style="font-size: 3rem;"></i>
            </div>
            <div class="tb-stat-label text-warning fw-bold text-uppercase tracking-wider mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Total Revenue</div>
            <div class="tb-stat-number text-warning" style="font-size: 2.5rem; font-weight: 800;">$<?= number_format($subStats['revenue'], 0) ?></div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="tb-dash-card border-0 shadow-sm position-relative overflow-hidden h-100" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
            <div class="position-absolute top-0 end-0 p-3 opacity-25 text-primary">
                <i class="bi bi-grid-fill" style="font-size: 3rem;"></i>
            </div>
            <div class="tb-stat-label text-primary fw-bold text-uppercase tracking-wider mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Active Tools</div>
            <div class="tb-stat-number text-primary" style="font-size: 2.5rem; font-weight: 800;"><?= count($toolUsage) ?></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Top Tools -->
    <div class="col-lg-5">
        <div class="tb-dash-card h-100 border-0 shadow-sm">
            <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="bi bi-bar-chart-line-fill me-3 text-primary fs-4"></i> Tool Analytics (30 Days)
            </h5>
            <?php $topTools = array_slice($toolUsage, 0, 8); ?>
            <?php foreach ($topTools as $t): ?>
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded hover-bg-light transition">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px; height:40px;">
                        <i class="bi bi-<?= e($t['icon'] ?? 'gear') ?>"></i>
                    </div>
                    <span class="fw-semibold text-dark"><?= e($t['name']) ?></span>
                </div>
                <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm"><?= $t['usage_count'] ?> requests</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($topTools)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1 mb-3 d-block"></i>
                <p class="text-muted small mb-0">No tool usage data collected yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="col-lg-7">
        <div class="tb-dash-card h-100 border-0 shadow-sm d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 d-flex align-items-center">
                    <i class="bi bi-person-badge-fill me-3 text-success fs-4"></i> Recent Signups
                </h5>
                <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-sm btn-light border text-muted fw-bold">View Directory</a>
            </div>
            
            <div class="table-responsive flex-grow-1">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">User Name</th>
                            <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">Email Account</th>
                            <th class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">Status</th>
                            <th class="text-uppercase text-muted text-end" style="font-size: 0.75rem; letter-spacing: 1px;">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td class="fw-bold text-dark border-bottom-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width:32px; height:32px; font-size:0.8rem;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <?= e($u['name']) ?>
                                </div>
                            </td>
                            <td class="small text-muted border-bottom-0 py-3"><?= e($u['email']) ?></td>
                            <td class="border-bottom-0 py-3">
                                <?php if ($u['is_pro']): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-2 py-1"><i class="bi bi-star-fill me-1"></i>PRO</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-bold px-2 py-1">FREE</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted text-end border-bottom-0 py-3"><?= timeAgo($u['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.hover-bg-light:hover { background-color: #f8f9fa; }
.transition { transition: all 0.2s ease; }
</style>

<?php require_once __DIR__ . '/footer.php'; ?>
