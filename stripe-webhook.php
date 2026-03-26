<?php
require_once 'config.php';
require_once 'stripe-php/init.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$payload    = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, STRIPE_WEBHOOK_SECRET);
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    error_log('Webhook: Invalid payload — ' . $e->getMessage());
    exit;
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    error_log('Webhook: Invalid signature — ' . $e->getMessage());
    exit;
}

$db = db();

try {
    switch ($event->type) {

        // ── Checkout completed ─────────────────────────────────────
        case 'checkout.session.completed':
            $session  = $event->data->object;
            $userId   = $session->metadata->user_id   ?? null;
            $planType = $session->metadata->plan_type ?? 'monthly';

            if (!$userId || $session->payment_status !== 'paid') break;

            $subscriptionId = $session->subscription;
            $subscription   = \Stripe\Subscription::retrieve($subscriptionId);

            // Idempotent upsert — payment-success.php may have already written this
            $stmt = $db->prepare("
                INSERT INTO subscriptions
                    (user_id, stripe_customer_id, stripe_subscription_id, plan_type, status,
                     current_period_start, current_period_end)
                VALUES (?, ?, ?, ?, 'active', FROM_UNIXTIME(?), FROM_UNIXTIME(?))
                ON DUPLICATE KEY UPDATE
                    stripe_subscription_id = VALUES(stripe_subscription_id),
                    plan_type              = VALUES(plan_type),
                    status                 = 'active',
                    current_period_start   = VALUES(current_period_start),
                    current_period_end     = VALUES(current_period_end)
            ");
            $stmt->execute([
                $userId,
                $session->customer,
                $subscriptionId,
                $planType,
                $subscription->current_period_start,
                $subscription->current_period_end,
            ]);

            // Only insert transaction if not already recorded
            $check = $db->prepare("SELECT id FROM transactions WHERE stripe_payment_intent_id = ? LIMIT 1");
            $check->execute([$session->payment_intent]);
            if (!$check->fetch()) {
                $amount = ($session->amount_total ?? 0) / 100;
                $stmt   = $db->prepare("
                    INSERT INTO transactions
                        (user_id, stripe_payment_intent_id, amount, currency, status, payment_method, description, metadata)
                    VALUES (?, ?, ?, 'INR', 'succeeded', 'card', ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $session->payment_intent,
                    $amount,
                    ucfirst($planType) . ' subscription',
                    json_encode(['session_id' => $session->id]),
                ]);
            }

            logActivity($userId, 'subscription_created', 'subscription', null, [
                'plan_type' => $planType,
            ]);
            break;

        // ── Renewal payment succeeded ──────────────────────────────
        case 'invoice.payment_succeeded':
            $invoice        = $event->data->object;
            $subscriptionId = $invoice->subscription;
            if (!$subscriptionId) break;

            $subscription = \Stripe\Subscription::retrieve($subscriptionId);

            $stmt = $db->prepare("SELECT user_id FROM subscriptions WHERE stripe_subscription_id = ? LIMIT 1");
            $stmt->execute([$subscriptionId]);
            $row = $stmt->fetch();
            if (!$row) break;

            $userId = $row['user_id'];

            $stmt = $db->prepare("
                UPDATE subscriptions
                SET status               = 'active',
                    current_period_start = FROM_UNIXTIME(?),
                    current_period_end   = FROM_UNIXTIME(?)
                WHERE stripe_subscription_id = ?
            ");
            $stmt->execute([
                $subscription->current_period_start,
                $subscription->current_period_end,
                $subscriptionId,
            ]);

            // Log renewal transaction (skip if this invoice was the initial charge — already handled above)
            if ($invoice->billing_reason !== 'subscription_create') {
                $check = $db->prepare("SELECT id FROM transactions WHERE stripe_charge_id = ? LIMIT 1");
                $check->execute([$invoice->charge]);
                if (!$check->fetch()) {
                    $stmt = $db->prepare("
                        INSERT INTO transactions
                            (user_id, stripe_charge_id, amount, currency, status, payment_method, description)
                        VALUES (?, ?, ?, 'INR', 'succeeded', 'card', 'Subscription renewal')
                    ");
                    $stmt->execute([$userId, $invoice->charge, $invoice->amount_paid / 100]);
                }
            }

            logActivity($userId, 'subscription_renewed', 'subscription', null);
            break;

        // ── Payment failed ─────────────────────────────────────────
        case 'invoice.payment_failed':
            $invoice        = $event->data->object;
            $subscriptionId = $invoice->subscription;
            if (!$subscriptionId) break;

            $stmt = $db->prepare("UPDATE subscriptions SET status = 'past_due' WHERE stripe_subscription_id = ?");
            $stmt->execute([$subscriptionId]);

            $stmt = $db->prepare("SELECT user_id FROM subscriptions WHERE stripe_subscription_id = ? LIMIT 1");
            $stmt->execute([$subscriptionId]);
            $row = $stmt->fetch();
            if ($row) {
                logActivity($row['user_id'], 'payment_failed', 'subscription', null, [
                    'invoice_id' => $invoice->id,
                    'amount'     => $invoice->amount_due / 100,
                ]);
            }
            break;

        // ── Subscription updated (cancel at period end, plan change) ─
        case 'customer.subscription.updated':
            $subscription = $event->data->object;
            $stmt         = $db->prepare("
                UPDATE subscriptions
                SET status               = ?,
                    cancel_at_period_end = ?,
                    current_period_start = FROM_UNIXTIME(?),
                    current_period_end   = FROM_UNIXTIME(?)
                WHERE stripe_subscription_id = ?
            ");
            $stmt->execute([
                $subscription->status,
                (int)$subscription->cancel_at_period_end,
                $subscription->current_period_start,
                $subscription->current_period_end,
                $subscription->id,
            ]);
            break;

        // ── Subscription deleted / expired ─────────────────────────
        case 'customer.subscription.deleted':
            $subscription = $event->data->object;
            $stmt         = $db->prepare("UPDATE subscriptions SET status = 'canceled' WHERE stripe_subscription_id = ?");
            $stmt->execute([$subscription->id]);

            $stmt = $db->prepare("SELECT user_id FROM subscriptions WHERE stripe_subscription_id = ? LIMIT 1");
            $stmt->execute([$subscription->id]);
            $row = $stmt->fetch();
            if ($row) {
                logActivity($row['user_id'], 'subscription_canceled', 'subscription', null);
            }
            break;

        default:
            error_log('Unhandled Stripe webhook event: ' . $event->type);
            break;
    }

    http_response_code(200);
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Webhook handler error: ' . $e->getMessage());
    exit;
}
?>