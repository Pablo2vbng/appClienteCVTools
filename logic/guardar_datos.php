<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Capturar el cuerpo de la petición
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($_SESSION['cliente_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No hay sesion o datos']);
    exit;
}

try {
    $tipo = $data['tipo']; // 'pedido' o 'presupuesto'
    $tabla = ($tipo == 'pedido') ? 'pedidos' : 'presupuestos';
    
    // Preparar SQL dinámico
    if ($tipo == 'presupuesto') {
        $stmt = $conn->prepare("INSERT INTO presupuestos (cliente_id, total, detalle, margen_aplicado) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['cliente_id'], $data['total'], json_encode($data['items']), $data['margen']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO pedidos (cliente_id, total, detalle) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['cliente_id'], $data['total'], json_encode($data['items'])]);
    }
    
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}