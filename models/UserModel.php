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

    public function register($username, $email, $password)
    {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection not set");
            }

            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch())
                throw new Exception("This email already exsists in ou records! Try another one.");
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch())
                throw new Exception("This username is already Taken! Try another one.");
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            //INSERRT Record in db
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $password])) {
                $this->username = $username;
                $this->email = $email;
                $this->password = $hashedPassword;
                $this->username = $username;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Registration Error: " . $e->getMessage());
        }
    }

    public function login($email, $password)
    {
        try {
            if (!$this->conn) {
                throw new Exception("Database connection not set");
            }
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            if ($user && password_verify($password, $user->password)) {
                $this->username = $user->username;
                $this->email = $user->email;
                $this->password = $user->password;
                return $user;
            }
        } catch (PDOException $e) {
            throw new Exception("Error during Login: " . $e->getMessage());
        }
    }

    public function getAllUsers() {
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

}
