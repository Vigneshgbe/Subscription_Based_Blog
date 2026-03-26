<?php
require_once 'config.php';
require_once 'stripe-php/init.php';

// Set Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Get the webhook payload
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    // Verify webhook signature
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sig_header,
        STRIPE_WEBHOOK_SECRET
    );
    
    $db = db();
    
    // Handle the event
    switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            
            // Get user ID from metadata
            $userId = $session->metadata->user_id ?? null;
            $planType = $session->metadata->plan_type ?? 'monthly';
            
            if ($userId && $session->payment_status === 'paid') {
                // Get subscription details
                $subscriptionId = $session->subscription;
                $subscription = \Stripe\Subscription::retrieve($subscriptionId);
                
                // Update subscription in database
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
                    (user_id, stripe_payment_intent_id, amount, currency, status, payment_method, description, metadata)
                    VALUES (?, ?, ?, 'INR', 'succeeded', 'card', ?, ?)
                ");
                
                $amount = $session->amount_total / 100;
                $description = ucfirst($planType) . ' subscription';
                
                $stmt->execute([
                    $userId,
                    $session->payment_intent,
                    $amount,
                    $description,
                    json_encode(['session_id' => $session->id])
                ]);
                
                logActivity($userId, 'subscription_created', 'subscription', null, [
                    'plan_type' => $planType,
                    'amount' => $amount
                ]);
            }
            break;
            
        case 'invoice.payment_succeeded':
            $invoice = $event->data->object;
            
            // Get customer and subscription
            $customerId = $invoice->customer;
            $subscriptionId = $invoice->subscription;
            
            if ($subscriptionId) {
                // Get subscription from Stripe
                $subscription = \Stripe\Subscription::retrieve($subscriptionId);
                
                // Find user in database
                $stmt = $db->prepare("SELECT user_id FROM subscriptions WHERE stripe_subscription_id = ?");
                $stmt->execute([$subscriptionId]);
                $result = $stmt->fetch();
                
                if ($result) {
                    $userId = $result['user_id'];
                    
                    // Update subscription period
                    $stmt = $db->prepare("
                        UPDATE subscriptions 
                        SET status = 'active',
                            current_period_start = FROM_UNIXTIME(?),
                            current_period_end = FROM_UNIXTIME(?)
                        WHERE stripe_subscription_id = ?
                    ");
                    
                    $stmt->execute([
                        $subscription->current_period_start,
                        $subscription->current_period_end,
                        $subscriptionId
                    ]);
                    
                    // Record transaction
                    $stmt = $db->prepare("
                        INSERT INTO transactions 
                        (user_id, stripe_charge_id, amount, currency, status, payment_method, description)
                        VALUES (?, ?, ?, 'INR', 'succeeded', 'card', 'Subscription renewal')
                    ");
                    
                    $stmt->execute([
                        $userId,
                        $invoice->charge,
                        $invoice->amount_paid / 100
                    ]);
                    
                    logActivity($userId, 'subscription_renewed', 'subscription', null);
                }
            }
            break;
            
        case 'invoice.payment_failed':
            $invoice = $event->data->object;
            $subscriptionId = $invoice->subscription;
            
            if ($subscriptionId) {
                // Update subscription status
                $stmt = $db->prepare("
                    UPDATE subscriptions 
                    SET status = 'past_due'
                    WHERE stripe_subscription_id = ?
                ");
                $stmt->execute([$subscriptionId]);
                
                // Get user for logging
                $stmt = $db->prepare("SELECT user_id FROM subscriptions WHERE stripe_subscription_id = ?");
                $stmt->execute([$subscriptionId]);
                $result = $stmt->fetch();
                
                if ($result) {
                    logActivity($result['user_id'], 'payment_failed', 'subscription', null, [
                        'invoice_id' => $invoice->id,
                        'amount' => $invoice->amount_due / 100
                    ]);
                }
            }
            break;
            
        case 'customer.subscription.updated':
            $subscription = $event->data->object;
            
            // Update subscription in database
            $status = $subscription->status;
            $cancelAtPeriodEnd = $subscription->cancel_at_period_end;
            
            $stmt = $db->prepare("
                UPDATE subscriptions 
                SET status = ?,
                    cancel_at_period_end = ?,
                    current_period_start = FROM_UNIXTIME(?),
                    current_period_end = FROM_UNIXTIME(?)
                WHERE stripe_subscription_id = ?
            ");
            
            $stmt->execute([
                $status,
                $cancelAtPeriodEnd,
                $subscription->current_period_start,
                $subscription->current_period_end,
                $subscription->id
            ]);
            break;
            
        case 'customer.subscription.deleted':
            $subscription = $event->data->object;
            
            // Mark subscription as canceled
            $stmt = $db->prepare("
                UPDATE subscriptions 
                SET status = 'canceled'
                WHERE stripe_subscription_id = ?
            ");
            $stmt->execute([$subscription->id]);
            
            // Get user for logging
            $stmt = $db->prepare("SELECT user_id FROM subscriptions WHERE stripe_subscription_id = ?");
            $stmt->execute([$subscription->id]);
            $result = $stmt->fetch();
            
            if ($result) {
                logActivity($result['user_id'], 'subscription_canceled', 'subscription', null);
            }
            break;
            
        default:
            // Unhandled event type
            error_log('Unhandled webhook event: ' . $event->type);
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    error_log('Webhook error: Invalid payload');
    exit;
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    error_log('Webhook error: Invalid signature');
    exit;
} catch (Exception $e) {
    // Other error
    http_response_code(500);
    error_log('Webhook error: ' . $e->getMessage());
    exit;
}
?>