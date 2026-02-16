<?php session_start(); require_once 'logic/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Clientes - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container { max-width: 400px; margin: 80px auto; padding: 25px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align:center; }
        .auth-form input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-auth { width: 100%; padding: 14px; background: #007aff; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="auth-container">
        <img src="img/cvtools.png" alt="Logo" style="width: 140px; margin-bottom: 20px;">
        <h2>Acceso Clientes</h2>
        <?php
        if ($_POST) {
            $stmt = $conn->prepare("SELECT * FROM usuarios_clientes WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            $user = $stmt->fetch();
            if ($user && password_verify($_POST['password'], $user['password'])) {
                if ($user['estado'] == 'activo') {
                    $_SESSION['cliente_id'] = $user['id'];
                    $_SESSION['cliente_nombre'] = $user['nombre'];
                    $_SESSION['cliente_tarifa'] = $user['tarifa_asignada']; // Guardamos su tarifa
                    header("Location: index.php");
                    exit();
                } else { echo '<p style="color:red">Tu cuenta está pendiente de activación.</p>'; }
            } else { echo '<p style="color:red">Credenciales incorrectas.</p>'; }
        }
        ?>
        <form method="POST" class="auth-form">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="btn-auth">Entrar</button>
        </form>
        <p style="margin-top:15px; font-size: 0.9em;">¿No eres cliente? <a href="registro.php">Regístrate</a></p>
    </div>
</body>
</html>