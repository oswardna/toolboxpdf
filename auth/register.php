<?php
/**
 * ToolBox — Register Page
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth($pdo);
$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $result = $auth->register($name, $email, $password);
            if ($result['success']) {
                setFlash('success', 'Account created! Welcome to ToolBox.');
                $return = $_POST['return'] ?? '';
                redirect($return ?: BASE_URL . '/dashboard/');
            } else {
                $errors = $result['errors'];
            }
        }
    }
}

$pageTitle = 'Create Account';
$pageDesc  = 'Create your free ToolBox account to access all PDF and image tools.';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="tb-auth-page">
    <div class="container">
        <div class="tb-auth-card">
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>/" class="text-decoration-none">
                    <img src="<?= BASE_URL ?>/images/logo.png" alt="Logo" style="height: 60px; width: auto;">
                </a>
                <h2 class="fw-bold mt-2 mb-1">Create your account</h2>
                <p class="text-muted small">Start using tools for free — no credit card needed</p>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger py-2">
                    <?php foreach ($errors as $err): ?>
                        <div class="small"><i class="bi bi-exclamation-circle me-1"></i><?= e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" id="registerForm">
                <?= csrfField() ?>
                <input type="hidden" name="return" value="<?= e($_GET['return'] ?? '') ?>">

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control tb-form-control" 
                           value="<?= e($name) ?>" placeholder="John Doe" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control tb-form-control" 
                           value="<?= e($email) ?>" placeholder="you@example.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold">Password</label>
                    <input type="password" id="password" name="password" class="form-control tb-form-control" 
                           placeholder="Min 6 characters" required minlength="6">
                    <div class="tb-progress mt-2" style="height:4px">
                        <div class="tb-progress-bar" id="strengthBar" style="width:0%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label small fw-semibold">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control tb-form-control" 
                           placeholder="Repeat password" required minlength="6">
                </div>

                <button type="submit" class="btn tb-btn-primary w-100 btn-lg mb-3">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </button>

                <p class="text-center text-muted small mb-0">
                    Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    let strength = 0;
    if (val.length >= 6) strength += 25;
    if (val.length >= 10) strength += 25;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) strength += 25;
    if (/[0-9!@#$%^&*]/.test(val)) strength += 25;
    const bar = document.getElementById('strengthBar');
    bar.style.width = strength + '%';
    bar.style.background = strength <= 25 ? '#ef4444' : strength <= 50 ? '#f59e0b' : strength <= 75 ? '#06b6d4' : '#10b981';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
