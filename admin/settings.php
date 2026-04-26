<?php
/**
 * ToolBox — Admin System Settings
 */

// Load dependencies early (no HTML output yet) so POST can redirect
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth($pdo);
$auth->requireAdmin();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        setFlash('danger', 'Invalid request. Please try again.');
        redirect(BASE_URL . '/admin/settings.php');
    }
    // Checkbox defaults (unchecked checkboxes aren't submitted)
    $checkboxes = ['stripe_enabled', 'flw_enabled'];
    foreach ($checkboxes as $cb) {
        if (!isset($_POST['settings'][$cb])) {
            $_POST['settings'][$cb] = '0';
        }
    }

    // Save Settings
    foreach ($_POST['settings'] as $key => $value) {
        updateSetting($key, $value);
    }
    setFlash('success', 'Settings updated successfully.');
    redirect(BASE_URL . '/admin/settings.php');
}

// Now safe to output HTML
$pageTitle = 'System Settings';
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">System Settings</h3>
        <p class="text-muted small mb-0">Configure pricing, payments, and platform behavior</p>
    </div>
</div>

<form method="post">
    <?= csrfField() ?>
    <div class="row g-4">
        <!-- Pricing Settings -->
        <div class="col-lg-6">
            <div class="tb-dash-card h-100">
                <h6 class="fw-bold mb-4"><i class="bi bi-currency-dollar me-2"></i>Pricing & Subscriptions</h6>
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Currency</label>
                    <select name="settings[currency]" class="form-select tb-form-control">
                        <?php $curr = getSetting('currency', 'USD'); ?>
                        <option value="USD" <?= $curr === 'USD' ? 'selected' : '' ?>>USD</option>
                        <option value="NGN" <?= $curr === 'NGN' ? 'selected' : '' ?>>NGN</option>
                        <option value="EUR" <?= $curr === 'EUR' ? 'selected' : '' ?>>EUR</option>
                        <option value="GBP" <?= $curr === 'GBP' ? 'selected' : '' ?>>GBP</option>
                        <option value="KES" <?= $curr === 'KES' ? 'selected' : '' ?>>KES</option>
                        <option value="ZAR" <?= $curr === 'ZAR' ? 'selected' : '' ?>>ZAR</option>
                        <option value="GHS" <?= $curr === 'GHS' ? 'selected' : '' ?>>GHS</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Monthly Price</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-white"><?= e($curr) ?></span>
                        <input type="number" step="0.01" name="settings[price_monthly]" class="form-control tb-form-control" value="<?= e(getSetting('price_monthly', '1.00')) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Yearly Price</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-white"><?= e($curr) ?></span>
                        <input type="number" step="0.01" name="settings[price_yearly]" class="form-control tb-form-control" value="<?= e(getSetting('price_yearly', '9.00')) ?>">
                    </div>
                </div>

                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle me-2"></i>Prices updated here will apply to all new checkouts.
                </div>
            </div>
        </div>

        <!-- Stripe Settings -->
        <div class="col-lg-6">
            <div class="tb-dash-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0"><i class="bi bi-stripe me-2"></i>Stripe Integration</h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="settings[stripe_enabled]" value="1" <?= getSetting('stripe_enabled', '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small text-muted">Enable</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Environment Mode</label>
                    <select name="settings[stripe_mode]" class="form-select tb-form-control">
                        <option value="sandbox" <?= getSetting('stripe_mode', 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Test)</option>
                        <option value="live" <?= getSetting('stripe_mode') === 'live' ? 'selected' : '' ?>>Live (Production)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Publishable Key</label>
                    <input type="text" name="settings[stripe_publishable_key]" class="form-control tb-form-control" value="<?= e(getSetting('stripe_publishable_key', '')) ?>" placeholder="pk_test_... or pk_live_...">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Secret Key</label>
                    <input type="password" name="settings[stripe_secret_key]" class="form-control tb-form-control" value="<?= e(getSetting('stripe_secret_key', '')) ?>" placeholder="sk_test_... or sk_live_...">
                </div>
            </div>
        </div>

        <!-- Flutterwave Settings -->
        <div class="col-lg-6">
            <div class="tb-dash-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold mb-0"><span class="me-2">🦋</span>Flutterwave Integration</h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="settings[flw_enabled]" value="1" <?= getSetting('flw_enabled', '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small text-muted">Enable</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Environment Mode</label>
                    <select name="settings[flw_mode]" class="form-select tb-form-control">
                        <option value="sandbox" <?= getSetting('flw_mode', 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Test)</option>
                        <option value="live" <?= getSetting('flw_mode') === 'live' ? 'selected' : '' ?>>Live (Production)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">FLW Public Key</label>
                    <input type="text" name="settings[flw_public_key]" class="form-control tb-form-control" value="<?= e(getSetting('flw_public_key', '')) ?>" placeholder="FLWPUBK_TEST-... or FLWPUBK_LIVE-...">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">FLW Secret Key</label>
                    <input type="password" name="settings[flw_secret_key]" class="form-control tb-form-control" value="<?= e(getSetting('flw_secret_key', '')) ?>" placeholder="FLWSECK_TEST-... or FLWSECK_LIVE-...">
                </div>
            </div>
        </div>

        <!-- Global Save -->
        <div class="col-12 mt-4 text-end">
            <!-- Hidden inputs to ensure unchecked checkboxes submit a '0' value -->
            <input type="hidden" name="settings_present" value="1">
            <button type="submit" class="btn tb-btn-primary btn-lg px-5">
                <i class="bi bi-save me-2"></i>Save All Settings
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>
