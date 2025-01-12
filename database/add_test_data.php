<?php
require_once __DIR__ . '/../config/config.php';

try {
    $db = new Database('bank');
    $conn = $db->getConnection();
    
    // Get John's current account
    $stmt = $conn->prepare("
        SELECT a.id 
        FROM accounts a 
        INNER JOIN users u ON a.user_id = u.id 
        WHERE u.email = 'john@example.com' AND a.account_type = 'current'
    ");
    $stmt->execute();
    $johnAccountId = $stmt->fetchColumn();
    
    // Get Jane's current account
    $stmt = $conn->prepare("
        SELECT a.id 
        FROM accounts a 
        INNER JOIN users u ON a.user_id = u.id 
        WHERE u.email = 'jane@example.com' AND a.account_type = 'current'
    ");
    $stmt->execute();
    $janeAccountId = $stmt->fetchColumn();
    
    if ($johnAccountId && $janeAccountId) {
        // Add test transactions
        $transactions = [
            [
                'account_id' => $johnAccountId,
                'type' => 'deposit',
                'amount' => 1000.00,
                'beneficiary_account_id' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'account_id' => $johnAccountId,
                'type' => 'withdrawal',
                'amount' => 500.00,
                'beneficiary_account_id' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'account_id' => $johnAccountId,
                'type' => 'transfer',
                'amount' => 200.00,
                'beneficiary_account_id' => $janeAccountId,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ],
            [
                'account_id' => $janeAccountId,
                'type' => 'deposit',
                'amount' => 1500.00,
                'beneficiary_account_id' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'account_id' => $janeAccountId,
                'type' => 'transfer',
                'amount' => 300.00,
                'beneficiary_account_id' => $johnAccountId,
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours'))
            ]
        ];
        
        $stmt = $conn->prepare("
            INSERT INTO transactions 
                (account_id, type, amount, beneficiary_account_id, created_at)
            VALUES 
                (?, ?, ?, ?, ?)
        ");
        
        foreach ($transactions as $trans) {
            $stmt->execute([
                $trans['account_id'],
                $trans['type'],
                $trans['amount'],
                $trans['beneficiary_account_id'],
                $trans['created_at']
            ]);
        }
        
        echo "Test transactions added successfully!\n";
    } else {
        echo "Could not find required accounts.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
