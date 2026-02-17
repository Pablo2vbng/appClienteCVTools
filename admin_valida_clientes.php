<?php
session_start();
require_once 'logic/db.php';

// 1. LOGIN ADMIN
$ADMIN_USER = "Pablo2vbngdaw";
$ADMIN_PASS = "Piramide73++%%";

if (isset($_POST['admin_login'])) {
    if ($_POST['user'] === $ADMIN_USER && $_POST['pass'] === $ADMIN_PASS) {
        $_SESSION['admin_pablo'] = true;
    } else { $error = "Credenciales incorrectas."; }
}

if (isset($_GET['logout'])) { unset($_SESSION['admin_pablo']); header("Location: admin_valida_clientes.php"); exit(); }

// 2. MOSTRAR LOGIN SI NO ESTÁ LOGUEADO
if (!isset($_SESSION['admin_pablo'])):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-box { max-width: 340px; margin: 80px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 10px; }
    </style>
</head>
<body style="display:flex; align-items:center; justify-content:center; height:100vh; background:#eee;">
    <div class="login-box">
        <img src="img/cvtools.png" style="width:120px; margin-bottom:20px;">
        <h3>Panel Control Clientes</h3>
        <?php if(isset($error)) echo "<p style='color:red;font-size:0.8em;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="user" placeholder="Usuario" required>
            <input type="password" name="pass" placeholder="Contraseña" required>
            <button type="submit" name="admin_login" class="btn-approve" style="background:var(--dark);">Entrar</button>
        </form>
    </div>
</body>
</html>
<?php exit(); endif;

// 3. PROCESAR ALTA
if (isset($_POST['activar_id'])) {
    $stmt = $conn->prepare("UPDATE usuarios_clientes SET estado = 'activo', tarifa_asignada = ? WHERE id = ?");
    $stmt->execute([$_POST['tarifa'], $_POST['activar_id']]);
    header("Location: admin_valida_clientes.php"); exit();
}

$pendientes = $conn->query("SELECT * FROM usuarios_clientes WHERE estado = 'pendiente' ORDER BY fecha_registro DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación Clientes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="admin-header" style="display:flex; justify-content:space-between; align-items:center;">
            <img src="img/cvtools.png" style="width:100px;">
            <a href="?logout=true" style="color:red; text-decoration:none; font-size:0.8em; border:1px solid red; padding:5px 10px; border-radius:5px;">Salir</a>
        </header>

        <h1>Validación de Clientes</h1>

        <?php foreach($pendientes as $c): ?>
            <div class="admin-card">
                <div class="info-grid-admin">
                    <div class="info-row"><strong>Nombre:</strong><span><?= $c['nombre'] ?></span></div>
                    <div class="info-row"><strong>Empresa:</strong><span><?= $c['empresa'] ?></span></div>
                    <div class="info-row"><strong>Email:</strong><span><?= $c['email'] ?></span></div>
                    <div class="info-row"><strong>Teléfono:</strong><span><?= $c['telefono'] ?></span></div>
                </div>

                <div class="comment-box">
                    <strong>Comentario / Grupo:</strong><br>
                    <?= nl2br(htmlspecialchars($c['comentario'])) ?>
                </div>

                <form method="POST" class="tariff-select-container">
                    <input type="hidden" name="activar_id" value="<?= $c['id'] ?>">
                    <select name="tarifa" required>
                        <option value="Tarifa_General.json">Tarifa General (Dto 50%)</option>
                        <option value="Tarifa_Bigmat.json">Tarifa BigMat (Dto 50%)</option>
                        <option value="Tarifa_Coferdroza.json">Tarifa Coferdroza (Dto 50%)</option>
                        <option value="Tarifa_Neopro.json">Tarifa Neopro (Dto 52%)</option>
                        <option value="Tarifa_Ehlis.json">Tarifa Ehlis (Dto 52%)</option>
                        <option value="Tarifa_Cecofersa.json">Tarifa Cecofersa (Dto 52%)</option>
                        <option value="Tarifa_Synergas.json">Tarifa Synergas (Dto 52%)</option>
                        <option value="Tarifa_IndustrialPro.json">Tarifa Industrial Pro (Dto 52%)</option>
                    </select>
                    <button type="submit" class="btn-approve">✅ Validar y Activar</button>
                </form>
            </div>
        <?php endforeach; if(!$pendientes) echo "<p style='text-align:center; padding:40px; color:#999;'>No hay altas pendientes.</p>"; ?>
        
        <a href="index.php" class="back-link">Volver al portal</a>
    </div>
</body>
</html>