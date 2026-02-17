<?php
session_start();
require_once 'logic/db.php';

if (!isset($_SESSION['admin_pablo'])) { header("Location: login.php"); exit(); }

// --- ACCIONES CLIENTES ---
if (isset($_GET['delete_id'])) {
    $conn->prepare("DELETE FROM usuarios_clientes WHERE id = ? AND es_admin = 0")->execute([$_GET['delete_id']]);
    header("Location: admin_valida_clientes.php?msg=deleted"); exit();
}
if (isset($_POST['update_client'])) {
    $conn->prepare("UPDATE usuarios_clientes SET estado = 'activo', tarifa_asignada = ? WHERE id = ?")->execute([$_POST['tarifa'], $_POST['client_id']]);
    header("Location: admin_valida_clientes.php?msg=updated"); exit();
}

// Consultas
$clientes = $conn->query("SELECT * FROM usuarios_clientes WHERE es_admin = 0 ORDER BY estado ASC, fecha_registro DESC")->fetchAll();
$pedidos = $conn->query("SELECT p.*, u.nombre as cli, u.empresa as emp FROM pedidos p JOIN usuarios_clientes u ON p.cliente_id = u.id ORDER BY p.fecha DESC")->fetchAll();
$presupuestos = $conn->query("SELECT pr.*, u.nombre as cli, u.empresa as emp FROM presupuestos pr JOIN usuarios_clientes u ON pr.cliente_id = u.id ORDER BY pr.fecha DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin CV Tools</title>
    <link rel="stylesheet" href="style.css?v=1.2">
    <style>
        .tabs { display: flex; gap: 10px; margin: 20px 0; border-bottom: 2px solid #ddd; padding-bottom: 10px; overflow-x: auto; }
        .tab-btn { padding: 12px 20px; border: none; background: #e0e0e0; border-radius: 10px; cursor: pointer; font-weight: bold; white-space: nowrap; }
        .tab-btn.active { background: #007aff; color: white; }
        .admin-section { display: none; }
        .admin-section.active { display: block; }
        .card { background: white; padding: 20px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .details-box { background: #f0f7ff; padding: 12px; border-radius: 10px; margin-top: 10px; font-size: 0.85rem; }
    </style>
</head>
<body style="background:#f8f9fa;">
    <div class="container">
        <header style="display:flex; justify-content:space-between; align-items:center; padding: 20px 0;">
            <img src="img/cvtools.png" style="width: 120px;">
            <a href="logout.php" style="color:red; font-weight:bold;">Cerrar Sesión</a>
        </header>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('clientes')">Usuarios</button>
            <button class="tab-btn" onclick="showTab('pedidos')">Pedidos Recibidos</button>
            <button class="tab-btn" onclick="showTab('presupuestos')">Presupuestos Enviados</button>
        </div>

        <section id="clientes" class="admin-section active">
            <?php foreach($clientes as $c): ?>
                <div class="card">
                    <span style="background:<?= $c['estado']=='activo'?'#d4edda':'#fff3cd' ?>; padding:4px 10px; border-radius:5px; font-size:0.7rem; font-weight:bold;"><?= strtoupper($c['estado']) ?></span>
                    <h3 style="margin:10px 0 5px 0;"><?= htmlspecialchars($c['empresa']) ?></h3>
                    <p style="font-size:0.85rem; color:#666;"><?= $c['nombre'] ?> | <?= $c['email'] ?> | <?= $c['telefono'] ?></p>
                    <div style="font-size:0.8rem; color:#0056b3; margin-top:5px;"><strong>Nota:</strong> <?= htmlspecialchars($c['comentario']) ?></div>
                    <form method="POST" style="margin-top:15px; display:flex; gap:10px;">
                        <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                        <select name="tarifa" style="flex-grow:1; padding:10px; border-radius:8px;">
                            <option value="Tarifa_General.json" <?= $c['tarifa_asignada'] == 'Tarifa_General.json' ? 'selected' : '' ?>>General (50%)</option>
                            <option value="Tarifa_Bigmat.json" <?= $c['tarifa_asignada'] == 'Tarifa_Bigmat.json' ? 'selected' : '' ?>>BigMat</option>
                            <option value="Tarifa_Neopro.json" <?= $c['tarifa_asignada'] == 'Tarifa_Neopro.json' ? 'selected' : '' ?>>Neopro (52%)</option>
                            <option value="Tarifa_Ehlis.json" <?= $c['tarifa_asignada'] == 'Tarifa_Ehlis.json' ? 'selected' : '' ?>>Ehlis (52%)</option>
                            <option value="Tarifa_Cecofersa.json" <?= $c['tarifa_asignada'] == 'Tarifa_Cecofersa.json' ? 'selected' : '' ?>>Cecofersa (52%)</option>
                            <option value="Tarifa_Synergas.json" <?= $c['tarifa_asignada'] == 'Tarifa_Synergas.json' ? 'selected' : '' ?>>Synergas</option>
                            <option value="Tarifa_IndustrialPro.json" <?= $c['tarifa_asignada'] == 'Tarifa_IndustrialPro.json' ? 'selected' : '' ?>>Industrial Pro</option>
                            <option value="Tarifa_Grandes_Cuentas.json" <?= $c['tarifa_asignada'] == 'Tarifa_Grandes_Cuentas.json' ? 'selected' : '' ?>>Grandes Cuentas</option>
                        </select>
                        <button type="submit" name="update_client" style="background:#007aff; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:bold;">Guardar</button>
                    </form>
                    <div style="text-align:right; margin-top:10px;"><a href="?delete_id=<?= $c['id'] ?>" style="color:red; font-size:0.8rem;" onclick="return confirm('¿Borrar?')">Eliminar Cliente</a></div>
                </div>
            <?php endforeach; ?>
        </section>

        <section id="pedidos" class="admin-section">
            <?php foreach($pedidos as $p): ?>
                <div class="card">
                    <div style="display:flex; justify-content:space-between;"><strong><?= $p['emp'] ?></strong><b style="color:#007aff;"><?= number_format($p['total'], 2) ?> €</b></div>
                    <p style="font-size:0.8rem; color:#888;"><?= date("d/m/Y H:i", strtotime($p['fecha'])) ?> | Cliente: <?= $p['cli'] ?></p>
                    <div class="details-box">
                        <?php foreach(json_decode($p['detalle'], true) as $i): ?>
                            • <?= $i['qty'] ?>x [<?= $i['ref'] ?>] <?= $i['desc'] ?><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section id="presupuestos" class="admin-section">
            <?php foreach($presupuestos as $pr): ?>
                <div class="card">
                    <div style="display:flex; justify-content:space-between;"><strong><?= $pr['emp'] ?></strong><b style="color:#28a745;"><?= number_format($pr['total'], 2) ?> € (Neto)</b></div>
                    <p style="font-size:0.8rem; color:#888;"><?= date("d/m/Y H:i", strtotime($pr['fecha'])) ?> | Margen aplicado: <?= $pr['margen_aplicado'] ?>%</p>
                    <div class="details-box">
                        <?php foreach(json_decode($pr['detalle'], true) as $i): ?>
                            • <?= $i['qty'] ?>x [<?= $i['ref'] ?>] <?= $i['desc'] ?><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <a href="index.php" class="back-link">← Volver al Portal</a>
    </div>

    <script>
        function showTab(id) {
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            event.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>