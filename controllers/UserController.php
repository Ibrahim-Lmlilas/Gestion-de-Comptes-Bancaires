<?php
require_once (__DIR__ . '/../config/config.php');
require_once (__DIR__ . '/../models/UserModel.php');

function getUsers() {
    $db = new Database('bank');
    $conn = $db->getConnection();
    $userController = new User($conn);
    $stmt = $userController->getAllUsers();
    return $stmt->fetchAll();
}

function updateUser($userId, $data) {
    $db = new Database('bank');
    $conn = $db->getConnection();
    $userController = new User($conn);
    
    $userController->setUsername($data['username']);
    $userController->setEmail($data['email']);
    
    return $userController->updateUser($userId);
}

function deleteUser($userId) {
    $db = new Database('bank');
    $conn = $db->getConnection();
    $userController = new User($conn);
    
    return $userController->deleteUser($userId);
}

?>
