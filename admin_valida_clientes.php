<?php
session_start();
require_once 'logic/db.php';

if (!isset($_SESSION['admin_pablo'])) {
    header("Location: login.php");
    exit();
}

// --- ACCIONES CRUD CLIENTES ---
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM usuarios_clientes WHERE id = ? AND es_admin = 0");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: admin_valida_clientes.php?msg=deleted");
    exit();
}

if (isset($_POST['update_client'])) {
    $stmt = $conn->prepare("UPDATE usuarios_clientes SET estado = 'activo', tarifa_asignada = ? WHERE id = ?");
    $stmt->execute([$_POST['tarifa'], $_POST['client_id']]);
    header("Location: admin_valida_clientes.php?msg=updated");
    exit();
}

// CONSULTAS
$clientes = $conn->query("SELECT * FROM usuarios_clientes WHERE es_admin = 0 ORDER BY estado ASC, fecha_registro DESC")->fetchAll();

// CONSULTA PEDIDOS con datos del cliente
$pedidos = $conn->query("
    SELECT p.*, u.nombre as cliente_nombre, u.empresa as cliente_empresa 
    FROM pedidos p 
    JOIN usuarios_clientes u ON p.cliente_id = u.id 
    ORDER BY p.fecha DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .tab-btn { padding: 10px 20px; border: none; background: #eee; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .tab-btn.active { background: #007aff; color: white; }
        .admin-section { display: none; }
        .admin-section.active { display: block; }
        
        .order-row { background: white; padding: 15px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #eee; }
        .order-details { font-size: 0.85rem; background: #f9f9f9; padding: 10px; border-radius: 8px; margin-top: 10px; }
        .search-admin { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; margin-bottom: 20px; }
    </style>
</head>
<body style="background:#f4f7f6;">

    <div class="container">
        <header style="display:flex; justify-content:space-between; align-items:center; padding: 20px 0;">
            <img src="img/cvtools.png" style="width: 120px;">
            <a href="logout.php" style="color:red; font-weight:bold;">Cerrar Admin</a>
        </header>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('clientes')">Gestión de Clientes</button>
            <button class="tab-btn" onclick="showTab('pedidos')">Pedidos Recibidos</button>
        </div>

        <!-- SECCIÓN CLIENTES -->
        <section id="clientes" class="admin-section active">
            <h2>Validación de Usuarios</h2>
            <div class="menu-grid" style="grid-template-columns: 1fr;">
                <?php foreach($clientes as $c): ?>
                    <div style="background:white; padding:20px; border-radius:15px; margin-bottom:15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <span style="font-size:0.7rem; padding:3px 8px; border-radius:5px; background:<?= ($c['estado']=='activo') ? '#d4edda':'#fff3cd' ?>;">
                            <?= strtoupper($c['estado']) ?>
                        </span>
                        <h3><?= htmlspecialchars($c['empresa']) ?> (<?= htmlspecialchars($c['nombre']) ?>)</h3>
                        <p style="font-size:0.9rem; color:#666;">Tel: <?= $c['telefono'] ?> | Email: <?= $c['email'] ?></p>
                        
                        <form method="POST" style="display:flex; gap:10px; margin-top:15px;">
                            <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                            <select name="tarifa" style="flex-grow:1; padding:8px; border-radius:5px;">
                                <option value="Tarifa_General.json" <?= $c['tarifa_asignada'] == 'Tarifa_General.json' ? 'selected' : '' ?>>General (50%)</option>
                                <option value="Tarifa_Bigmat.json" <?= $c['tarifa_asignada'] == 'Tarifa_Bigmat.json' ? 'selected' : '' ?>>BigMat</option>
                                <option value="Tarifa_Neopro.json" <?= $c['tarifa_asignada'] == 'Tarifa_Neopro.json' ? 'selected' : '' ?>>Neopro (52%)</option>
                                <option value="Tarifa_Ehlis.json" <?= $c['tarifa_asignada'] == 'Tarifa_Ehlis.json' ? 'selected' : '' ?>>Ehlis (52%)</option>
                                <option value="Tarifa_Cecofersa.json" <?= $c['tarifa_asignada'] == 'Tarifa_Cecofersa.json' ? 'selected' : '' ?>>Cecofersa (52%)</option>
                            </select>
                            <button type="submit" name="update_client" style="background:#007aff; color:white; border:none; padding:8px 15px; border-radius:5px;">Validar/Guardar</button>
                        </form>
                        <div style="text-align:right; margin-top:10px;">
                            <a href="?delete_id=<?= $c['id'] ?>" onclick="return confirm('¿Borrar?')" style="color:red; font-size:0.8rem;">Eliminar Cliente</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SECCIÓN PEDIDOS -->
        <section id="pedidos" class="admin-section">
            <h2>Historial de Pedidos Web</h2>
            <input type="text" id="orderSearch" class="search-admin" placeholder="Filtrar por empresa o nombre de cliente..." onkeyup="filterOrders()">
            
            <div id="ordersList">
                <?php foreach($pedidos as $p): 
                    $items = json_decode($p['detalle'], true);
                ?>
                    <div class="order-row" data-search="<?= strtolower($p['cliente_nombre'].' '.$p['cliente_empresa']) ?>">
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eee; padding-bottom:5px;">
                            <strong><?= htmlspecialchars($p['cliente_empresa']) ?></strong>
                            <span style="color:#007aff; font-weight:bold;"><?= number_format($p['total'], 2) ?> €</span>
                        </div>
                        <div style="font-size:0.8rem; color:#888; margin-top:5px;">
                            Fecha: <?= date("d/m/Y H:i", strtotime($p['fecha'])) ?> | Cliente: <?= htmlspecialchars($p['cliente_nombre']) ?>
                        </div>
                        <div class="order-details">
                            <strong>Detalle del pedido:</strong><br>
                            <?php foreach($items as $i): ?>
                                - <?= $i['qty'] ?>x [<?= $i['ref'] ?>] <?= $i['desc'] ?><br>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <a href="index.php" class="back-link">← Ir al Buscador</a>
    </div>

    <script>
        function showTab(id) {
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            event.target.classList.add('active');
        }

        function filterOrders() {
            let val = document.getElementById('orderSearch').value.toLowerCase();
            document.querySelectorAll('.order-row').forEach(row => {
                let text = row.getAttribute('data-search');
                row.style.display = text.includes(val) ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>