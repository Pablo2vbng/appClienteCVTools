<?php
session_start();
require_once 'logic/db.php';

if (!isset($_SESSION['admin_pablo'])) {
    header("Location: login.php");
    exit();
}

// --- ACCIONES CLIENTES ---
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

$pedidos = $conn->query("SELECT p.*, u.nombre as cli, u.empresa as emp FROM pedidos p JOIN usuarios_clientes u ON p.cliente_id = u.id ORDER BY p.fecha DESC")->fetchAll();

$presupuestos = $conn->query("SELECT pr.*, u.nombre as cli, u.empresa as emp FROM presupuestos pr JOIN usuarios_clientes u ON pr.cliente_id = u.id ORDER BY pr.fecha DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin CV Tools</title>
    <link rel="stylesheet" href="style.css?v=1.2">
    <style>
        .tabs { display: flex; gap: 5px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 5px; }
        .tab-btn { padding: 12px 15px; border: none; background: #ddd; border-radius: 8px; cursor: pointer; white-space: nowrap; font-weight: bold; }
        .tab-btn.active { background: #007aff; color: white; }
        .admin-section { display: none; }
        .admin-section.active { display: block; }
        .card { background: white; padding: 15px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .details { font-size: 0.85rem; background: #f4f7f6; padding: 10px; border-radius: 8px; margin-top: 10px; }
    </style>
</head>
<body style="background:#f8f9fa;">
    <div class="container">
        <header style="display:flex; justify-content:space-between; align-items:center; padding: 20px 0;">
            <img src="img/cvtools.png" style="width: 100px;">
            <a href="logout.php" style="color:red; font-weight:bold;">Cerrar</a>
        </header>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('clientes')">Usuarios</button>
            <button class="tab-btn" onclick="showTab('pedidos')">Pedidos Web</button>
            <button class="tab-btn" onclick="showTab('presupuestos')">Presupuestos WhatsApp</button>
        </div>

        <!-- CLIENTES -->
        <section id="clientes" class="admin-section active">
            <?php foreach($clientes as $c): ?>
                <div class="card">
                    <span style="font-size:0.7rem; font-weight:bold; color:<?= $c['estado']=='activo'?'green':'orange' ?>"><?= strtoupper($c['estado']) ?></span>
                    <h3 style="margin:5px 0;"><?= htmlspecialchars($c['empresa']) ?></h3>
                    <p style="font-size:0.85rem; color:#666;"><?= $c['nombre'] ?> | <?= $c['email'] ?> | <?= $c['telefono'] ?></p>
                    <div style="font-size:0.8rem; background:#e7f3ff; padding:8px; border-radius:5px; margin:10px 0;"><?= nl2br(htmlspecialchars($c['comentario'])) ?></div>
                    
                    <form method="POST" style="display:flex; gap:5px;">
                        <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                        <select name="tarifa" style="flex-grow:1; padding:8px; border-radius:8px;">
                            <option value="Tarifa_General.json" <?= $c['tarifa_asignada'] == 'Tarifa_General.json' ? 'selected' : '' ?>>General (50%)</option>
                            <option value="Tarifa_Bigmat.json" <?= $c['tarifa_asignada'] == 'Tarifa_Bigmat.json' ? 'selected' : '' ?>>BigMat</option>
                            <option value="Tarifa_Neopro.json" <?= $c['tarifa_asignada'] == 'Tarifa_Neopro.json' ? 'selected' : '' ?>>Neopro (52%)</option>
                            <option value="Tarifa_Ehlis.json" <?= $c['tarifa_asignada'] == 'Tarifa_Ehlis.json' ? 'selected' : '' ?>>Ehlis (52%)</option>
                            <option value="Tarifa_Cecofersa.json" <?= $c['tarifa_asignada'] == 'Tarifa_Cecofersa.json' ? 'selected' : '' ?>>Cecofersa (52%)</option>
                            <option value="Tarifa_Synergas.json" <?= $c['tarifa_asignada'] == 'Tarifa_Synergas.json' ? 'selected' : '' ?>>Synergas</option>
                            <option value="Tarifa_IndustrialPro.json" <?= $c['tarifa_asignada'] == 'Tarifa_IndustrialPro.json' ? 'selected' : '' ?>>Industrial Pro</option>
                            <option value="Tarifa_Grandes_Cuentas.json" <?= $c['tarifa_asignada'] == 'Tarifa_Grandes_Cuentas.json' ? 'selected' : '' ?>>Grandes Cuentas</option>
                        </select>
                        <button type="submit" name="update_client" style="background:#007aff; color:white; border:none; padding:8px 12px; border-radius:8px; font-weight:bold;">OK</button>
                    </form>
                    <a href="?delete_id=<?= $c['id'] ?>" onclick="return confirm('¿Borrar?')" style="display:block; text-align:right; color:red; font-size:0.75rem; margin-top:10px;">Eliminar</a>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- PEDIDOS -->
        <section id="pedidos" class="admin-section">
            <input type="text" class="search-admin" placeholder="Buscar pedido..." onkeyup="filter('pedidos', this.value)" style="width:100%; padding:10px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">
            <div id="list-pedidos">
                <?php foreach($pedidos as $p): ?>
                    <div class="card" data-search="<?= strtolower($p['emp'].' '.$p['cli']) ?>">
                        <div style="display:flex; justify-content:space-between;">
                            <strong><?= $p['emp'] ?></strong>
                            <b style="color:#007aff"><?= $p['total'] ?> €</b>
                        </div>
                        <span style="font-size:0.75rem; color:#888;"><?= date("d/m H:i", strtotime($p['fecha'])) ?> - <?= $p['cli'] ?></span>
                        <div class="details">
                            <?php foreach(json_decode($p['detalle'], true) as $i): ?>
                                • <?= $i['qty'] ?>x [<?= $i['ref'] ?>] <?= $i['desc'] ?><br>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- PRESUPUESTOS -->
        <section id="presupuestos" class="admin-section">
            <input type="text" class="search-admin" placeholder="Buscar presupuesto..." onkeyup="filter('presupuestos', this.value)" style="width:100%; padding:10px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">
            <div id="list-presupuestos">
                <?php foreach($presupuestos as $pr): ?>
                    <div class="card" data-search="<?= strtolower($pr['emp'].' '.$pr['cli']) ?>">
                        <div style="display:flex; justify-content:space-between;">
                            <strong><?= $pr['emp'] ?></strong>
                            <b style="color:#28a745"><?= $pr['total'] ?> € (Coste)</b>
                        </div>
                        <span style="font-size:0.75rem; color:#888;"><?= date("d/m H:i", strtotime($pr['fecha'])) ?> - Margen: <?= $pr['margen_aplicado'] ?>%</span>
                        <div class="details">
                            <?php foreach(json_decode($pr['detalle'], true) as $i): ?>
                                • <?= $i['qty'] ?>x [<?= $i['ref'] ?>] <?= $i['desc'] ?><br>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <a href="index.php" class="back-link">← Volver</a>
    </div>

    <script>
        function showTab(id) {
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            event.currentTarget.classList.add('active');
        }
        function filter(type, val) {
            val = val.toLowerCase();
            document.querySelectorAll(`#list-${type} .card`).forEach(c => {
                c.style.display = c.getAttribute('data-search').includes(val) ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>