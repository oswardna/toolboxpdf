<?php
/**
 * ToolBox — Admin Profile Settings
 */

// Load dependencies early (no HTML output yet) so POST can redirect
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth($pdo);
$auth->requireAdmin();

$userModel = new User($pdo);
$userId = $auth->userId();
$currentUser = $userModel->getById($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name && $email) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, $userId])) {
                setFlash('success', 'Profile updated successfully.');
            } else {
                setFlash('danger', 'Failed to update profile. Email might be in use.');
            }
        }
        redirect(BASE_URL . '/admin/profile.php');
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        
        if (password_verify($currentPass, $currentUser['password'])) {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $userId]);
            setFlash('success', 'Password changed successfully.');
        } else {
            setFlash('danger', 'Incorrect current password.');
        }
        redirect(BASE_URL . '/admin/profile.php');
    }
}

// Now safe to output HTML
$pageTitle = 'Admin Profile';
require_once __DIR__ . '/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Admin Profile</h3>
        <p class="text-muted small mb-0">Manage your personal account settings</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="tb-dash-card h-100">
            <h5 class="fw-bold mb-4"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Profile Details</h5>
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">Full Name</label>
                    <input type="text" name="name" class="form-control bg-dark text-white border-secondary" value="<?= e($currentUser['name']) ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control bg-dark text-white border-secondary" value="<?= e($currentUser['email']) ?>" required>
                </div>
                <button type="submit" class="btn tb-btn-primary w-100">Save Profile Changes</button>
            </form>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="tb-dash-card h-100">
            <h5 class="fw-bold mb-4"><i class="bi bi-shield-lock-fill me-2 text-warning"></i>Change Password</h5>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">Current Password</label>
                    <input type="password" name="current_password" class="form-control bg-dark text-white border-secondary" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small fw-semibold">New Password</label>
                    <input type="password" name="new_password" class="form-control bg-dark text-white border-secondary" required minlength="6">
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold">Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
