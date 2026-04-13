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

    // Get user
    $stmt = $db->prepare("SELECT id, email, full_name FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('User account not found');
    }

    // Always create a fresh Stripe customer — avoids stale customer ID errors
    $customer = \Stripe\Customer::create([
        'email'    => $user['email'],
        'name'     => $user['full_name'],
        'metadata' => ['user_id' => (string)$userId]
    ]);
    $customerId = $customer->id;

    // Build line_items using Price IDs from Stripe dashboard
    $priceId = $planType === 'monthly' ? STRIPE_MONTHLY_PRICE_ID : STRIPE_YEARLY_PRICE_ID;

    // Fallback: if Price IDs not set, use inline price_data
    if (empty($priceId) || strpos($priceId, 'price_xxx') !== false) {
        // Use inline pricing (CHF to match your Stripe account currency)
        $amount   = $planType === 'monthly' ? 299 * 100 : 2999 * 100; // in smallest currency unit
        $interval = $planType === 'monthly' ? 'month' : 'year';
        $lineItems = [[
            'price_data' => [
                'currency'     => 'chf',  // matches your Stripe account currency- chf
                'product_data' => [
                    'name'        => SITE_NAME . ' — ' . ucfirst($planType) . ' Subscription',
                    'description' => 'Unlimited access to all premium articles',
                ],
                'unit_amount' => $amount,
                'recurring'   => ['interval' => $interval],
            ],
            'quantity' => 1,
        ]];
    } else {
        // Use Price IDs from dashboard (preferred)
        $lineItems = [[
            'price'    => $priceId,
            'quantity' => 1,
        ]];
    }

    $successUrl = rtrim(SITE_URL, '/') . '/payment-success.php?session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl  = rtrim(SITE_URL, '/') . '/pricing.php?canceled=1';

    $session = \Stripe\Checkout\Session::create([
        'customer'             => $customerId,
        'payment_method_types' => ['card'],
        'line_items'           => $lineItems,
        'mode'                 => 'subscription',
        'success_url'          => $successUrl,
        'cancel_url'           => $cancelUrl,
        'metadata'             => [
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