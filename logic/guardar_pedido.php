<?php
session_start();
require_once 'db.php';

// Obtener datos del post (JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data && isset($_SESSION['cliente_id'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO pedidos (cliente_id, total, detalle) VALUES (?, ?, ?)");
        $stmt->execute([
            $_SESSION['cliente_id'],
            $data['total'],
            json_encode($data['items'])
        ]);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
}
?>