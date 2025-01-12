<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/UserModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

try {
    $db = new Database('bank');
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT account_type, balance 
        FROM accounts 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $balances = [];
    foreach ($accounts as $account) {
        $balances[$account['account_type']] = $account['balance'];
    }
    
    echo json_encode($balances);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching balances']);
}
