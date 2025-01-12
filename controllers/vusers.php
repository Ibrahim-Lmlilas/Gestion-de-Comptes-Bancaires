<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../models/UserModel.php');
require_once(__DIR__ . '/../models/Account.php');

function getUsers() {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $query = "SELECT u.*, 
                  a.id as account_id,
                  a.account_number,
                  a.account_type,
                  a.balance,
                  CASE 
                    WHEN a.balance > 0 THEN 'Active'
                    ELSE 'Inactive'
                  END as status
                  FROM users u
                  LEFT JOIN accounts a ON u.id = a.user_id
                  ORDER BY u.username, a.account_type";
        
        $stmt = $conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

function addClient($data) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $conn->beginTransaction();

        // Create user
        $user = new User($conn);
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $userId = $user->create();

        // Create accounts
        $account = new Account($conn);
        
        // Create current account
        $currentAccountNumber = $account->generateAccountNumber();
        $stmt = $conn->prepare("
            INSERT INTO accounts (user_id, account_type, account_number, balance) 
            VALUES (?, 'current', ?, ?)
        ");
        $stmt->execute([$userId, $currentAccountNumber, $data['current_balance']]);

        // Create savings account
        $savingsAccountNumber = $account->generateAccountNumber();
        $stmt = $conn->prepare("
            INSERT INTO accounts (user_id, account_type, account_number, balance) 
            VALUES (?, 'savings', ?, ?)
        ");
        $stmt->execute([$userId, $savingsAccountNumber, $data['savings_balance']]);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log($e->getMessage());
        return false;
    }
}

function updateClient($data) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $conn->beginTransaction();

        // Update user details
        $stmt = $conn->prepare("
            UPDATE users 
            SET username = ?, email = ? 
            WHERE id = ?
        ");
        $stmt->execute([$data['username'], $data['email'], $data['user_id']]);

        // Update current account balance
        $stmt = $conn->prepare("
            UPDATE accounts 
            SET balance = ? 
            WHERE user_id = ? AND account_type = 'current'
        ");
        $stmt->execute([$data['current_balance'], $data['user_id']]);

        // Update savings account balance
        $stmt = $conn->prepare("
            UPDATE accounts 
            SET balance = ? 
            WHERE user_id = ? AND account_type = 'savings'
        ");
        $stmt->execute([$data['savings_balance'], $data['user_id']]);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log($e->getMessage());
        return false;
    }
}

function deleteClient($userId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $conn->beginTransaction();

        // Delete accounts first (due to foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM accounts WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Then delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log($e->getMessage());
        return false;
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $result = addClient($_POST);
                echo json_encode(['success' => $result]);
                break;
            
            case 'update':
                $result = updateClient($_POST);
                echo json_encode(['success' => $result]);
                break;
            
            case 'delete':
                $data = json_decode(file_get_contents('php://input'), true);
                $result = deleteClient($data['user_id']);
                echo json_encode(['success' => $result]);
                break;
            
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    }
}
?>
