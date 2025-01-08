<?php


require_once (__DIR__ . '/../config/config.php');
require_once (__DIR__ . '/../models/UserModel.php');


$db = new Database('bank');
$conn  = $db->getConnection();

$userController = new User($conn);
$stmt = $userController->getAllUsers();
$result = $stmt->fetchAll();

// var_dump($result);

?>
