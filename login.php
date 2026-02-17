<?php 
session_start(); 
require_once 'logic/db.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Acceso Clientes - CV Tools</title>
    <link rel="stylesheet" href="style.css?v=1.2">
    <style>
        body.auth-page { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f0f2f5; margin: 0; padding: 20px; }
        .auth-container { background: #fff; width: 100%; max-width: 400px; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; }
        .auth-form input { width: 100% !important; padding: 15px !important; margin: 10px 0 !important; border: 1px solid #ddd !important; border-radius: 12px !important; font-size: 16px !important; box-sizing: border-box; }
        .btn-auth { width: 100% !important; padding: 16px !important; background: #007aff !important; color: white !important; border: none !important; border-radius: 12px !important; font-weight: bold !important; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body class="auth-page">

    <div class="auth-container">
        <header>
            <img src="img/cvtools.png" alt="CV Tools" style="width: 160px; height: auto; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #2c3e50; font-size: 1.6rem;">Acceso Clientes</h2>
            <p style="color: #666; font-size: 0.9rem; margin-top: 5px;">Portal exclusivo para profesionales</p>
        </header>

        <div style="margin: 20px 0;">
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $stmt = $conn->prepare("SELECT * FROM usuarios_clientes WHERE email = ?");
                $stmt->execute([$_POST['email']]);
                $user = $stmt->fetch();

                if ($user && password_verify($_POST['password'], $user['password'])) {
                    if ($user['estado'] == 'activo') {
                        $_SESSION['cliente_id'] = $user['id'];
                        $_SESSION['cliente_nombre'] = $user['nombre'];
                        $_SESSION['cliente_tarifa'] = $user['tarifa_asignada'];
                        
                        // LÓGICA DE REDIRECCIÓN SI ES ADMIN
                        if ($user['es_admin'] == 1) {
                            $_SESSION['admin_pablo'] = true;
                            header("Location: admin_valida_clientes.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit();
                    } else { 
                        echo '<p style="color:#d9534f; background:#fbe9e9; padding:12px; border-radius:10px; font-size:0.85rem; border:1px solid #f5c6cb;">⚠️ Cuenta pendiente de validación.</p>'; 
                    }
                } else { 
                    echo '<p style="color:#d9534f; background:#fbe9e9; padding:12px; border-radius:10px; font-size:0.85rem; border:1px solid #f5c6cb;">❌ Credenciales incorrectas.</p>'; 
                }
            }
            ?>
        </div>

        <form method="POST" class="auth-form">
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="btn-auth">Entrar al Portal</button>
        </form>

        <p style="font-size: 0.95rem; color: #444; margin-top: 25px;">
            ¿Todavía no eres cliente?<br>
            <a href="registro.php" style="color:#007aff; font-weight:bold; text-decoration:none;">Regístrate aquí</a>
        </p>
    </div>

</body>
</html>