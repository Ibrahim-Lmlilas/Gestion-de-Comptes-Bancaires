<?php
require_once (__DIR__ . '/../config/config.php');
require_once (__DIR__ . '/../models/UserModel.php');

function getUsers() {
    try {
        $db = new Database('bank');
        $conn = $db->getConnection();
        
        $sql = "SELECT u.*, 
                       GROUP_CONCAT(a.account_type) as account_types,
                       GROUP_CONCAT(a.account_number) as account_numbers,
                       GROUP_CONCAT(a.balance) as balances,
                       GROUP_CONCAT(a.status) as account_statuses
                FROM users u
                LEFT JOIN accounts a ON u.id = a.user_id
                GROUP BY u.id";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the user data
        foreach ($users as &$user) {
            $types = explode(',', $user['account_types'] ?? '');
            $numbers = explode(',', $user['account_numbers'] ?? '');
            $balances = explode(',', $user['balances'] ?? '');
            $statuses = explode(',', $user['account_statuses'] ?? '');
            
            $user['accounts'] = [];
            for ($i = 0; $i < count($types); $i++) {
                if (!empty($types[$i])) {
                    $user['accounts'][] = [
                        'type' => $types[$i],
                        'account_number' => $numbers[$i] ?? '',
                        'balance' => $balances[$i] ?? 0,
                        'status' => $statuses[$i] ?? 'Active'
                    ];
                }
            }
            
            // Remove the raw data
            unset($user['account_types'], $user['account_numbers'], $user['balances'], $user['account_statuses']);
            
            // Set overall status based on account statuses
            $user['status'] = !empty($statuses) && in_array('Active', $statuses) ? 'Active' : 'Inactive';
        }
        
        return $users;
    } catch (Exception $e) {
        error_log($e->getMessage());
        throw new Exception("Error fetching users: " . $e->getMessage());
    }
}

function updateUser($userId, $data) {
    try {
        $db = new Database('bank');
        $conn = $db->getConnection();
        $conn->beginTransaction();

        // Update user details
        $stmt = $conn->prepare("
            UPDATE users 
            SET username = ?, 
                email = ?,
                role = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['username'],
            $data['email'],
            $data['role'],
            $userId
        ]);

        // Update account statuses
        if (isset($data['account_status']) && is_array($data['account_status'])) {
            $stmt = $conn->prepare("
                UPDATE accounts 
                SET status = ? 
                WHERE user_id = ? AND account_type = ?
            ");
            
            foreach ($data['account_status'] as $accountType => $status) {
                // Convert status to proper case
                $status = ucfirst(strtolower($status));
                if (!in_array($status, ['Active', 'Inactive'])) {
                    $status = 'Inactive';
                }
                $stmt->execute([$status, $userId, $accountType]);
            }
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log($e->getMessage());
        throw new Exception("Error updating user: " . $e->getMessage());
    }
}

function addUser($username, $email, $password, $currentBalance, $savingsBalance, $role) {
    try {
        $db = new Database('bank');
        $conn = $db->getConnection();
        $conn->beginTransaction();

        // Insert user
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$username, $email, $hashedPassword, $role]);
        $userId = $conn->lastInsertId();

        // Create current account
        $currentAccNumber = generateAccountNumber();
        $stmt = $conn->prepare("
            INSERT INTO accounts (user_id, account_type, account_number, balance, status) 
            VALUES (?, 'current', ?, ?, 'Active')
        ");
        $stmt->execute([$userId, $currentAccNumber, $currentBalance]);

        // Create savings account
        $savingsAccNumber = generateAccountNumber();
        $stmt = $conn->prepare("
            INSERT INTO accounts (user_id, account_type, account_number, balance, status) 
            VALUES (?, 'savings', ?, ?, 'Active')
        ");
        $stmt->execute([$userId, $savingsAccNumber, $savingsBalance]);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log($e->getMessage());
        throw new Exception("Error adding user: " . $e->getMessage());
    }
}

function generateAccountNumber() {
    return sprintf('%010d', mt_rand(1000000000, 9999999999));
}

function deleteUser($userId) {
    try {
        $db = new Database('bank');
        $conn = $db->getConnection();
        $conn->beginTransaction();

        // First, delete all transactions related to user's accounts
        $stmt = $conn->prepare("
            DELETE t FROM transactions t
            INNER JOIN accounts a ON (t.account_id = a.id OR t.beneficiary_account_id = a.id)
            WHERE a.user_id = ?
        ");
        $stmt->execute([$userId]);

        // Then delete the accounts
        $stmt = $conn->prepare("DELETE FROM accounts WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Finally delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log($e->getMessage());
        throw new Exception("Error deleting user: " . $e->getMessage());
    }
}

function updateProfile($userId, $username, $email, $currentPassword = null, $newPassword = null) {
    try {
        $db = new Database('bank');
        $conn = $db->getConnection();
        
        // First verify the current password if provided
        if ($currentPassword && $newPassword) {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
            
            // Update with new password
            $stmt = $conn->prepare("
                UPDATE users 
                SET username = ?, 
                    email = ?,
                    password = ?
                WHERE id = ?
            ");
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt->execute([$username, $email, $hashedPassword, $userId]);
        } else {
            // Update without password change
            $stmt = $conn->prepare("
                UPDATE users 
                SET username = ?, 
                    email = ?
                WHERE id = ?
            ");
            $stmt->execute([$username, $email, $userId]);
        }
        
        // Update session variables
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        return true;
    } catch (Exception $e) {
        error_log($e->getMessage());
        throw new Exception($e->getMessage());
    }
}

?>
