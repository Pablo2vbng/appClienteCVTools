<?php
session_start();
require_once 'logic/db.php';

// SEGURIDAD: Solo puede entrar si se logueó como admin
if (!isset($_SESSION['admin_pablo'])) {
    header("Location: login.php");
    exit();
}

// --- PROCESAR ACCIONES DEL CRUD ---

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

// CONSULTAR LISTADO (Pendientes primero, luego el resto)
$stmt = $conn->query("SELECT * FROM usuarios_clientes WHERE es_admin = 0 ORDER BY estado ASC, fecha_registro DESC");
$clientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Admin</title>
    <link rel="stylesheet" href="style.css?v=1.2">
    <style>
        .admin-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 2px solid #ddd; margin-bottom: 20px; }
        .client-card { background: white; padding: 20px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); position: relative; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: bold; margin-bottom: 10px; }
        .status-pendiente { background: #fff3cd; color: #856404; }
        .status-activo { background: #d4edda; color: #155724; }
        .btn-delete { color: #d9534f; font-size: 0.8rem; text-decoration: underline; background: none; border: none; cursor: pointer; }
        .crud-grid { display: grid; grid-template-columns: 1fr; gap: 15px; }
        @media (min-width: 768px) { .crud-grid { grid-template-columns: 1fr 1fr; } }
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

        <h1>Gestión de Clientes</h1>

        <?php if(isset($_GET['msg'])): ?>
            <div style="background:#d4edda; color:#155724; padding:10px; border-radius:8px; margin-bottom:20px; text-align:center; font-size:0.9rem;">
                <?= ($_GET['msg'] == 'updated') ? '✅ Cliente actualizado correctamente.' : '🗑️ Cliente eliminado.' ?>
            </div>
        <?php endif; ?>

        <div class="crud-grid">
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

        <a href="index.php" class="back-link">← Ir al Buscador</a>
    </div>

</body>
</html>