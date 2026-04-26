<?php
/**
 * ToolBox — Billing & Subscription
 */
require_once __DIR__ . '/../includes/init.php';

// Ensure user is logged in before any HTML output
$auth->requireLogin();

// Set page meta first so header.php renders the correct <title>
$pageTitle = 'Billing';
require_once __DIR__ . '/../includes/header.php';

$subModel  = new Subscription($pdo);
$activeSub = $subModel->getActive($auth->userId());
$history   = $subModel->getHistory($auth->userId());
$isPro     = $auth->isPro();

$selectedPlan  = $_GET['plan'] ?? '';
$priceMonthly  = getSetting('price_monthly', '1.00');
$priceYearly   = getSetting('price_yearly', '9.00');
$currency      = getSetting('currency', 'USD');
$stripeEnabled = getSetting('stripe_enabled', '0') === '1';
$flwEnabled    = getSetting('flw_enabled', '0') === '1';
?>

<div class="container py-4">
    <h3 class="fw-bold mb-1">Billing &amp; Subscription</h3>
    <p class="text-muted mb-4">Manage your plan and payment history.</p>

    <!-- Current Plan -->
    <div class="tb-dash-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h6 class="fw-bold mb-1">
                    <i class="bi bi-<?= $isPro ? 'star-fill text-warning' : 'person' ?> me-2"></i>
                    Current Plan: <?= $isPro ? 'Pro (' . ucfirst($activeSub['plan']) . ')' : 'Free' ?>
                </h6>
                <?php if ($isPro): ?>
                    <p class="text-muted small mb-0">
                        Active until <?= date('F j, Y', strtotime($activeSub['expires_at'])) ?>
                        · via <?= ucfirst($activeSub['gateway']) ?>
                    </p>
                <?php else: ?>
                    <p class="text-muted small mb-0">Upgrade to unlock all premium tools.</p>
                <?php endif; ?>
            </div>
            <?php if ($isPro): ?>
                <span class="badge bg-success px-3 py-2">Active</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$isPro): ?>
    <!-- Pricing Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="tb-pricing-card <?= $selectedPlan === 'monthly' ? 'popular' : '' ?>" id="plan-monthly">
                <div class="text-center mb-3">
                    <h5 class="fw-bold">Pro Monthly</h5>
                    <div class="tb-price"><?= e($currency) ?> <?= e($priceMonthly) ?></div>
                    <div class="tb-price-period">/month</div>
                </div>
                <ul class="list-unstyled small mb-4">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>All 30+ tools</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Unlimited usage</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Cancel anytime</li>
                </ul>
                <div class="d-grid gap-2">
                    <?php if ($stripeEnabled): ?>
                    <button class="btn tb-btn-primary" onclick="startPayment('monthly','stripe')">
                        <i class="bi bi-credit-card me-2"></i>Pay with Stripe
                    </button>
                    <?php endif; ?>
                    <?php if ($flwEnabled): ?>
                    <button class="btn tb-btn-outline" onclick="startPayment('monthly','flutterwave')">
                        <i class="bi bi-wallet2 me-2"></i>Pay with Flutterwave
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="tb-pricing-card <?= $selectedPlan !== 'monthly' ? 'popular' : '' ?>" id="plan-yearly">
                <div class="text-center mb-3">
                    <h5 class="fw-bold">Pro Yearly</h5>
                    <div class="tb-price"><?= e($currency) ?> <?= e($priceYearly) ?></div>
                    <div class="tb-price-period">/year <span class="badge bg-success ms-1">Save 25%</span></div>
                </div>
                <ul class="list-unstyled small mb-4">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Everything in monthly</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Best value</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>All future tools included</li>
                </ul>
                <div class="d-grid gap-2">
                    <?php if ($stripeEnabled): ?>
                    <button class="btn tb-btn-primary" onclick="startPayment('yearly','stripe')">
                        <i class="bi bi-credit-card me-2"></i>Pay with Stripe
                    </button>
                    <?php endif; ?>
                    <?php if ($flwEnabled): ?>
                    <button class="btn tb-btn-outline" onclick="startPayment('yearly','flutterwave')">
                        <i class="bi bi-wallet2 me-2"></i>Pay with Flutterwave
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment History -->
    <div class="tb-dash-card">
        <h6 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Payment History</h6>
        <?php if (empty($history)): ?>
            <p class="text-muted small mb-0">No payment history yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table tb-table mb-0">
                    <thead><tr><th>Plan</th><th>Gateway</th><th>Status</th><th>Started</th><th>Expires</th></tr></thead>
                    <tbody>
                        <?php foreach ($history as $sub): ?>
                        <tr>
                            <td class="fw-semibold"><?= ucfirst($sub['plan']) ?></td>
                            <td><?= ucfirst($sub['gateway']) ?></td>
                            <td>
                                <?php
                                switch ($sub['status']) {
                                    case 'active':
                                        echo '<span class="badge bg-success">Active</span>';
                                        break;
                                    case 'cancelled':
                                        echo '<span class="badge bg-warning text-dark">Cancelled</span>';
                                        break;
                                    case 'expired':
                                        echo '<span class="badge bg-secondary">Expired</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-secondary">' . e($sub['status']) . '</span>';
                                        break;
                                }
                                ?>
                            </td>
                            <td class="small"><?= date('M d, Y', strtotime($sub['started_at'])) ?></td>
                            <td class="small"><?= date('M d, Y', strtotime($sub['expires_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function startPayment(plan, gateway) {
    if (gateway === 'stripe') {
        window.location.href = '<?= BASE_URL ?>/api/checkout-stripe.php?plan=' + plan;
    } else {
        window.location.href = '<?= BASE_URL ?>/api/checkout-flutterwave.php?plan=' + plan;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
