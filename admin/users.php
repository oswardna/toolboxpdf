<?php
/**
 * ToolBox — Admin User Management
 *
 * All state-mutating actions use POST + CSRF token to prevent CSRF attacks.
 */

// Load dependencies before any HTML output so redirects work
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Subscription.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth($pdo);
$auth->requireAdmin();

$userModel = new User($pdo);
$subModel  = new Subscription($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrf()) {
        setFlash('danger', 'Invalid request. Please try again.');
        redirect(BASE_URL . '/admin/users.php');
    }

    if ($_POST['action'] === 'grant_pro') {
        $uid       = (int)$_POST['user_id'];
        $plan      = $_POST['plan'];
        $expiresAt = $_POST['expires_at'] . ' 23:59:59';

        $subModel->revokePro($uid);
        $subModel->grantPro($uid, $plan, $expiresAt);

        setFlash('success', 'Pro access granted to user.');
        redirect(BASE_URL . '/admin/users.php');

    } elseif ($_POST['action'] === 'revoke_pro') {
        $uid = (int)$_POST['user_id'];
        $subModel->revokePro($uid);
        setFlash('success', 'Pro access revoked for user.');
        redirect(BASE_URL . '/admin/users.php');

    } elseif ($_POST['action'] === 'delete_user') {
        $uid = (int)$_POST['user_id'];
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$uid]);
        setFlash('success', 'User deleted.');
        redirect(BASE_URL . '/admin/users.php');
    }
}

// Now safe to output HTML
$pageTitle = 'User Management';
require_once __DIR__ . '/header.php';

$users = $userModel->getAll(100);
?>

<div class="container-fluid">
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">User Management</h3>
        <p class="text-muted small mb-0">Manage platform users and access levels</p>
    </div>
</div>

<div class="tb-dash-card">
    <div class="table-responsive">
        <table class="table tb-table align-middle">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?= e($u['name']) ?></div>
                        <?php if ($u['role'] === 'admin'): ?><span class="badge bg-danger" style="font-size:0.6rem">ADMIN</span><?php endif; ?>
                    </td>
                    <td class="small text-muted"><?= e($u['email']) ?></td>
                    <td>
                        <?php if ($u['is_pro']): ?>
                            <span class="badge bg-success">Pro</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Free</span>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-dark" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#grantProModal<?= $u['id'] ?>"><i class="bi bi-star me-2"></i><?= $u['is_pro'] ? 'Update Pro' : 'Grant Pro' ?></a></li>

                                <?php if ($u['is_pro']): ?>
                                <li>
                                    <form method="POST">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="revoke_pro">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="dropdown-item text-warning" onclick="return confirm('Revoke premium access?')"><i class="bi bi-x-circle me-2"></i>Revoke Pro</button>
                                    </form>
                                </li>
                                <?php endif; ?>

                                <?php if ($u['role'] !== 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" onsubmit="return confirm('Permanently delete this user?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                    </form>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- Grant Pro Modal -->
                        <div class="modal fade" id="grantProModal<?= $u['id'] ?>" tabindex="-1" style="text-align: left;">
                            <div class="modal-dialog">
                                <form method="POST" class="modal-content bg-dark text-white border-secondary">
                                    <?= csrfField() ?>
                                    <div class="modal-header border-secondary">
                                        <h5 class="modal-title">Grant Pro: <?= e($u['name']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="grant_pro">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">

                                        <div class="mb-3">
                                            <label class="form-label text-muted">Plan Type</label>
                                            <select name="plan" class="form-select bg-dark text-white border-secondary" required>
                                                <option value="monthly">Monthly</option>
                                                <option value="yearly">Yearly</option>
                                                <option value="lifetime">Lifetime</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Expiration Date</label>
                                            <input type="date" name="expires_at" class="form-control bg-dark text-white border-secondary" required value="<?= date('Y-m-d', strtotime('+1 month')) ?>">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-secondary">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn tb-btn-primary">Grant Access</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

