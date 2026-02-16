<?php
session_start();
require_once 'logic/db.php';

// 1. CONFIGURACIÓN DE CREDENCIALES ADMIN
$ADMIN_USER = "Pablo2vbngdaw";
$ADMIN_PASS = "Piramide73++%%";

// 2. LÓGICA DE LOGIN ADMIN
if (isset($_POST['admin_login'])) {
    if ($_POST['user'] === $ADMIN_USER && $_POST['pass'] === $ADMIN_PASS) {
        $_SESSION['admin_pablo'] = true;
    } else {
        $error = "Credenciales de administrador incorrectas.";
    }
}

// 3. LÓGICA DE LOGOUT ADMIN
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_pablo']);
    header("Location: admin_valida_clientes.php");
    exit();
}

// 4. SI NO ESTÁ AUTENTICADO, MOSTRAR LOGIN CON ESTILO
if (!isset($_SESSION['admin_pablo'])):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - App Cliente</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background-color: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-admin-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 350px; text-align: center; }
        .login-admin-card img { width: 120px; margin-bottom: 20px; }
        .login-admin-card input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-admin { width: 100%; padding: 14px; background: #333; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .error { color: #d9534f; font-size: 0.85em; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>
    <div class="login-admin-card">
        <img src="img/cvtools.png" alt="CV Tools">
        <h3>Panel Clientes</h3>
        <?php if(isset($error)) echo "<span class='error'>$error</span>"; ?>
        <form method="POST">
            <input type="text" name="user" placeholder="Usuario Administrador" required>
            <input type="password" name="pass" placeholder="Contraseña" required>
            <button type="submit" name="admin_login" class="btn-admin">Acceder</button>
        </form>
    </div>
</body>
</html>
<?php 
exit(); 
endif; 

// --- LÓGICA DE GESTIÓN (SI YA ESTÁ LOGUEADO) ---

// 5. ACTIVAR Y ASIGNAR TARIFA
if (isset($_POST['activar_id'])) {
    $stmt = $conn->prepare("UPDATE usuarios_clientes SET estado = 'activo', tarifa_asignada = ? WHERE id = ?");
    $stmt->execute([$_POST['tarifa'], $_POST['activar_id']]);
    header("Location: admin_valida_clientes.php");
    exit();
}

// 6. CONSULTAR PENDIENTES
$pendientes = $conn->query("SELECT * FROM usuarios_clientes WHERE estado = 'pendiente' ORDER BY fecha_registro DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Clientes - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .client-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 25px; border-left: 5px solid #007aff; }
        .client-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .info-item { font-size: 0.9em; color: #555; }
        .info-item strong { color: #333; display: block; margin-bottom: 2px; }
        .comment-area { background: #f8fbff; padding: 15px; border-radius: 8px; border: 1px solid #e1e9f5; margin: 15px 0; font-size: 0.95em; }
        .tariff-form { display: flex; align-items: flex-end; gap: 15px; padding-top: 15px; border-top: 1px solid #eee; }
        .tariff-form select { flex-grow: 1; padding: 10px; border-radius: 6px; border: 1px solid #ccc; }
        .btn-approve { background: #28a745; color: white; border: none; padding: 11px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .logout-link { color: #d9534f; text-decoration: none; font-size: 0.9em; font-weight: 500; border: 1px solid #d9534f; padding: 5px 12px; border-radius: 5px; }
    </style>
</head>
<body style="background: #f8f9fa;">
    <div class="admin-container">
        <div class="admin-header">
            <img src="img/cvtools.png" alt="Logo" style="width: 120px;">
            <a href="?logout=true" class="logout-link">Cerrar Sesión Admin</a>
        </div>

        <h1>Gestión de Altas de Clientes</h1>
        <p style="color:#666; margin-bottom:30px;">Revisa los datos y asigna la tarifa correspondiente para activar el acceso.</p>

        <?php foreach($pendientes as $c): ?>
            <div class="client-card">
                <div class="client-info-grid">
                    <div class="info-item">
                        <strong>Nombre Completo:</strong>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </div>
                    <div class="info-item">
                        <strong>Empresa / Razón Social:</strong>
                        <?= htmlspecialchars($c['empresa']) ?>
                    </div>
                    <div class="info-item">
                        <strong>Correo Electrónico:</strong>
                        <?= htmlspecialchars($c['email']) ?>
                    </div>
                    <div class="info-item">
                        <strong>Teléfono de Contacto:</strong>
                        <?= htmlspecialchars($c['telefono']) ?>
                    </div>
                </div>

                <div class="comment-area">
                    <strong>Comentario / Grupo del cliente:</strong><br>
                    <span style="color:#0056b3;"><?= nl2br(htmlspecialchars($c['comentario'])) ?></span>
                </div>

                <form method="POST" class="tariff-form">
                    <input type="hidden" name="activar_id" value="<?= $c['id'] ?>">
                    <div style="flex-grow:1">
                        <label style="display:block; font-size:0.8em; font-weight:bold; margin-bottom:5px;">Asignar Tarifa JSON:</label>
                        <select name="tarifa" required>
                            <option value="Tarifa_General.json">Tarifa General (Dto 50%)</option>
                            <option value="Tarifa_Bigmat.json">Tarifa BigMat (Dto 50%)</option>
                            <option value="Tarifa_Coferdroza.json">Tarifa Coferdroza (Dto 50%)</option>
                            <option value="Tarifa_Neopro.json">Tarifa Neopro (Dto 52%)</option>
                            <option value="Tarifa_Ehlis.json">Tarifa Ehlis (Dto 52%)</option>
                            <option value="Tarifa_Cecofersa.json">Tarifa Cecofersa (Dto 52%)</option>
                            <option value="Tarifa_Synergas.json">Tarifa Synergas (Dto 52%)</option>
                            <option value="Tarifa_IndustrialPro.json">Tarifa Industrial Pro (Dto 52%)</option>
                            <option value="Tarifa_Grandes_Cuentas.json">Tarifa Grandes Cuentas (Específica)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-approve">Aprobar y Activar</button>
                </form>
            </div>
        <?php endforeach; ?>

        <?php if(!$pendientes): ?>
            <div style="text-align:center; padding:50px; background:white; border-radius:12px; color:#999; border:2px dashed #ddd;">
                No hay nuevas solicitudes de alta pendientes.
            </div>
        <?php endif; ?>

        <div style="text-align:center; margin-top:30px;">
            <a href="index.php" class="back-link" style="color:#666">← Volver al Portal</a>
        </div>
    </div>
</body>
</html>