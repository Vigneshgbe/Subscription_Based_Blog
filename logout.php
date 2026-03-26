<?php
require_once 'config.php';

if (isLoggedIn()) {
    // Log activity
    logActivity($_SESSION['user_id'], 'user_logout', 'user', $_SESSION['user_id']);
    
    // Destroy session
    session_destroy();
    
    // Unset all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
}

flashMessage('success', 'You have been logged out successfully');
redirect('login.php');
?>