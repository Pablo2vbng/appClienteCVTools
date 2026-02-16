<?php
session_start();
if(!isset($_SESSION['cliente_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscador - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // ✅ PASAMOS LA TARIFA ASIGNADA DE PHP A JAVASCRIPT
        window.USER_TARIFF = "<?php echo $_SESSION['cliente_tarifa']; ?>";
    </script>
</head>
<body class="page-precios">
    <div class="container">
        <header class="main-header">
            <img src="img/cvtools.png" alt="Logo" class="provider-logo">
            <h1>Buscador y Presupuestos</h1>
            <p>Cliente: <strong><?php echo $_SESSION['cliente_nombre']; ?></strong></p>
        </header>
        <main>
            <div id="search-wrapper">
                 <input type="text" id="searchInput" placeholder="Buscar por referencia...">
            </div>
            <div id="resultsContainer"></div>
            <a href="index.php" class="back-link">← Volver al menú</a>
        </main>
    </div>

    <!-- Botón Carrito -->
    <div id="budget-fab" class="budget-fab" onclick="toggleBudgetModal()">🛒 <span id="budget-count">0</span></div>

    <!-- Modal Carrito -->
    <div id="budget-modal" class="modal hidden">
        <div class="modal-content">
            <span class="close-modal" onclick="toggleBudgetModal()">&times;</span>
            <h2>Mi Carrito</h2>
            <div id="budget-items-container"></div>
            <div class="budget-footer">
                <p>Tu Coste Total: <span id="budget-total">0.00</span> €</p>
                <div class="budget-actions">
                    <button onclick="openMarginModal('whatsapp')" class="btn-whatsapp">📲 WhatsApp Cliente</button>
                    <button onclick="sendOrderToCVTools()" class="btn-order">🏭 Pedir a CV Tools</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Margen -->
    <div id="margin-modal" class="modal hidden">
        <div class="margin-modal-content">
            <h3>Poner Margen a tu Cliente</h3>
            <p>Introduce el margen (%) que quieres sumar a tu coste para el presupuesto.</p>
            <input type="number" id="margin-input" value="30" style="width:80px; padding:10px;"> %
            <br><br>
            <button onclick="confirmMarginAction()" class="btn-confirm">Copiado y Generado</button>
            <button onclick="closeMarginModal()">Cancelar</button>
        </div>
    </div>

    <script src="logic/presupuesto.js"></script>
    <script src="logic/script.js"></script>
</body>
</html>