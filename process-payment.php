<?php
require_once 'config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Must be first output — no HTML before this
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

// CSRF validation
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

// Block if already subscribed to same plan
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

    // Get or create Stripe customer
    $stmt = $db->prepare("
        SELECT stripe_customer_id FROM subscriptions
        WHERE user_id = ? AND stripe_customer_id IS NOT NULL
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$userId]);
    $existingSub = $stmt->fetch();
    $customerId  = $existingSub['stripe_customer_id'] ?? null;

    if (!$customerId) {
        // Create new Stripe customer
        $customer   = \Stripe\Customer::create([
            'email'    => $user['email'],
            'name'     => $user['full_name'],
            'metadata' => ['user_id' => (string)$userId]
        ]);
        $customerId = $customer->id;
    }

    // Plan amounts and intervals
    $amount   = $planType === 'monthly' ? MONTHLY_PRICE  : YEARLY_PRICE;
    $interval = $planType === 'monthly' ? 'month' : 'year';

    $planLabel = $planType === 'monthly'
        ? 'Monthly Subscription (₹299/month)'
        : 'Yearly Subscription (₹2,999/year — Save 30%)';

    // Create Stripe Checkout Session
    $session = \Stripe\Checkout\Session::create([
        'customer'             => $customerId,
        'payment_method_types' => ['card'],
        'line_items'           => [[
            'price_data' => [
                'currency'     => 'inr',
                'product_data' => [
                    'name'        => SITE_NAME . ' — ' . $planLabel,
                    'description' => 'Unlimited access to all premium articles',
                ],
                'unit_amount' => $amount,
                'recurring'   => ['interval' => $interval],
            ],
            'quantity' => 1,
        ]],
        'mode'        => 'subscription',
        'success_url' => SITE_URL . '/payment-success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => SITE_URL . '/pricing.php?canceled=1',
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
        // Pre-fill customer email on Stripe checkout page
        'customer_email' => $customerId ? null : $user['email'],
    ]);

    logActivity($userId, 'checkout_initiated', 'subscription', null, [
        'plan_type'  => $planType,
        'session_id' => $session->id,
    ]);

    echo json_encode(['sessionId' => $session->id]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    error_log('Stripe API error: ' . $e->getMessage());
    echo json_encode(['error' => 'Payment service error. Please try again or contact support.']);

} catch (Exception $e) {
    http_response_code(500);
    error_log('process-payment error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>