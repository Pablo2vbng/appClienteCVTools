<?php
session_start();
require_once 'logic/db.php';

// SEGURIDAD: Solo puede entrar si se logueó como admin
if (!isset($_SESSION['admin_pablo'])) {
    header("Location: login.php");
    exit();
}

// --- PROCESAR ACCIONES DEL CRUD CLIENTES ---

// 1. ELIMINAR CLIENTE
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM usuarios_clientes WHERE id = ? AND es_admin = 0");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: admin_valida_clientes.php?msg=deleted");
    exit();
}

// 2. ACTIVAR / ACTUALIZAR TARIFA
if (isset($_POST['update_client'])) {
    $stmt = $conn->prepare("UPDATE usuarios_clientes SET estado = 'activo', tarifa_asignada = ? WHERE id = ?");
    $stmt->execute([$_POST['tarifa'], $_POST['client_id']]);
    header("Location: admin_valida_clientes.php?msg=updated");
    exit();
}

// CONSULTA DE CLIENTES (Pendientes primero)
$clientes = $conn->query("SELECT * FROM usuarios_clientes WHERE es_admin = 0 ORDER BY estado ASC, fecha_registro DESC")->fetchAll();

// CONSULTA DE PEDIDOS RECIBIDOS (Uniendo con la tabla de usuarios para saber quién es)
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
    <title>Gestión de Clientes y Pedidos - Admin</title>
    <link rel="stylesheet" href="style.css?v=1.2">
    <style>
        .admin-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 2px solid #ddd; margin-bottom: 20px; }
        .client-card { background: white; padding: 20px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); position: relative; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: bold; margin-bottom: 10px; }
        .status-pendiente { background: #fff3cd; color: #856404; }
        .status-activo { background: #d4edda; color: #155724; }
        .btn-delete { color: #d9534f; font-size: 0.8rem; text-decoration: underline; background: none; border: none; cursor: pointer; }
        
        /* Estilos Pestañas */
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 12px 20px; border: none; background: #e0e0e0; border-radius: 10px; cursor: pointer; font-weight: bold; font-family: inherit; }
        .tab-btn.active { background: #007aff; color: white; }
        .admin-section { display: none; }
        .admin-section.active { display: block; }

        /* Estilos Pedidos */
        .order-row { background: white; padding: 15px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
        .order-details { font-size: 0.85rem; background: #f0f7ff; padding: 12px; border-radius: 8px; margin-top: 10px; color: #333; line-height: 1.4; }
        .search-admin { width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #ccc; margin-bottom: 20px; font-size: 1rem; }
    </style>
</head>
<body style="background:#f8f9fa;">

    <div class="container">
        <header class="admin-header">
            <img src="img/cvtools.png" style="width: 100px;">
            <div>
                <span style="font-size: 0.8rem; color:#666;">Sesión: Admin Pablo</span> | 
                <a href="logout.php" style="color:red; font-weight:bold; font-size:0.8rem;">Cerrar</a>
            </div>
        </header>

        <h1>Panel de Control Admin</h1>

        <?php if(isset($_GET['msg'])): ?>
            <div style="background:#d4edda; color:#155724; padding:10px; border-radius:8px; margin-bottom:20px; text-align:center; font-size:0.9rem;">
                <?= ($_GET['msg'] == 'updated') ? '✅ Cliente actualizado correctamente.' : '🗑️ Cliente eliminado.' ?>
            </div>
        <?php endif; ?>

        <!-- Selector de Pestañas -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('clientes')">Usuarios / Clientes</button>
            <button class="tab-btn" onclick="showTab('pedidos')">Pedidos Recibidos</button>
        </div>

        <!-- SECCIÓN 1: GESTIÓN DE CLIENTES -->
        <section id="clientes" class="admin-section active">
            <div class="crud-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                <?php foreach($clientes as $c): ?>
                    <div class="client-card">
                        <span class="status-badge status-<?= $c['estado'] ?>">
                            <?= strtoupper($c['estado']) ?>
                        </span>
                        
                        <h3 style="margin:0;"><?= htmlspecialchars($c['nombre']) ?></h3>
                        <p style="font-size:0.85rem; color:#666; margin: 5px 0;">
                            <strong>Empresa:</strong> <?= htmlspecialchars($c['empresa']) ?><br>
                            <strong>Email:</strong> <?= htmlspecialchars($c['email']) ?><br>
                            <strong>Tel:</strong> <?= htmlspecialchars($c['telefono']) ?>
                        </p>

                        <div style="background:#f0f7ff; padding:10px; border-radius:8px; font-size:0.8rem; margin:10px 0; color:#0056b3;">
                            <strong>Comentario:</strong> <?= nl2br(htmlspecialchars($c['comentario'])) ?>
                        </div>

                        <form method="POST" style="margin-top:15px; border-top:1px solid #eee; padding-top:15px;">
                            <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                            <label style="font-size:0.75rem; font-weight:bold; display:block; margin-bottom:5px;">ASIGNAR TARIFA:</label>
                            <div style="display:flex; gap:10px;">
                                <select name="tarifa" style="flex-grow:1; padding:8px; border-radius:8px; border:1px solid #ccc;">
                                    <option value="Tarifa_General.json" <?= $c['tarifa_asignada'] == 'Tarifa_General.json' ? 'selected' : '' ?>>General (50%)</option>
                                    <option value="Tarifa_Bigmat.json" <?= $c['tarifa_asignada'] == 'Tarifa_Bigmat.json' ? 'selected' : '' ?>>BigMat</option>
                                    <option value="Tarifa_Neopro.json" <?= $c['tarifa_asignada'] == 'Tarifa_Neopro.json' ? 'selected' : '' ?>>Neopro (52%)</option>
                                    <option value="Tarifa_Ehlis.json" <?= $c['tarifa_asignada'] == 'Tarifa_Ehlis.json' ? 'selected' : '' ?>>Ehlis (52%)</option>
                                    <option value="Tarifa_Cecofersa.json" <?= $c['tarifa_asignada'] == 'Tarifa_Cecofersa.json' ? 'selected' : '' ?>>Cecofersa (52%)</option>
                                    <option value="Tarifa_Synergas.json" <?= $c['tarifa_asignada'] == 'Tarifa_Synergas.json' ? 'selected' : '' ?>>Synergas</option>
                                    <option value="Tarifa_IndustrialPro.json" <?= $c['tarifa_asignada'] == 'Tarifa_IndustrialPro.json' ? 'selected' : '' ?>>Industrial Pro</option>
                                    <option value="Tarifa_Grandes_Cuentas.json" <?= $c['tarifa_asignada'] == 'Tarifa_Grandes_Cuentas.json' ? 'selected' : '' ?>>Grandes Cuentas</option>
                                </select>
                                <button type="submit" name="update_client" style="background:#007aff; color:white; border:none; padding:8px 12px; border-radius:8px; font-weight:bold; cursor:pointer;">Guardar</button>
                            </div>
                        </form>

                        <div style="text-align:right; margin-top:15px;">
                            <a href="?delete_id=<?= $c['id'] ?>" onclick="return confirm('¿Eliminar a este cliente de forma permanente?')" class="btn-delete">Eliminar Cliente</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if(!$clientes) echo "<p style='text-align:center; padding:50px; color:#999;'>No hay clientes registrados.</p>"; ?>
        </section>

        <!-- SECCIÓN 2: HISTORIAL DE PEDIDOS -->
        <section id="pedidos" class="admin-section">
            <input type="text" id="orderSearch" class="search-admin" placeholder="Buscar pedidos por empresa o cliente..." onkeyup="filterOrders()">
            
            <div id="ordersList">
                <?php foreach($pedidos as $p): 
                    $items = json_decode($p['detalle'], true);
                ?>
                    <div class="order-row" data-search="<?= strtolower($p['cliente_nombre'].' '.$p['cliente_empresa']) ?>">
                        <div style="display:flex; justify-content:space-between; align-items: flex-start;">
                            <div>
                                <strong style="font-size:1.1rem;"><?= htmlspecialchars($p['cliente_empresa']) ?></strong><br>
                                <span style="font-size:0.85rem; color:#666;">Cliente: <?= htmlspecialchars($p['cliente_nombre']) ?></span>
                            </div>
                            <div style="text-align:right;">
                                <span style="color:#007aff; font-weight:bold; font-size:1.2rem;"><?= number_format($p['total'], 2) ?> €</span><br>
                                <span style="font-size:0.75rem; color:#888;"><?= date("d/m/Y H:i", strtotime($p['fecha'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <strong>Productos solicitados:</strong><br>
                            <?php foreach($items as $i): ?>
                                • <?= $i['qty'] ?> uds x [<?= $i['ref'] ?>] <?= htmlspecialchars($i['desc']) ?><br>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if(!$pedidos) echo "<p style='text-align:center; padding:50px; color:#999;'>Aún no se han registrado pedidos web.</p>"; ?>
        </section>

        <a href="index.php" class="back-link">← Ir al Buscador</a>
    </div>

    <script>
        // Función para cambiar entre pestañas
        function showTab(tabId) {
            // Ocultar todas las secciones
            document.querySelectorAll('.admin-section').forEach(section => {
                section.classList.remove('active');
            });
            // Quitar clase activa de botones
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            // Mostrar la seleccionada
            document.getElementById(tabId).classList.add('active');
            // Marcar botón como activo
            event.currentTarget.classList.add('active');
        }

        // Buscador de pedidos
        function filterOrders() {
            const val = document.getElementById('orderSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.order-row');
            
            rows.forEach(row => {
                const text = row.getAttribute('data-search');
                if (text.includes(val)) {
                    row.style.display = "block";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>

</body>
</html>