<?php
/**
 * ToolBox — Forgot Password
 */
$pageTitle = 'Reset Password';
require_once __DIR__ . '/../includes/header.php';

$userModel = new User($pdo);
$sent = false;
$error = '';
$token = $_GET['token'] ?? '';

// ─── Reset form (with token) ─────────────────
if ($token) {
    $reset = $userModel->verifyResetToken($token);
    if (!$reset) {
        $error = 'Invalid or expired reset link.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
        if (!validateCsrf()) {
            $error = 'Invalid request.';
        } else {
            $pass    = $_POST['password'] ?? '';
            $confirm = $_POST['password_confirm'] ?? '';

            if (strlen($pass) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($pass !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $userModel->resetPassword($token, $pass);
                setFlash('success', 'Password reset successfully. You can now sign in.');
                redirect(BASE_URL . '/auth/login.php');
            }
        }
    }
    ?>
    <div class="tb-auth-page">
        <div class="container">
            <div class="tb-auth-card">
                <div class="text-center mb-4">
                    <span style="font-size:2.5rem">🔑</span>
                    <h2 class="fw-bold mt-2 mb-1">Set New Password</h2>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
                <?php endif; ?>

                <?php if ($reset): ?>
                <form method="post">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">New Password</label>
                        <input type="password" id="password" name="password" class="form-control tb-form-control" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label for="password_confirm" class="form-label small fw-semibold">Confirm Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control tb-form-control" required>
                    </div>
                    <button type="submit" class="btn tb-btn-primary w-100 btn-lg">Reset Password</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// ─── Request reset link ─────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf()) {
        $error = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        // Always show success (prevent email enumeration)
        $token = $userModel->createPasswordReset($email);
        if ($token) {
            // In production: send email with reset link
            // For now: show the token in flash message (dev mode)
            setFlash('info', 'Reset link: ' . BASE_URL . '/auth/forgot-password.php?token=' . $token);
        }
        $sent = true;
    }
}
?>

<div class="tb-auth-page">
    <div class="container">
        <div class="tb-auth-card">
            <div class="text-center mb-4">
                <span style="font-size:2.5rem">🔑</span>
                <h2 class="fw-bold mt-2 mb-1">Forgot Password?</h2>
                <p class="text-muted small">Enter your email and we'll send you a reset link</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
            <?php endif; ?>

            <?php if ($sent): ?>
                <div class="alert alert-success py-2 small">
                    <i class="bi bi-check-circle me-1"></i>If an account exists with that email, a reset link has been sent.
                </div>
            <?php endif; ?>

            <form method="post">
                <?= csrfField() ?>
                <div class="mb-4">
                    <label for="email" class="form-label small fw-semibold">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control tb-form-control" placeholder="you@example.com" required>
                </div>
                <button type="submit" class="btn tb-btn-primary w-100 btn-lg mb-3">
                    <i class="bi bi-envelope me-2"></i>Send Reset Link
                </button>
                <p class="text-center small mb-0">
                    <a href="<?= BASE_URL ?>/auth/login.php"><i class="bi bi-arrow-left me-1"></i>Back to login</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
