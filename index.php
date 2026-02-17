<?php
session_start();
if(!isset($_SESSION['cliente_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Portal Cliente - CV Tools</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <img src="img/cvtools.png" alt="CV Tools" class="provider-logo">
            <h1>Portal del Cliente</h1>
            <p style="color:#666;">Bienvenido, <strong><?php echo $_SESSION['cliente_nombre']; ?></strong></p>
        </header>

        <main class="menu-grid">
            <a href="buscador.php" class="menu-card">
                <h2>🛒 Pedidos y Stock</h2>
                <p>Consulta precios netos, disponibilidad y crea presupuestos para tus clientes.</p>
            </a>

            <a href="fichas.php" class="menu-card">
                <h2>📄 Fichas Técnicas</h2>
                <p>Descarga toda la documentación y certificados de nuestros productos.</p>
            </a>

            <a href="garantias.php" class="menu-card">
                <h2>⚠️ Garantías</h2>
                <p>Gestiona incidencias, devoluciones o reclamaciones de forma rápida.</p>
            </a>

            <div class="menu-card" style="background: #fdfdfd; border: 1px dashed #ddd;">
                <h2 style="color:#333">📥 Descargas</h2>
                <a href="https://drive.google.com/file/d/1hGIb-8DpEWCIAgVW5xJ3a23ugO5PZuOO/view" target="_blank" style="color:var(--primary); display:block; margin: 10px 0; text-decoration:none; font-weight:500;">📖 Catálogo PDF</a>
                <a href="https://docs.google.com/spreadsheets/d/1WmcdS9oIkeHoBFg1eASZi73PimpaYuDj/edit?usp=sharing&ouid=101336923529478372586&rtpof=true&sd=true" target="_blank" style="color:var(--primary); text-decoration:none; font-weight:500;">📊 Tu Tarifa Excel</a>
            </div>
        </main>

        <div style="background: #e7f3ff; padding: 20px; border-radius: 15px; margin-top: 30px; text-align: center; color: #0056b3;">
            <strong>Crecemos Juntos:</strong> Esta App es una herramienta colaborativa. Tu éxito es el nuestro. Ayúdanos a mejorar con tus ideas
        </div>

        <footer class="site-footer">
            <p>CV Tools &copy; <?php echo date("Y"); ?></p>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </footer>
    </div>
</body>
</html>