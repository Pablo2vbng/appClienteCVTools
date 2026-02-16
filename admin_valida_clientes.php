<?php
session_start();
require_once 'logic/db.php';

// Seguridad: Usuario Pablo
if (!isset($_SESSION['admin_pablo']) && !isset($_POST['admin_login'])) {
    if ($_POST && $_POST['user'] === "Pablo2vbngdaw" && $_POST['pass'] === "Piramide73++%%") {
        $_SESSION['admin_pablo'] = true;
    } else {
        die('<form method="POST" style="text-align:center; margin-top:50px;">
            <input type="text" name="user" placeholder="Admin User"><br>
            <input type="password" name="pass" placeholder="Admin Pass"><br>
            <button type="submit" name="admin_login">Acceder Panel Clientes</button></form>');
    }
}

// Activar y Asignar Tarifa
if (isset($_POST['activar_id'])) {
    $stmt = $conn->prepare("UPDATE usuarios_clientes SET estado = 'activo', tarifa_asignada = ? WHERE id = ?");
    $stmt->execute([$_POST['tarifa'], $_POST['activar_id']]);
    header("Location: admin_valida_clientes.php");
    exit();
}

$pendientes = $conn->query("SELECT * FROM usuarios_clientes WHERE estado = 'pendiente'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Validar Clientes - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .comment-box { background: #f0f7ff; padding: 10px; border-radius: 5px; font-size: 0.9em; color: #333; margin: 10px 0; border-left: 4px solid #007aff; }
        select { padding: 8px; border-radius: 5px; }
        .btn-ok { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Altas de Clientes</h1>
        <?php foreach($pendientes as $c): ?>
            <div class="admin-card">
                <h3><?= $c['nombre'] ?> (<?= $c['empresa'] ?>)</h3>
                <p>Email: <?= $c['email'] ?> | Tel: <?= $c['telefono'] ?></p>
                <div class="comment-box">
                    <strong>Comentario / Grupo:</strong><br>
                    <?= nl2br(htmlspecialchars($c['comentario'])) ?>
                </div>
                <form method="POST">
                    <input type="hidden" name="activar_id" value="<?= $c['id'] ?>">
                    <label>Asignar Tarifa:</label>
                    <select name="tarifa">
                        <option value="Tarifa_General.json">General (50%)</option>
                        <option value="Tarifa_Bigmat.json">BigMat (50%)</option>
                        <option value="Tarifa_Neopro.json">Neopro (52%)</option>
                        <option value="Tarifa_Ehlis.json">Ehlis (52%)</option>
                        <option value="Tarifa_Cecofersa.json">Cecofersa (52%)</option>
                        <option value="Tarifa_Grandes_Cuentas.json">Grandes Cuentas (Específicos)</option>
                    </select>
                    <button type="submit" class="btn-ok">✅ Aprobar y Activar</button>
                </form>
            </div>
        <?php endforeach; if(!$pendientes) echo "<p>No hay solicitudes pendientes.</p>"; ?>
        <a href="index.php" class="back-link">Volver</a>
    </div>
</body>
</html>