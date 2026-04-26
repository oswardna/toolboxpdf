<?php
/**
 * ToolBox — API: Payment Success
 * Handles the return redirect from payment gateways after checkout.
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Subscription.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = new Auth($pdo);
if (!$auth->isLoggedIn()) {
    setFlash('danger', 'You must be logged in to complete a payment.');
    redirect(BASE_URL . '/auth/login.php');
}

$gateway   = $_GET['gateway'] ?? '';
$plan      = in_array($_GET['plan'] ?? '', ['monthly', 'yearly']) ? $_GET['plan'] : 'monthly';
$paymentId = 'unknown_' . time();

if ($gateway === 'flutterwave') {
    $status        = $_GET['status'] ?? '';
    $transactionId = $_GET['transaction_id'] ?? '';

    if ($status !== 'successful' || empty($transactionId)) {
        setFlash('danger', 'Payment failed or was cancelled. Please try again.');
        redirect(BASE_URL . '/dashboard/billing.php');
    }

    $flw_secret = getSetting('flw_secret_key', getenv('FLW_SECRET_KEY'));

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => "https://api.flutterwave.com/v3/transactions/{$transactionId}/verify",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $flw_secret,
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($curl);
    $curlErr  = curl_error($curl);
    curl_close($curl);

    if ($curlErr) {
        setFlash('danger', 'Could not verify payment. Please contact support.');
        redirect(BASE_URL . '/dashboard/billing.php');
    }

    $res = json_decode($response);
    if (!$res || $res->status !== 'success' || $res->data->status !== 'successful') {
        setFlash('danger', 'Payment verification failed. Please contact support if charged.');
        redirect(BASE_URL . '/dashboard/billing.php');
    }

    $paymentId = (string) $transactionId;

} elseif ($gateway === 'stripe') {
    // Stripe redirects with ?session_id= — verified server-side by webhook in production
    $sessionId = $_GET['session_id'] ?? '';
    if (empty($sessionId)) {
        setFlash('danger', 'Invalid Stripe session.');
        redirect(BASE_URL . '/dashboard/billing.php');
    }
    $paymentId = $sessionId;

} else {
    setFlash('danger', 'Unknown payment gateway.');
    redirect(BASE_URL . '/dashboard/billing.php');
}

$sub = new Subscription($pdo);
$sub->create($auth->userId(), $plan, $paymentId, $gateway);

setFlash('success', 'Thank you! Your ' . ucfirst($plan) . ' Pro subscription is now active. 🎉');
redirect(BASE_URL . '/dashboard/');

