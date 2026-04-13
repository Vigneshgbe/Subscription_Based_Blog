<?php
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to subscribe']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request body']);
    exit;
}

if (empty($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token. Please refresh the page and try again.']);
    exit;
}

$planType = $input['plan_type'] ?? '';
$userId   = $_SESSION['user_id'];

if (!in_array($planType, ['monthly', 'yearly'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid plan type selected']);
    exit;
}

if (hasActiveSubscription($userId)) {
    http_response_code(400);
    echo json_encode(['error' => 'You already have an active subscription']);
    exit;
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $db = db();

    $stmt = $db->prepare("SELECT id, email, full_name FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('User account not found');
    }

    // Always create fresh customer to avoid stale ID errors
    $customer = \Stripe\Customer::create([
        'email'    => $user['email'],
        'name'     => $user['full_name'],
        'metadata' => ['user_id' => (string)$userId]
    ]);

    // CHF pricing — your Stripe account is CHF-based
    // CHF 2.50/month ≈ ₹299 | CHF 25.00/year ≈ ₹2,999
    if ($planType === 'monthly') {
        $unitAmount  = 24900;  
        $interval    = 'month';
        $productName = SITE_NAME . ' — Monthly Subscription';
        $productDesc = 'Unlimited premium articles — billed monthly (≈ ₹299/month)';
    } else {
        $unitAmount  = 249900;  
        $interval    = 'year';
        $productName = SITE_NAME . ' — Yearly Subscription';
        $productDesc = 'Unlimited premium articles — billed yearly (≈ ₹2,999/year)';
    }

    $session = \Stripe\Checkout\Session::create([
        'customer'             => $customer->id,
        'payment_method_types' => ['card'],
        'line_items'           => [[
            'price_data' => [
                'currency'     => 'inr',
                'product_data' => [
                    'name'        => $productName,
                    'description' => $productDesc,
                ],
                'unit_amount' => $unitAmount,
                'recurring'   => ['interval' => $interval],
            ],
            'quantity' => 1,
        ]],
        'mode'        => 'subscription',
        'success_url' => rtrim(SITE_URL, '/') . '/payment-success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => rtrim(SITE_URL, '/') . '/pricing.php?canceled=1',
        'metadata'    => [
            'user_id'   => (string)$userId,
            'plan_type' => $planType,
        ],
        'subscription_data' => [
            'metadata' => [
                'user_id'   => (string)$userId,
                'plan_type' => $planType,
            ]
        ],
    ]);

    logActivity($userId, 'checkout_initiated', 'subscription', null, [
        'plan_type'  => $planType,
        'session_id' => $session->id,
    ]);

    echo json_encode(['sessionId' => $session->id]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    error_log('Stripe API error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('process-payment error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>