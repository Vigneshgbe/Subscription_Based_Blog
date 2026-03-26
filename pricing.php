<?php
require_once 'config.php';

requireLogin();

$db = db();
$userId = $_SESSION['user_id'];

// Check current subscription
$stmt = $db->prepare("
    SELECT * FROM subscriptions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->execute([$userId]);
$currentSubscription = $stmt->fetch();

$hasActiveSubscription = hasActiveSubscription($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing - <?php echo SITE_NAME; ?></title>
    <script src="https://js.stripe.com/v3/"></script>
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
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        
        .header h1 {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .header p {
            font-size: 20px;
            opacity: 0.9;
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .pricing-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        
        .pricing-card:hover {
            transform: translateY(-10px);
        }
        
        .pricing-card.featured {
            border: 4px solid #ffd700;
        }
        
        .badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffd700;
            color: #000;
            padding: 6px 20px;
            font-weight: 900;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 20px;
        }
        
        .plan-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }
        
        .plan-price {
            font-size: 48px;
            font-weight: 900;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .plan-price .currency {
            font-size: 24px;
        }
        
        .plan-duration {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .features {
            list-style: none;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .features li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        
        .features li:before {
            content: '✓ ';
            color: #28a745;
            font-weight: 900;
            margin-right: 10px;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .alert {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .alert-success {
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .features-section {
            background: white;
            border-radius: 12px;
            padding: 40px;
            margin-top: 50px;
        }
        
        .features-section h2 {
            font-size: 32px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .feature-item {
            text-align: center;
        }
        
        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .feature-item h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .feature-item p {
            color: #666;
            line-height: 1.6;
        }
        
        #payment-message {
            display: none;
            margin-top: 20px;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
        }
        
        #payment-message.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }
        
        #payment-message.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Back to Home</a>
        
        <div class="header">
            <h1>Choose Your Plan</h1>
            <p>Unlock unlimited access to premium content</p>
        </div>
        
        <?php if ($hasActiveSubscription): ?>
        <div class="alert alert-success">
            <strong>✓ Active Subscription</strong><br>
            You currently have an active subscription. Thank you for your support!
        </div>
        <?php endif; ?>
        
        <div class="pricing-grid">
            <!-- Free Plan -->
            <div class="pricing-card">
                <div class="plan-name">Free</div>
                <div class="plan-price"><span class="currency">₹</span>0</div>
                <div class="plan-duration">Forever</div>
                <ul class="features">
                    <li>3 premium articles per month</li>
                    <li>Access to free articles</li>
                    <li>Email newsletter</li>
                    <li>Community access</li>
                </ul>
                <button class="btn btn-outline" disabled>Current Plan</button>
            </div>
            
            <!-- Monthly Plan -->
            <div class="pricing-card featured">
                <span class="badge">Most Popular</span>
                <div class="plan-name">Monthly</div>
                <div class="plan-price"><span class="currency">₹</span>299</div>
                <div class="plan-duration">Per month</div>
                <ul class="features">
                    <li>Unlimited premium articles</li>
                    <li>Ad-free reading experience</li>
                    <li>Exclusive content</li>
                    <li>Early access to articles</li>
                    <li>Download articles as PDF</li>
                    <li>Priority support</li>
                </ul>
                <?php if ($hasActiveSubscription && $currentSubscription['plan_type'] === 'monthly'): ?>
                    <button class="btn btn-success" disabled>Active</button>
                <?php else: ?>
                    <button class="btn btn-primary" onclick="subscribe('monthly')">Subscribe Now</button>
                <?php endif; ?>
                <div id="payment-message"></div>
            </div>
            
            <!-- Yearly Plan -->
            <div class="pricing-card">
                <span class="badge" style="background: #28a745;">Save 16%</span>
                <div class="plan-name">Yearly</div>
                <div class="plan-price"><span class="currency">₹</span>2,999</div>
                <div class="plan-duration">Per year (₹250/month)</div>
                <ul class="features">
                    <li>Everything in Monthly</li>
                    <li>2 months free</li>
                    <li>Exclusive yearly member perks</li>
                    <li>Invitation to annual event</li>
                    <li>Premium member badge</li>
                    <li>Dedicated account manager</li>
                </ul>
                <?php if ($hasActiveSubscription && $currentSubscription['plan_type'] === 'yearly'): ?>
                    <button class="btn btn-success" disabled>Active</button>
                <?php else: ?>
                    <button class="btn btn-primary" onclick="subscribe('yearly')">Subscribe Now</button>
                <?php endif; ?>
                <div id="payment-message"></div>
            </div>
        </div>
        
        <div class="features-section">
            <h2>Why Subscribe?</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">📚</div>
                    <h3>Unlimited Access</h3>
                    <p>Read all premium articles without limits. New content added daily.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🚫</div>
                    <h3>Ad-Free Experience</h3>
                    <p>Enjoy uninterrupted reading without any advertisements.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">⭐</div>
                    <h3>Exclusive Content</h3>
                    <p>Access members-only articles, interviews, and insights.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔔</div>
                    <h3>Early Access</h3>
                    <p>Be the first to read new articles before public release.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        
        async function subscribe(planType) {
            const button = event.target;
            const messageDiv = button.nextElementSibling;
            
            button.disabled = true;
            button.textContent = 'Processing...';
            messageDiv.className = '';
            messageDiv.textContent = '';
            messageDiv.style.display = 'none';
            
            try {
                const response = await fetch('process-payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        plan_type: planType,
                        csrf_token: '<?php echo generateCSRFToken(); ?>'
                    })
                });
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.sessionId) {
                    // Redirect to Stripe Checkout
                    const result = await stripe.redirectToCheckout({
                        sessionId: data.sessionId
                    });
                    
                    if (result.error) {
                        throw new Error(result.error.message);
                    }
                } else {
                    throw new Error('Failed to create checkout session');
                }
            } catch (error) {
                console.error('Payment error:', error);
                messageDiv.className = 'error';
                messageDiv.textContent = error.message || 'Payment failed. Please try again.';
                messageDiv.style.display = 'block';
                button.disabled = false;
                button.textContent = 'Subscribe Now';
            }
        }
    </script>
</body>
</html>