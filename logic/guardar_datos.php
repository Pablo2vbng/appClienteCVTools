<?php
session_start();
require_once 'db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data && isset($_SESSION['cliente_id'])) {
    try {
        $tipo = $data['tipo']; // 'pedido' o 'presupuesto'
        $tabla = ($tipo == 'pedido') ? 'pedidos' : 'presupuestos';
        
        $sql = "INSERT INTO $tabla (cliente_id, total, detalle" . ($tipo == 'presupuesto' ? ", margen_aplicado" : "") . ") VALUES (?, ?, ?" . ($tipo == 'presupuesto' ? ", ?" : "") . ")";
        
        $params = [$_SESSION['cliente_id'], $data['total'], json_encode($data['items'])];
        if ($tipo == 'presupuesto') { $params[] = $data['margen']; }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>