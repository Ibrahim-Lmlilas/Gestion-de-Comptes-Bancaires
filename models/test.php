<?php
class Transaction {
    private $db;


    // Constructor: kay7t connection dial database
    public function __construct($db) {
        
        $this->db = $db;
    }

    // Jib ga3 les transactions
    public function getAllTransactions() {
        $query = "SELECT t.*, 
                  a1.user_id as sender_id,
                  a2.user_id as receiver_id,
                  u1.name as sender_name,
                  u2.name as receiver_name
                  FROM transactions t
                  LEFT JOIN accounts a1 ON t.account_id = a1.id
                  LEFT JOIN accounts a2 ON t.beneficiary_account_id = a2.id
                  LEFT JOIN users u1 ON a1.user_id = u1.id
                  LEFT JOIN users u2 ON a2.user_id = u2.id
                  ORDER BY t.created_at DESC";
        
        return $this->db->query($query);
    }

    // Dir transaction jdida
    public function createTransaction($accountId, $type, $amount, $beneficiaryAccountId = null) {
        // Bda transaction
        $this->db->begin_transaction();

        try {
            // Check balance
            $balanceQuery = "SELECT balance FROM accounts WHERE id = ?";
            $stmt = $this->db->prepare($balanceQuery);
            $stmt->bind_param("i", $accountId);
            $stmt->execute();
            $balance = $stmt->get_result()->fetch_assoc()['balance'];

            if ($type !== 'deposit' && $balance < $amount) {
                throw new Exception("Makaynch flous bzzaf");
            }

            // Insert transaction
            $query = "INSERT INTO transactions (account_id, transaction_type, amount, beneficiary_account_id) 
                     VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isdi", $accountId, $type, $amount, $beneficiaryAccountId);
            $stmt->execute();

            // Update balances
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

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Update balance
    private function updateBalance($accountId, $amount) {
        $query = "UPDATE accounts SET balance = balance + ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("di", $amount, $accountId);
        $stmt->execute();
    }

    // Jib transactions dial user
    public function getUserTransactions($userId) {
        $query = "SELECT t.*, 
                  a1.user_id as sender_id,
                  a2.user_id as receiver_id,
                  u1.name as sender_name,
                  u2.name as receiver_name
                  FROM transactions t
                  LEFT JOIN accounts a1 ON t.account_id = a1.id
                  LEFT JOIN accounts a2 ON t.beneficiary_account_id = a2.id
                  LEFT JOIN users u1 ON a1.user_id = u1.id
                  LEFT JOIN users u2 ON a2.user_id = u2.id
                  WHERE a1.user_id = ? OR a2.user_id = ?
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        return $stmt->get_result();
    }
}