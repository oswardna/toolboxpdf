<?php
/**
 * ToolBox — Admin Tool Management
 */
$pageTitle = 'Tool Management';
require_once __DIR__ . '/header.php';

$toolModel = new Tool($pdo);

// Handle Toggles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrf()) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/tools.php');
    }

    $id = (int)$_POST['id'];
    $val = (int)$_POST['val'];

    if ($_POST['action'] === 'toggle_premium') {
        $pdo->prepare("UPDATE tools SET is_premium = ? WHERE id = ?")->execute([$val, $id]);
        setFlash('success', 'Tool premium status updated.');
    } elseif ($_POST['action'] === 'toggle_active') {
        $pdo->prepare("UPDATE tools SET is_active = ? WHERE id = ?")->execute([$val, $id]);
        setFlash('success', 'Tool visibility updated.');
    }
    redirect(BASE_URL . '/admin/tools.php');
}

$tools = $toolModel->getAllAdmin();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Tool Management</h3>
        <p class="text-muted small mb-0">Toggle tool availability and pricing tiers</p>
    </div>
</div>

<div class="tb-dash-card">
    <div class="table-responsive">
        <table class="table tb-table align-middle">
            <thead>
                <tr>
                    <th>Tool Name</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Tier</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tools as $t): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="tb-feature-icon me-3" style="width:32px; height:32px; font-size: 0.9rem;">
                                <i class="bi bi-<?= e($t['icon']) ?>"></i>
                            </div>
                            <div class="fw-bold"><?= e($t['name']) ?></div>
                        </div>
                    </td>
                    <td><span class="badge bg-dark text-uppercase" style="font-size:0.7rem"><?= e($t['category']) ?></span></td>
                    <td>
                        <?php if ($t['is_client']): ?>
                            <span class="text-info small"><i class="bi bi-browser-chrome me-1"></i>Client-Side</span>
                        <?php else: ?>
                            <span class="text-warning small"><i class="bi bi-cpu me-1"></i>Server-Side</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="toggle_premium">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="val" value="<?= $t['is_premium'] ? 0 : 1 ?>">
                            <button type="submit" class="badge <?= $t['is_premium'] ? 'bg-primary' : 'bg-success' ?> border-0">
                                <i class="bi bi-<?= $t['is_premium'] ? 'star-fill' : 'unlock-fill' ?> me-1"></i>
                                <?= $t['is_premium'] ? 'Premium' : 'Free' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="toggle_active">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="val" value="<?= $t['is_active'] ? 0 : 1 ?>">
                            <button type="submit" class="badge <?= $t['is_active'] ? 'bg-success' : 'bg-danger' ?> border-0">
                                <?= $t['is_active'] ? 'Active' : 'Disabled' ?>
                            </button>
                        </form>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-dark" title="Edit Metadata (Coming Soon)"><i class="bi bi-pencil"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
