<?php

require_once __DIR__ . '/../config/config.php';

class Account {
    private $conn;
    private $id;
    private $user_id;
    private $account_number;
    private $account_type;
    private $balance;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getAccountNumber() { return $this->account_number; }
    public function getAccountType() { return $this->account_type; }
    public function getBalance() { return $this->balance; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setAccountNumber($account_number) { $this->account_number = $account_number; }
    public function setAccountType($account_type) { $this->account_type = $account_type; }
    public function setBalance($balance) { $this->balance = $balance; }

    private function generateAccountNumber() {
        do {
            $account_number = 'ACC' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM accounts WHERE account_number = ?");
            $stmt->execute([$account_number]);
        } while ($stmt->fetchColumn() > 0);
        
        return $account_number;
    }

    public function createUserAccounts($user_id) {
        try {
            $this->conn->beginTransaction();

            // Create Current Account
            $currentAccountNumber = $this->generateAccountNumber();
            $stmt = $this->conn->prepare("
                INSERT INTO accounts (user_id, account_type, account_number, balance) 
                VALUES (?, 'current', ?, 0.00)
            ");
            $stmt->execute([$user_id, $currentAccountNumber]);

            // Create Savings Account
            $savingsAccountNumber = $this->generateAccountNumber();
            $stmt = $this->conn->prepare("
                INSERT INTO accounts (user_id, account_type, account_number, balance) 
                VALUES (?, 'savings', ?, 0.00)
            ");
            $stmt->execute([$user_id, $savingsAccountNumber]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getAccountsByUserId($user_id) {
        $stmt = $this->conn->prepare("
            SELECT id, user_id, account_number, account_type, balance 
            FROM accounts 
            WHERE user_id = ?
            ORDER BY account_type
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccountByNumber($account_number) {
        $stmt = $this->conn->prepare("
            SELECT id, user_id, account_number, account_type, balance 
            FROM accounts 
            WHERE account_number = ?
        ");
        $stmt->execute([$account_number]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $stmt = $this->conn->prepare("
            UPDATE accounts 
            SET account_type = ?, balance = ? 
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $this->account_type,
            $this->balance,
            $this->id
        ]);
    }

    public function updateBalance($amount) {
        $this->balance += $amount;
        return $this->update();
    }
}
