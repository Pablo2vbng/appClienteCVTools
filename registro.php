<?php require_once 'logic/db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Clientes - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container { max-width: 450px; margin: 30px auto; padding: 25px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .auth-form input, .auth-form textarea { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: inherit; }
        .btn-auth { width: 100%; padding: 14px; background: #007aff; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .msg { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="img/cvtools.png" alt="Logo" style="width: 140px;">
            <h2>Registro de Nuevo Cliente</h2>
            <p style="color:#666; font-size:0.9em;">Acceso exclusivo para profesionales</p>
        </div>

        <?php
        if ($_POST) {
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];
            $empresa = $_POST['empresa'];
            $telefono = $_POST['telefono'];
            $comentario = $_POST['comentario'];
            $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);

            $stmt = $conn->prepare("SELECT id FROM usuarios_clientes WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                echo '<div class="msg" style="background:#f8d7da; color:#721c24;">El email ya está registrado.</div>';
            } else {
                $ins = $conn->prepare("INSERT INTO usuarios_clientes (nombre, email, empresa, telefono, comentario, password) VALUES (?, ?, ?, ?, ?, ?)");
                if ($ins->execute([$nombre, $email, $empresa, $telefono, $comentario, $pass])) {
                    echo '<div class="msg" style="background:#d4edda; color:#155724;">✅ Solicitud enviada. Validaremos tu cuenta en breve.</div>';
                }
            }
        }
        ?>

        <form method="POST" class="auth-form">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Email profesional" required>
            <input type="text" name="empresa" placeholder="Empresa / Razón Social" required>
            <input type="tel" name="telefono" placeholder="Teléfono" required>
            <textarea name="comentario" rows="3" placeholder="¿Perteneces a alguna cooperativa o grupo? Cuéntanos algo sobre tu negocio..."></textarea>
            <input type="password" name="password" placeholder="Crea una contraseña" required>
            <button type="submit" class="btn-auth">Enviar Solicitud de Alta</button>
        </form>
        <p style="text-align:center; margin-top:15px; font-size: 0.9em;">¿Ya tienes cuenta? <a href="login.php">Entra aquí</a></p>
    </div>
</body>
</html>