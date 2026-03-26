<?php
require_once 'config.php';
require_once 'stripe-php/init.php'; // Include Stripe PHP library

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Verify CSRF token
if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$planType = $input['plan_type'] ?? '';
$userId = $_SESSION['user_id'];

if (!in_array($planType, ['monthly', 'yearly'])) {
    echo json_encode(['error' => 'Invalid plan type']);
    exit;
}

// Set Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $db = db();
    
    // Get user details
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Check for existing subscription
    $stmt = $db->prepare("SELECT stripe_customer_id FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $existingSubscription = $stmt->fetch();
    
    $customerId = $existingSubscription['stripe_customer_id'] ?? null;
    
    // Create or retrieve Stripe customer
    if (!$customerId) {
        $customer = \Stripe\Customer::create([
            'email' => $user['email'],
            'name' => $user['full_name'],
            'metadata' => [
                'user_id' => $userId
            ]
        ]);
        $customerId = $customer->id;
    }
    
    // Set price based on plan
    $amount = $planType === 'monthly' ? MONTHLY_PRICE : YEARLY_PRICE;
    $interval = $planType === 'monthly' ? 'month' : 'year';
    
    // Create Stripe Checkout Session
    $session = \Stripe\Checkout\Session::create([
        'customer' => $customerId,
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'inr',
                'product_data' => [
                    'name' => SITE_NAME . ' ' . ucfirst($planType) . ' Subscription',
                    'description' => 'Unlimited access to premium articles'
                ],
                'unit_amount' => $amount,
                'recurring' => [
                    'interval' => $interval
                ]
            ],
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'success_url' => SITE_URL . '/payment-success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => SITE_URL . '/pricing.php?canceled=1',
        'metadata' => [
            'user_id' => $userId,
            'plan_type' => $planType
        ]
    ]);
    
    // Log activity
    logActivity($userId, 'checkout_initiated', 'subscription', null, [
        'plan_type' => $planType,
        'session_id' => $session->id
    ]);
    
    echo json_encode([
        'sessionId' => $session->id
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Payment processing failed: ' . $e->getMessage()]);
    logActivity($userId ?? null, 'payment_error', null, null, ['error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>