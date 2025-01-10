<?php

class Transaction
{
    private $conn;
    private $id;
    private $account_number;
    private $beneficiary_account_number;
    private $amount;
    private $type;
    private $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getAccountNumber()
    {
        return $this->account_number;
    }
    public function getBeneficiaryAccountNumber()
    {
        return $this->beneficiary_account_number;
    }
    public function getAmount()
    {
        return $this->amount;
    }
    public function getType()
    {
        return $this->type;
    }
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setAccountNumber($account_number)
    {
        $this->account_number = $account_number;
    }
    public function setBeneficiaryAccountNumber($beneficiary_account_number)
    {
        $this->beneficiary_account_number = $beneficiary_account_number;
    }
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
    public function setType($type)
    {
        $this->type = $type;
    }
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function getAllTransactions()
    {
        $stmt = $this->conn->prepare("
            SELECT t.*, 
                   a1.account_number as source_account,
                   a2.account_number as beneficiary_account,
                   u.username as username
            FROM transactions t
            JOIN accounts a1 ON t.account_id = a1.id
            LEFT JOIN accounts a2 ON t.beneficiary_account_id = a2.id
            LEFT JOIN users u ON a1.user_id = u.id
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTransaction($account_number)
    {
        $stmt = $this->conn->prepare("
            SELECT t.*, 
                   a1.account_number as source_account,
                   a2.account_number as beneficiary_account
            FROM transactions t
            JOIN accounts a1 ON t.account_id = a1.id
            LEFT JOIN accounts a2 ON t.beneficiary_account_id = a2.id
            WHERE a1.account_number = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$account_number]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAccountIdByNumber($account_number)
    {
        $stmt = $this->conn->prepare("SELECT id, balance FROM accounts WHERE account_number = ?");
        $stmt->execute([$account_number]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create()
    {
        try {
            $this->conn->beginTransaction();

            // Get source account details
            $sourceAccount = $this->getAccountIdByNumber($this->account_number);
            if (!$sourceAccount) {
                throw new Exception("Source account not found");
            }

            // For transfers, validate beneficiary account
            $beneficiaryAccount = null;
            if ($this->type == 'transfer') {
                $beneficiaryAccount = $this->getAccountIdByNumber($this->beneficiary_account_number);
                if (!$beneficiaryAccount) {
                    throw new Exception("Beneficiary account not found");
                }
            }

            // Check if source account has sufficient balance
            if ($this->type == 'withdrawal' || $this->type == 'transfer') {
                if ($sourceAccount['balance'] < $this->amount) {
                    throw new Exception("Insufficient funds");
                }
            }

            // Insert transaction record
            $stmt = $this->conn->prepare("
                INSERT INTO transactions (account_id, beneficiary_account_id, amount, type) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $sourceAccount['id'],
                $beneficiaryAccount ? $beneficiaryAccount['id'] : null,
                $this->amount,
                $this->type
            ]);

            // Update account balances
            switch ($this->type) {
                case 'deposit':
                    $stmt = $this->conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$this->amount, $sourceAccount['id']]);
                    break;

                case 'withdrawal':
                    $stmt = $this->conn->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
                    $stmt->execute([$this->amount, $sourceAccount['id']]);
                    break;

                case 'transfer':
                    // Deduct from source account
                    $stmt = $this->conn->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
                    $stmt->execute([$this->amount, $sourceAccount['id']]);

                    // Add to beneficiary account
                    $stmt = $this->conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$this->amount, $beneficiaryAccount['id']]);
                    break;
            }

            $this->conn->commit();
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function update()
    {
        $stmt = $this->conn->prepare("UPDATE transactions SET account_id = ?, beneficiary_account_id = ?, amount = ?, type = ? WHERE id = ?");
        $stmt->execute([$this->account_number, $this->beneficiary_account_number, $this->amount, $this->type, $this->id]);
        return $stmt->rowCount();
    }

    public function delete()
    {
        $stmt = $this->conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$this->id]);
        return $stmt->rowCount();
    }
}
