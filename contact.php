<?php
require_once 'config.php';

$flash = getFlashMessage();
$hasSubscription = isLoggedIn() ? hasActiveSubscription($_SESSION['user_id']) : false;
$freeRemaining = getFreeArticlesRemaining();

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        setFlashMessage('Please fill in all fields', 'danger');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('Please enter a valid email address', 'danger');
    } else {
        try {
            // Insert contact message into database
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message, status, created_at) 
                VALUES (:name, :email, :subject, :message, 'unread', NOW())
            ");
            
            $result = $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message
            ]);
            
            if ($result) {
                setFlashMessage('Thank you for your message! We\'ll get back to you within 24 hours.', 'success');
                
                // Optional: Send email notification to admin
                // You can uncomment and configure this section if you want email notifications
                /*
                $to = "admin@" . strtolower(str_replace(' ', '', SITE_NAME)) . ".com";
                $email_subject = "New Contact Form Submission: " . $subject;
                $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
                $headers = "From: noreply@" . strtolower(str_replace(' ', '', SITE_NAME)) . ".com";
                mail($to, $email_subject, $email_body, $headers);
                */
                
                // Redirect to prevent form resubmission
                header('Location: contact.php');
                exit;
            } else {
                setFlashMessage('Sorry, there was an error sending your message. Please try again.', 'danger');
            }
        } catch (PDOException $e) {
            error_log("Contact form error: " . $e->getMessage());
            setFlashMessage('Sorry, there was an error sending your message. Please try again.', 'danger');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Get in touch with our team. We'd love to hear from you.">
    <link rel="icon" type="image/x-icon" href="https://raw.githubusercontent.com/Vigneshgbe/Subscription_Based_Blog/refs/heads/main/assets/Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #0a0a0a;
            --secondary: #ffffff;
            --accent: #FF6B6B;
            --accent-dark: #E74C3C;
            --text: #1a1a1a;
            --text-light: #6b7280;
            --text-lighter: #9ca3af;
            --border: #e5e7eb;
            --border-light: #f3f4f6;
            --bg-light: #fafafa;
            --bg-lighter: #f9fafb;
            --premium: #FFD700;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: var(--secondary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Header Styles */
        .header {
            background: var(--secondary);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }
        
        .header-top {
            background: var(--primary);
            color: var(--secondary);
            padding: 10px 0;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        
        .header-top-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        
        .premium-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: var(--premium);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
        }
        
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            gap: 32px;
        }
        
        .logo {
            font-size: 26px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: var(--primary);
            text-decoration: none;
            font-family: 'Playfair Display', serif;
            transition: opacity 0.2s;
        }
        
        .logo:hover {
            opacity: 0.8;
        }
        
        .nav {
            display: flex;
            gap: 32px;
            align-items: center;
        }
        
        .nav a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
            position: relative;
        }
        
        .nav a:hover {
            color: var(--accent);
        }
        
        .nav a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: width 0.3s;
        }
        
        .nav a:hover::after {
            width: 100%;
        }
        
        /* Button Styles */
        .btn {
            padding: 10px 24px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
        }
        
        .btn-primary {
            background: var(--accent);
            color: var(--secondary);
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
        }
        
        .btn-primary:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
        }

        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            font-size: 16px;
            justify-content: center;
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text);
            padding: 8px;
        }
        
        .mobile-nav {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: var(--secondary);
            z-index: 2000;
            padding: 24px;
            overflow-y: auto;
        }
        
        .mobile-nav.active {
            display: block;
        }
        
        .mobile-nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        
        .mobile-close {
            background: none;
            border: none;
            font-size: 32px;
            cursor: pointer;
            color: var(--text);
        }
        
        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .mobile-nav-links a {
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: 18px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #2a2a2a 100%);
            color: var(--secondary);
            padding: 80px 0;
            margin-bottom: 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 107, 107, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 215, 0, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            text-align: center;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 56px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
            letter-spacing: -1px;
            font-family: 'Playfair Display', serif;
        }
        
        .hero p {
            font-size: 20px;
            opacity: 0.9;
            line-height: 1.6;
            font-weight: 300;
        }

        /* Contact Section */
        .contact-section {
            padding: 0 0 80px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            align-items: start;
        }

        /* Contact Info */
        .contact-info h2 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 24px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }

        .contact-info p {
            font-size: 17px;
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .info-cards {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .info-card {
            background: var(--bg-lighter);
            padding: 28px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        .info-card:hover {
            border-color: var(--accent);
            box-shadow: var(--shadow-lg);
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 12px;
        }

        .info-icon {
            font-size: 28px;
            width: 56px;
            height: 56px;
            background: var(--accent);
            color: var(--secondary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-card h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
        }

        .info-card p {
            color: var(--text);
            margin: 0;
            font-size: 16px;
            line-height: 1.6;
        }

        .info-card a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .info-card a:hover {
            color: var(--accent-dark);
        }

        /* Contact Form */
        .contact-form {
            background: var(--secondary);
            padding: 48px;
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-lg);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text);
            background: var(--secondary);
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }

        .form-group textarea {
            min-height: 160px;
            resize: vertical;
        }

        .form-group select {
            cursor: pointer;
        }

        /* Alerts */
        .alert {
            padding: 16px 24px;
            margin-bottom: 32px;
            border-left: 4px solid;
            font-weight: 500;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        
        .alert-danger {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }

        /* FAQ Section */
        .faq-section {
            background: var(--bg-lighter);
            padding: 80px 0;
            margin-top: 80px;
        }

        .faq-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 64px;
        }

        .faq-header h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
        }

        .faq-header p {
            font-size: 18px;
            color: var(--text-light);
        }

        .faq-list {
            max-width: 900px;
            margin: 0 auto;
        }

        .faq-item {
            background: var(--secondary);
            padding: 32px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
        }

        .faq-item:hover {
            box-shadow: var(--shadow-lg);
        }

        .faq-question {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--primary);
            font-family: 'Playfair Display', serif;
        }

        .faq-answer {
            color: var(--text-light);
            line-height: 1.8;
            font-size: 16px;
        }
        
        /* Footer */
        .footer {
            background: var(--primary);
            color: var(--secondary);
            padding: 64px 0 32px;
            margin-top: 0;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 48px;
            margin-bottom: 48px;
        }
        
        .footer-section h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }
        
        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
        }
        
        .footer a:hover {
            color: var(--accent);
        }
        
        .footer-bottom {
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 48px;
            }
        }

        @media (max-width: 768px) {
            .nav {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero {
                padding: 60px 0;
            }
            
            .hero h1 {
                font-size: 40px;
            }
            
            .hero p {
                font-size: 16px;
            }

            .contact-form {
                padding: 32px 24px;
            }

            .contact-info h2 {
                font-size: 28px;
            }

            .faq-header h2 {
                font-size: 32px;
            }

            .logo {
                font-size: 22px;
            }
            
            .container {
                padding: 0 16px;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 32px;
            }

            .contact-info h2 {
                font-size: 24px;
            }

            .faq-question {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            <button class="mobile-close" onclick="toggleMobileMenu()">×</button>
        </div>
        <nav class="mobile-nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="admin/dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a href="my-account.php">My Account</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Subscribe Now</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <?php if (isLoggedIn()): ?>
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        <?php if ($hasSubscription): ?>
                            <span class="premium-badge">✓ Premium Member</span>
                        <?php else: ?>
                            • <?php echo $freeRemaining; ?> free articles remaining
                        <?php endif; ?>
                    <?php else: ?>
                        Get unlimited access • Subscribe today
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
                <nav class="nav">
                    <a href="index.php">Home</a>
                    <a href="about.php">About</a>
                    <a href="contact.php">Contact Us</a>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php">Dashboard</a>
                        <?php endif; ?>
                        <a href="my-account.php">My Account</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php" class="btn btn-primary">Subscribe</a>
                    <?php endif; ?>
                </nav>
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <div class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Get In Touch</h1>
                <p>We'd love to hear from you. Drop us a line and we'll get back to you soon.</p>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <div class="contact-grid">
                <!-- Contact Info -->
                <div class="contact-info">
                    <h2>Let's Start a Conversation</h2>
                    <p>Whether you have a question about our content, subscriptions, partnerships, or just want to say hello—we're here to help.</p>

                    <div class="info-cards">
                        <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-icon">✉️</div>
                                <h3>Email Us</h3>
                            </div>
                            <p><a href="mailto:hello@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com">hello@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</a></p>
                            <p style="margin-top: 8px; font-size: 14px; color: var(--text-lighter);">We typically respond within 24 hours</p>
                        </div>

                        <!-- <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-icon">💬</div>
                                <h3>Support</h3>
                            </div>
                            <p><a href="mailto:support@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com">support@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</a></p>
                            <p style="margin-top: 8px; font-size: 14px; color: var(--text-lighter);">For subscription & account help</p>
                        </div> -->

                        <!-- <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-icon">🤝</div>
                                <h3>Partnerships</h3>
                            </div>
                            <p><a href="mailto:partnerships@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com">partnerships@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com</a></p>
                            <p style="margin-top: 8px; font-size: 14px; color: var(--text-lighter);">Collaborations & business inquiries</p>
                        </div> -->

                        <div class="info-card">
                            <div class="info-card-header">
                                <div class="info-icon">📍</div>
                                <h3>Office</h3>
                            </div>
                            <p>123 Innovation Street<br>Tech District, San Francisco<br>CA 94105, United States</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Your Name *</label>
                            <input type="text" id="name" name="name" required placeholder="John Doe">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required placeholder="john@example.com">
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a topic...</option>
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Subscription Help">Subscription Help</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Content Feedback">Content Feedback</option>
                                <option value="Partnership">Partnership Opportunity</option>
                                <option value="Press">Press Inquiry</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" required placeholder="Tell us what's on your mind..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-submit">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="faq-header">
                <h2>Frequently Asked Questions</h2>
                <p>Quick answers to common questions</p>
            </div>

            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">How quickly will I receive a response?</div>
                    <div class="faq-answer">We aim to respond to all inquiries within 24 hours during business days. For urgent subscription or technical issues, we often respond much faster.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Can I schedule a call with your team?</div>
                    <div class="faq-answer">For partnership inquiries or enterprise subscriptions, absolutely! Send us a message through the form above and we'll arrange a convenient time to chat.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">Do you accept guest contributions?</div>
                    <div class="faq-answer">We occasionally publish guest articles from industry experts. If you have a compelling pitch, please email it to hello@<?php echo strtolower(str_replace(' ', '', SITE_NAME)); ?>.com with "Guest Post" in the subject line.</div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">How can I report a technical issue?</div>
                    <div class="faq-answer">Please use the contact form above and select "Technical Support" as your subject. Include details about your device, browser, and what you were trying to do when the issue occurred.</div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p style="color: rgba(255, 255, 255, 0.7); margin-top: 12px; line-height: 1.6;">
                        Premium insights and analysis for those who want to stay ahead.
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Navigation</h3>
                    <div class="footer-links">
                        <a href="index.php">Home</a>
                        <a href="pricing.php">Pricing</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Company</h3>
                    <div class="footer-links">
                        <a href="about.php">About</a>
                        <a href="contact.php">Contact</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <div class="footer-links">
                        <a href="#">Terms of Service</a>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Cookie Policy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const mobileNav = document.getElementById('mobileNav');
            mobileNav.classList.toggle('active');
            document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : '';
        }
    </script>
</body>
</html>