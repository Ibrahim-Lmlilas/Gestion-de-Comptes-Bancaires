<?php

class Transaction {
    private $id;
    private $account_id;
    private $beneficiary_account_id;
    private $amount;
    private $type;
    private $created_at;
    private $conn = NULL;

    public function __construct($pdo) {
        if ($pdo instanceof PDO) {
            $this->conn = $pdo;
        } else {
            throw new Exception("Invalid database connection");
        }
    }

    //Getters
    public function getAccountId() { return $this->account_id; }
    public function getBeneficiaryAccountId() { return $this->beneficiary_account_id; }
    public function getAmount() { return $this->amount; }
    public function getType() { return $this->type; }
    public function getCreatedAt() { return $this->created_at; }

    //Setters
    public function setAccountId($account_id) { $this->account_id = $account_id; }
    public function setBeneficiaryAccountId($beneficiary_account_id) { $this->beneficiary_account_id = $beneficiary_account_id; }
    public function setAmount($amount) { $this->amount = $amount; }
    public function setType($type) { $this->type = $type; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }

    public function getAllTransactions() {
        $query = "SELECT t.*, 
                  a1.user_id as sender_id,
                  a2.user_id as receiver_id,
                  u1.username as sender_name,
                  u2.username as receiver_name
                  FROM transactions t
                  LEFT JOIN accounts a1 ON t.account_id = a1.id
                  LEFT JOIN accounts a2 ON t.beneficiary_account_id = a2.id
                  LEFT JOIN users u1 ON a1.user_id = u1.id
                  LEFT JOIN users u2 ON a2.user_id = u2.id
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTotalAmountTransactions() {
        $stmt = $this->conn->prepare("SELECT SUM(amount) FROM transactions");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getTransaction($account_id) {
        $stmt = $this->conn->prepare("SELECT * FROM transactions WHERE account_id = :account_id");
        $stmt->execute(['account_id' => $account_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function create($accountId, $type, $amount, $beneficiaryAccountId = null) {
        $this->conn->beginTransaction();
        
        try {
            // Check balance for withdrawals and transfers
            if ($type !== 'deposit') {
                $balanceStmt = $this->conn->prepare("SELECT balance FROM accounts WHERE id = ?");
                $balanceStmt->execute([$accountId]);
                $balance = $balanceStmt->fetchColumn();
                
                if ($balance < $amount) {
                    throw new Exception("Insufficient funds");
                }
            }

            // Create transaction record
            $stmt = $this->conn->prepare("INSERT INTO transactions (account_id, transaction_type, amount, beneficiary_account_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$accountId, $type, $amount, $beneficiaryAccountId]);

            // Update account balances
            switch($type) {
                case 'deposit':
                    $this->updateBalance($accountId, $amount);
                    break;
                case 'withdrawal':
                    $this->updateBalance($accountId, -$amount);
                    break;
                case 'transfer':
                    if($beneficiaryAccountId) {
                        $this->updateBalance($accountId, -$amount);
                        $this->updateBalance($beneficiaryAccountId, $amount);
                    }
                    break;
            }

            $this->conn->commit();
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    private function updateBalance($accountId, $amount) {
        $stmt = $this->conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $accountId]);
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare("UPDATE transactions SET account_id = ?, beneficiary_account_id = ?, amount = ?, type = ? WHERE id = ?");
        $stmt->execute([$data['account_id'], $data['beneficiary_account_id'], $data['amount'], $data['type'], $id]);
        return $stmt->rowCount();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }
}