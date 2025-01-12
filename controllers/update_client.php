<?php
require_once(__DIR__ . '/vusers.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['action'] = 'update';
    $result = updateClient($_POST);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
}
?>
