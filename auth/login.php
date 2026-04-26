<?php
/**
 * ToolBox — Login Page
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth($pdo);

if ($auth->isLoggedIn()) {
    redirect(BASE_URL . '/dashboard/');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $auth->login($email, $password);
        if ($user) {
            setFlash('success', 'Welcome back, ' . e($user['name']) . '!');
            $return = $_POST['return'] ?? '';
            redirect($return ?: BASE_URL . '/dashboard/');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Sign In';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="tb-auth-page">
    <div class="container">
        <div class="tb-auth-card">
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>/" class="text-decoration-none">
                    <img src="<?= BASE_URL ?>/images/logo.png" alt="Logo" style="height: 60px; width: auto;">
                </a>
                <h2 class="fw-bold mt-2 mb-1">Welcome back</h2>
                <p class="text-muted small">Sign in to access your tools and dashboard</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small">
                    <i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="return" value="<?= e($_GET['return'] ?? '') ?>">

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control tb-form-control" 
                           value="<?= e($email) ?>" placeholder="you@example.com" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold">Password</label>
                    <input type="password" id="password" name="password" class="form-control tb-form-control" 
                           placeholder="Your password" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small text-muted" for="remember">Remember me</label>
                    </div>
                    <a href="<?= BASE_URL ?>/auth/forgot-password.php" class="small">Forgot password?</a>
                </div>

                <button type="submit" class="btn tb-btn-primary w-100 btn-lg mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>

                <p class="text-center text-muted small mb-0">
                    Don't have an account? <a href="<?= BASE_URL ?>/auth/register.php">Create one free</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
