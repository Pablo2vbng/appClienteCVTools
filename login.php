<?php session_start(); require_once 'logic/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Acceso Clientes - CV Tools</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

    <div class="auth-container">
        <header>
            <img src="img/cvtools.png" alt="CV Tools" class="provider-logo" style="width: 150px; margin-bottom: 20px;">
            <h2 style="margin-top:0;">Acceso Clientes</h2>
            <p style="color:#666; font-size:0.9em;">Portal exclusivo para profesionales</p>
        </header>

        <?php
        if ($_POST) {
            $stmt = $conn->prepare("SELECT * FROM usuarios_clientes WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            $user = $stmt->fetch();
            if ($user && password_verify($_POST['password'], $user['password'])) {
                if ($user['estado'] == 'activo') {
                    $_SESSION['cliente_id'] = $user['id'];
                    $_SESSION['cliente_nombre'] = $user['nombre'];
                    $_SESSION['cliente_tarifa'] = $user['tarifa_asignada'];
                    header("Location: index.php");
                    exit();
                } else { 
                    echo '<p style="color:var(--danger); font-size:0.9em; background:#fee; padding:10px; border-radius:8px;">⚠️ Tu cuenta está pendiente de activación por un administrador.</p>'; 
                }
            } else { 
                echo '<p style="color:var(--danger); font-size:0.9em; background:#fee; padding:10px; border-radius:8px;">❌ Correo o contraseña incorrectos.</p>'; 
            }
        }
        ?>

        <form method="POST" class="auth-form">
            <input type="email" name="email" placeholder="Correo electrónico" required autocomplete="email">
            <input type="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
            <button type="submit" class="btn-auth">Entrar al Portal</button>
        </form>

        <p style="margin-top:20px; font-size: 0.9rem; color:#888;">
            ¿Todavía no eres cliente? <br>
            <a href="registro.php" style="color:var(--primary); font-weight:bold;">Regístrate aquí</a>
        </p>
    </div>

</body>
</html>