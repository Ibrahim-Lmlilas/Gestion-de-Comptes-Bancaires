<?php

// echo __DIR__
require_once(__DIR__ . '/../config/config.php'); 

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

    // Getters
    public function getUsername()
    {
        return $this->username;
    }
    public function getEmail()
    {
        return $this->email;
    }

    // Setters

    public function setUsername($username)
    {
        return $this->username = $username;
    }
    public function setEmail($email)
    {
        return $this->email = $email;
    }
    public function setPassword($password)
    {
        return $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function getAllUsers()
    {
        $query = "SELECT u.*, 
                  a.id as account_id, 
                  a.balance, 
                  a.account_type,
                  CASE 
                    WHEN a.balance > 0 THEN 'Active'
                    ELSE 'Inactive'
                  END as status
                  FROM users u
                  LEFT JOIN accounts a ON u.id = a.user_id
                  ORDER BY u.created_at DESC";

        return $this->conn->query($query);
    }

    public function updateUser($userId)
    {
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

    public function deleteUser($userId)
    {
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

    public function authenticate($email, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function createUser($username, $email, $password, $role = 'user')
    {
        try {
            $this->conn->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $role]);
            
            $userId = $this->conn->lastInsertId();

            // Create a default account for the user
            $stmt = $this->conn->prepare("INSERT INTO accounts (user_id, account_type, balance) VALUES (?, 'current', 0.00)");
            $stmt->execute([$userId]);

            $this->conn->commit();
            return $userId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function updateProfile($userId, $data)
    {
        try {
            $this->conn->beginTransaction();

            $updates = [];
            $params = [];

            if (!empty($data['username'])) {
                $updates[] = "username = ?";
                $params[] = $data['username'];
            }

            if (!empty($data['email'])) {
                $updates[] = "email = ?";
                $params[] = $data['email'];
            }

            if (!empty($data['new_password'])) {
                $updates[] = "password = ?";
                $params[] = password_hash($data['new_password'], PASSWORD_DEFAULT);
            }

            if (!empty($updates)) {
                $params[] = $userId;
                $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute($params);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
