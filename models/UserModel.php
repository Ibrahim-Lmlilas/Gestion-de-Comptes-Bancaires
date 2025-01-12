<?php

require_once(__DIR__ . '/../config/config.php'); 
require_once(__DIR__ . '/Account.php');

class User
{
    private $username;
    private $email;
    private $password;
    private $conn;

    public function __construct($pdo)
    {
        if ($pdo instanceof PDO)
            $this->conn = $pdo;
        else
            throw new Exception("Invalid database connection");
    }

    // Getters and setters...
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function setUsername($username) { $this->username = $username; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { 
        $this->password = password_hash($password, PASSWORD_DEFAULT); 
    }

    public function create() {
        try {
            $this->conn->beginTransaction();

            // Create user
            $stmt = $this->conn->prepare("
                INSERT INTO users (username, email, password, role) 
                VALUES (?, ?, ?, 'user')
            ");
            
            $stmt->execute([
                $this->username,
                $this->email,
                $this->password
            ]);
            
            $userId = $this->conn->lastInsertId();

            // Create accounts for the user
            $account = new Account($this->conn);
            $account->createUserAccounts($userId);

            $this->conn->commit();
            return $userId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function authenticate($email, $password) {
        $stmt = $this->conn->prepare("
            SELECT id, username, email, password, role 
            FROM users 
            WHERE email = ?
        ");
        
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    public function getAllUsers() {
        $query = "SELECT u.*, 
                  a.id as account_id, 
                  a.balance, 
                  a.account_type,
                  a.account_number,
                  CASE 
                    WHEN a.balance > 0 THEN 'Active'
                    ELSE 'Inactive'
                  END as status
                  FROM users u
                  LEFT JOIN accounts a ON u.id = a.user_id
                  ORDER BY u.created_at DESC";

        return $this->conn->query($query);
    }

    public function updateUser($userId) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$this->username, $this->email, $userId]);

            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function deleteUser($userId) {
        try {
            $this->conn->beginTransaction();

            // First delete related records in accounts table
            $stmt = $this->conn->prepare("DELETE FROM accounts WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Then delete the user
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$userId]);

            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
