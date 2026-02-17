<?php
session_start();
if(!isset($_SESSION['cliente_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Cliente - CV Tools</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <img src="img/cvtools.png" alt="CV Tools" class="provider-logo">
            <h1>Portal del Cliente</h1>
            <p>Hola, <strong><?php echo $_SESSION['cliente_nombre']; ?></strong></p>
        </header>

        <main class="menu-grid">
            <a href="buscador.php" class="menu-card">
                <h2 style="color:var(--primary)">🛒 Pedidos y Stock</h2>
                <p>Consulta tus precios netos, stock real y genera presupuestos para tus clientes.</p>
            </a>

            <a href="fichas.php" class="menu-card">
                <h2>📄 Fichas Técnicas</h2>
                <p>Descarga especificaciones y certificados de calidad de cada referencia.</p>
            </a>

            <a href="garantias.php" class="menu-card">
                <h2 style="color:var(--danger)">⚠️ Garantías</h2>
                <p>Gestiona reclamaciones o incidencias de productos defectuosos.</p>
            </a>
            
            <div class="menu-card" style="grid-column: span 1;">
                <h2>📥 Descargas</h2>
                <a href="https://drive.google.com/file/d/1hGIb-8DpEWCIAgVW5xJ3a23ugO5PZuOO/view" target="_blank" style="display:block; margin-bottom:10px; color:var(--primary);">📖 Catálogo General PDF</a>
                <a href="https://docs.google.com/spreadsheets/d/1DhbKMFnJCfqO9ujj9d4Z8eezPCAPth0nZ0qPMMcWLbQ/edit" target="_blank" style="color:var(--primary);">📊 Tu Tarifa Excel</a>
            </div>
        </main>

        <footer class="site-footer" style="text-align:center; padding-top:40px; border-top:1px solid #ddd; margin-top:40px;">
            <p style="color:#666; font-size:0.9em; max-width:600px; margin: 0 auto 20px auto;">
                <strong>🚀 Herramienta Colaborativa:</strong> Esta App está diseñada para crecer contigo. Tu feedback nos ayuda a mejorar.
            </p>
            <p><strong>CV Tools &copy; <?php echo date("Y"); ?></strong></p>
            <a href="logout.php" style="color:var(--danger); text-decoration:none; font-weight:bold;">Cerrar Sesión</a>
        </footer>
    </div>
</body>
</html>