<?php
require_once(__DIR__ . '/vusers.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = deleteClient($data['user_id']);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
}
?>
