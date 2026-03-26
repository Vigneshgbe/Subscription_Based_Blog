<?php
require_once 'config.php';
require_once 'stripe-php/init.php';

requireLogin();

$sessionId = $_GET['session_id'] ?? '';

if (empty($sessionId)) {
    flashMessage('danger', 'Invalid session');
    redirect('pricing.php');
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    // Retrieve the session
    $session = \Stripe\Checkout\Session::retrieve($sessionId);
    
    if ($session->payment_status === 'paid') {
        $db = db();
        $userId = $_SESSION['user_id'];
        
        // Get subscription details
        $subscriptionId = $session->subscription;
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        
        $planType = $session->metadata->plan_type ?? 'monthly';
        
        // Update or create subscription record
        $stmt = $db->prepare("
            INSERT INTO subscriptions 
            (user_id, stripe_customer_id, stripe_subscription_id, plan_type, status, current_period_start, current_period_end)
            VALUES (?, ?, ?, ?, 'active', FROM_UNIXTIME(?), FROM_UNIXTIME(?))
            ON DUPLICATE KEY UPDATE
            stripe_subscription_id = VALUES(stripe_subscription_id),
            plan_type = VALUES(plan_type),
            status = 'active',
            current_period_start = VALUES(current_period_start),
            current_period_end = VALUES(current_period_end)
        ");
        
        $stmt->execute([
            $userId,
            $session->customer,
            $subscriptionId,
            $planType,
            $subscription->current_period_start,
            $subscription->current_period_end
        ]);
        
        // Record transaction
        $stmt = $db->prepare("
            INSERT INTO transactions 
            (user_id, stripe_payment_intent_id, amount, currency, status, payment_method, description)
            VALUES (?, ?, ?, 'INR', 'succeeded', 'card', ?)
        ");
        
        $amount = $session->amount_total / 100; // Convert from paisa to rupees
        $description = ucfirst($planType) . ' subscription';
        
        $stmt->execute([
            $userId,
            $session->payment_intent,
            $amount,
            $description
        ]);
        
        // Log activity
        logActivity($userId, 'subscription_activated', 'subscription', $db->lastInsertId(), [
            'plan_type' => $planType,
            'amount' => $amount
        ]);
        
        // Send confirmation email
        $emailBody = "
            <h2>Subscription Activated!</h2>
            <p>Thank you for subscribing to " . SITE_NAME . ".</p>
            <p><strong>Plan:</strong> " . ucfirst($planType) . "</p>
            <p><strong>Amount:</strong> ₹" . number_format($amount, 2) . "</p>
            <p>You now have unlimited access to all premium content.</p>
            <p><a href='" . SITE_URL . "'>Start Reading</a></p>
        ";
        
        sendEmail($_SESSION['user_email'], 'Subscription Activated - ' . SITE_NAME, $emailBody);
        
        $success = true;
    } else {
        $success = false;
    }
    
} catch (Exception $e) {
    $success = false;
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment <?php echo $success ? 'Success' : 'Failed'; ?> - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .result-container {
            background: white;
            max-width: 500px;
            width: 100%;
            padding: 50px 40px;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .success-icon {
            color: #28a745;
        }
        
        .error-icon {
            color: #dc3545;
        }
        
        h1 {
            font-size: 32px;
            margin-bottom: 15px;
            color: #333;
        }
        
        p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .details {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        
        .details p {
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .details strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <?php if ($success): ?>
            <div class="icon success-icon">✓</div>
            <h1>Payment Successful!</h1>
            <p>Your subscription has been activated. Welcome to the premium experience!</p>
            
            <div class="details">
                <p><strong>Plan:</strong> <?php echo ucfirst($planType); ?> Subscription</p>
                <p><strong>Status:</strong> Active</p>
                <p><strong>Access:</strong> Unlimited Premium Articles</p>
            </div>
            
            <a href="index.php" class="btn">Start Reading</a>
        <?php else: ?>
            <div class="icon error-icon">✗</div>
            <h1>Payment Failed</h1>
            <p>We couldn't process your payment. Please try again or contact support.</p>
            
            <?php if (isset($errorMessage)): ?>
                <div class="details">
                    <p><strong>Error:</strong> <?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
            <?php endif; ?>
            
            <a href="pricing.php" class="btn">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>