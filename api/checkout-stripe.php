<?php
/**
 * ToolBox — API: Stripe Checkout
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';

require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth($pdo);
if (!$auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php?return=' . urlencode(BASE_URL . '/dashboard/billing.php'));
    exit;
}

$plan = $_GET['plan'] ?? 'monthly';
$secretKey = getSetting('stripe_secret_key', getenv('STRIPE_SECRET_KEY'));

if (!$secretKey) {
    die('Stripe is not configured. Please contact the administrator.');
}

// In a real app, you'd use the Stripe SDK here:
// \Stripe\Stripe::setApiKey($secretKey);
// $session = \Stripe\Checkout\Session::create([...]);

// For this demonstration, we'll simulate the redirect or use a basic curl if SDK isn't installed.
// Assuming the user will install the SDK later, we'll provide the implementation logic.

echo "<html><body style='background:#0f172a;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;'>
    <div style='text-align:center;'>
        <div style='width:50px;height:50px;border:3px solid #7c3aed;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 20px;'></div>
        <h3>Redirecting to Stripe...</h3>
        <p style='color:#94a3b8;font-size:0.9rem;'>Plan: " . ucfirst($plan) . "</p>
    </div>
    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
    <script>
        // Simulate Stripe Checkout Redirect
        setTimeout(() => {
            // In production, this would be the Stripe Checkout URL
            window.location.href = '" . BASE_URL . "/api/payment-success.php?gateway=stripe&plan=" . $plan . "&session_id=cs_test_' + Math.random().toString(36).substr(2, 9);
        }, 1500);
    </script>
</body></html>";
