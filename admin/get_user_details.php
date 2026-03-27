<?php
require_once '../config.php';
requireAdmin();

header('Content-Type: application/json');

$userId = intval($_GET['id'] ?? 0);

if (!$userId) {
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

$db = db();

try {
    // Get user details
    $stmt = $db->prepare("
        SELECT u.id, u.email, u.full_name, u.role, u.is_active, u.email_verified, 
               u.created_at, u.last_login, u.updated_at,
               (SELECT plan_type FROM subscriptions WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as plan,
               (SELECT status FROM subscriptions WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as sub_status,
               (SELECT created_at FROM subscriptions WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as sub_date
        FROM users u
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    // Get read count
    $readStmt = $db->prepare("SELECT COUNT(*) FROM reading_history WHERE user_id = ?");
    $readStmt->execute([$userId]);
    $user['reads'] = $readStmt->fetchColumn();
    
    // Format dates
    $user['created_at'] = formatDate($user['created_at']);
    $user['last_login'] = $user['last_login'] ? formatDate($user['last_login']) : null;
    $user['updated_at'] = formatDate($user['updated_at']);
    $user['sub_date'] = $user['sub_date'] ? formatDate($user['sub_date']) : null;
    
    // Format plan
    $user['plan'] = ucfirst($user['plan'] ?? 'free');
    
    echo json_encode($user);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
?>