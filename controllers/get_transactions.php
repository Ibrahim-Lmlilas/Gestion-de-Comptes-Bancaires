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
    
    // Get all transactions for the user
    $sql = "
        SELECT 
            t.*,
            a1.account_number as source_account,
            a2.account_number as beneficiary_account,
            DATE_FORMAT(t.created_at, '%Y-%m-%d %H:%i:%s') as created_at,
            CASE
                WHEN t.account_id IN (SELECT id FROM accounts WHERE user_id = ?) THEN 'out'
                ELSE 'in'
            END as direction
        FROM transactions t
        LEFT JOIN accounts a1 ON t.account_id = a1.id
        LEFT JOIN accounts a2 ON t.beneficiary_account_id = a2.id
        WHERE t.account_id IN (SELECT id FROM accounts WHERE user_id = ?)
           OR t.beneficiary_account_id IN (SELECT id FROM accounts WHERE user_id = ?)
        ORDER BY t.created_at DESC
        LIMIT 10
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($transactions);
} catch (Exception $e) {
    error_log("Transaction Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error fetching transactions',
        'details' => $e->getMessage(),
        'user_id' => $_SESSION['user_id'] ?? 'not set'
    ]);
}
