<?php
require_once (__DIR__ . '/../config/config.php');
require_once (__DIR__ . '/../models/Transaction.php');

function getTotalAmountTransactions() {
    try {
        $db = new Database('bank');
        $pdo = $db->getConnection();
        $transactionController = new Transaction($pdo);
        return $transactionController->getTotalAmountTransactions();
    } catch (Exception $e) {
        // Log error and return 0 or handle as needed
        error_log($e->getMessage());
        return 0;
    }
}

function getAllTransactions() {
    try {
        $db = new Database('bank');
        $pdo = $db->getConnection();
        $transaction = new Transaction($pdo);
        return $transaction->getAllTransactions();
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

function getTransaction($account_id) {
    try {
        $db = new Database('bank');
        $pdo = $db->getConnection();
        $transaction = new Transaction($pdo);
        return $transaction->getTransaction($account_id);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

function createTransaction($accountId, $type, $amount, $beneficiaryAccountId = null) {
    try {
        $db = new Database('bank');
        $pdo = $db->getConnection();
        $transaction = new Transaction($pdo);
        return $transaction->create($accountId, $type, $amount, $beneficiaryAccountId);
    } catch (Exception $e) {
        error_log($e->getMessage());
        throw $e;
    }
}

// var_dump($result);

?>