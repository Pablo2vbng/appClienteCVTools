<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['cliente_id'])) {
    $data = JSON_decode(file_get_contents('php://input'), true);
    
    $stmt = $conn->prepare("INSERT INTO pedidos (cliente_id, total, detalle) VALUES (?, ?, ?)");
    $stmt->execute([
        $_SESSION['cliente_id'],
        $data['total'],
        JSON_encode($data['items'])
    ]);
    echo JSON_encode(['status' => 'ok']);
}
?>