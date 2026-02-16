<?php
session_start();
if(!isset($_SESSION['cliente_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <script>
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

    <!-- Botón Carrito Flotante -->
    <div id="budget-fab" class="budget-fab" onclick="toggleBudgetModal()">🛒 <span id="budget-count">0</span></div>

    <!-- Modal Carrito Principal -->
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
                    <button onclick="clearBudget()" class="btn-danger">🗑️ Borrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Margen Comercial -->
    <div id="margin-modal" class="modal hidden">
        <div class="margin-modal-content">
            <h3>Margen Comercial</h3>
            <p>Introduce el margen (%) para tu cliente.</p>
            <input type="number" id="margin-input" value="30" style="width:80px; padding:10px;"> %
            <br><br>
            <button onclick="confirmMarginAction()" class="btn-confirm">Generar</button>
            <button onclick="closeMarginModal()">Cerrar</button>
        </div>
    </div>

    <!-- POP-UP PERSONALIZADO DE STOCK (SIN NÚMEROS) -->
    <div id="stock-warning-modal" class="modal hidden">
        <div class="stock-warning-content">
            <div class="warning-icon">⚠️</div>
            <h3>Stock Insuficiente</h3>
            <p id="stock-warning-msg">No disponemos de stock suficiente para cubrir la cantidad solicitada en este momento.</p>
            <div class="contact-info">
                Para consultar disponibilidad o plazos de entrega, contacte con nuestra oficina:
                <br>
                <a href="tel:962920132" class="phone-link">📞 962 920 132</a>
            </div>
            <button onclick="closeStockWarning()" class="btn-close-warning">Entendido</button>
        </div>
    </div>

    <script src="logic/presupuesto.js"></script>
    <script src="logic/script.js"></script>
</body>
</html>