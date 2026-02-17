<?php 
require_once 'logic/db.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registro de Clientes - CV Tools</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

    <div class="auth-container" style="max-width: 450px;">
        <header>
            <img src="img/cvtools.png" alt="CV Tools" class="provider-logo" style="width: 150px; margin-bottom: 20px;">
            <h2 style="margin-top:0;">Alta de Cliente</h2>
            <p style="color:#666; font-size:0.9em;">Solicita tu acceso exclusivo para profesionales</p>
        </header>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $empresa = trim($_POST['empresa']);
            $telefono = trim($_POST['telefono']);
            $comentario = trim($_POST['comentario']);
            $password_plain = $_POST['password'];

            // 1. Verificar si el email ya existe
            $stmt = $conn->prepare("SELECT id FROM usuarios_clientes WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                echo '<div style="background:var(--pastel-red); color:var(--pastel-red-text); padding:12px; border-radius:10px; margin-bottom:15px; font-size:0.9em; border:1px solid #f5c6cb;">
                        ❌ Este correo electrónico ya está registrado.
                      </div>';
            } else {
                // 2. Encriptar contraseña e insertar
                $password_hashed = password_hash($password_plain, PASSWORD_BCRYPT);
                
                $ins = $conn->prepare("INSERT INTO usuarios_clientes (nombre, email, empresa, telefono, comentario, password, estado) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
                
                if ($ins->execute([$nombre, $email, $empresa, $telefono, $comentario, $password_hashed])) {
                    echo '<div style="background:var(--pastel-green); color:var(--pastel-green-text); padding:15px; border-radius:10px; margin-bottom:15px; font-size:0.95em; border:1px solid #c3e6cb;">
                            <strong>✅ ¡Solicitud Enviada!</strong><br>
                            Hemos recibido tus datos. Un administrador validará tu cuenta y te asignará la tarifa correspondiente en breve.
                          </div>';
                } else {
                    echo '<div style="background:var(--pastel-red); color:var(--pastel-red-text); padding:12px; border-radius:10px; margin-bottom:15px; font-size:0.9em;">
                            ❌ Error al procesar el registro. Inténtalo de nuevo.
                          </div>';
                }
            }
        }
        ?>

        <form method="POST" class="auth-form">
            <input type="text" name="nombre" placeholder="Nombre y Apellidos" required autocomplete="name">
            
            <input type="email" name="email" placeholder="Correo electrónico profesional" required autocomplete="email">
            
            <input type="text" name="empresa" placeholder="Empresa / Razón Social" required>
            
            <input type="tel" name="telefono" placeholder="Teléfono de contacto" required autocomplete="tel">
            
            <textarea name="comentario" rows="3" placeholder="¿Perteneces a alguna cooperativa o grupo? Cuéntanos brevemente sobre tu negocio..." style="font-family:inherit;"></textarea>
            
            <input type="password" name="password" placeholder="Crea una contraseña segura" required autocomplete="new-password">
            
            <button type="submit" class="btn-auth">Enviar Solicitud de Alta</button>
        </form>

        <p style="margin-top:25px; font-size: 0.9rem; color:#888;">
            ¿Ya tienes una cuenta validada? <br>
            <a href="login.php" style="color:var(--primary); font-weight:bold;">Inicia sesión aquí</a>
        </p>
        
        <p style="margin-top:15px;">
            <a href="index.php" style="text-decoration:none; color:#bbb; font-size:0.8rem;">← Volver al portal</a>
        </p>
    </div>

</body>
</html>