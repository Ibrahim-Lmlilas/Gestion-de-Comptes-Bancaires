<?php
class User {
    private $db;
    private $id;
    private $name;
    private $email;
    private $role;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function authenticate($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $this->id = $user['id'];
            $this->name = $user['name'];
            $this->email = $user['email'];
            $this->role = $user['role'];
            return true;
        }
        return false;
    }

    public function register($name, $email, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            return $stmt->execute([$name, $email, $hashedPassword]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update($name, $email) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $this->id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAccounts() {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE user_id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
}