<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Transaction.php';

function getTotalAmountTransactions() {
    $db = new Database('bank');
    $pdo = $db->getConnection();
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions");
    $stmt->execute();
    return $stmt->fetchColumn() ?? 0;
}

function getTransactionsByAccountNumber($account_number) {
    $db = new Database('bank');
    $pdo = $db->getConnection();
    $transaction = new Transaction($pdo);
    return $transaction->getTransaction($account_number);
}

function createTransaction($account_number, $type, $amount, $beneficiary_account_number = null) {
    try {
        validateTransaction($account_number, $type, $amount, $beneficiary_account_number);
        
        $db = new Database('bank');
        $pdo = $db->getConnection();
        $transaction = new Transaction($pdo);
        
        $transaction->setAccountNumber($account_number);
        $transaction->setType($type);
        $transaction->setAmount($amount);
        if ($beneficiary_account_number) {
            $transaction->setBeneficiaryAccountNumber($beneficiary_account_number);
        }
        
        return $transaction->create();
    } catch (Exception $e) {
        // You might want to handle different types of exceptions differently
        throw $e;
    }
}

function getAllTransactions() {
    $db = new Database('bank');
    $pdo = $db->getConnection();
    $transaction = new Transaction($pdo);
    return $transaction->getAllTransactions();
}

function validateTransaction($account_number, $type, $amount, $beneficiary_account_number = null) {
    if (!is_numeric($amount) || $amount <= 0) {
        throw new Exception("Invalid amount");
    }
    
    if (!in_array($type, ['deposit', 'withdrawal', 'transfer'])) {
        throw new Exception("Invalid transaction type");
    }
    
    if ($type === 'transfer' && empty($beneficiary_account_number)) {
        throw new Exception("Beneficiary account number is required for transfers");
    }
    
    if ($beneficiary_account_number === $account_number) {
        throw new Exception("Cannot transfer to the same account");
    }
}
?>