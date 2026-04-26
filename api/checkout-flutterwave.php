<?php
/**
 * ToolBox — API: Flutterwave Checkout
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
$priceMonthly = getSetting('price_monthly', '1.00');
$priceYearly  = getSetting('price_yearly', '9.00');
$amount = ($plan === 'yearly') ? $priceYearly : $priceMonthly;
$tx_ref = 'toolbox_' . time() . '_' . $auth->userId();

$flw_secret = getSetting('flw_secret_key', getenv('FLW_SECRET_KEY'));

if (!$flw_secret) {
    die('Flutterwave is not configured.');
}

$currency = getSetting('currency', 'USD');

// Prepare Flutterwave payload
$payload = [
    'tx_ref' => $tx_ref,
    'amount' => $amount,
    'currency' => $currency,
    'redirect_url' => BASE_URL . '/api/payment-success.php?gateway=flutterwave&plan=' . $plan,
    'payment_options' => 'card,mobilemoney,ussd',
    'customer' => [
        'email' => $auth->user()['email'],
        'name' => $auth->user()['name'],
    ],
    'customizations' => [
        'title' => 'ToolBox Pro ' . ucfirst($plan),
        'description' => 'Unlimited access to all PDF and Image tools',
        'logo' => BASE_URL . '/images/logo.png',
    ],
];

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.flutterwave.com/v3/payments",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $flw_secret,
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    die("cURL Error #:" . $err);
} else {
    $res = json_decode($response);
    if ($res && isset($res->status) && $res->status === 'success') {
        header('Location: ' . $res->data->link);
        exit;
    } else {
        die("Flutterwave API Error: " . ($res->message ?? 'Unknown error') . " <br>Response: " . htmlspecialchars($response));
    }
}
