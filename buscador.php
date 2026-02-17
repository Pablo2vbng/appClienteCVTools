<?php
session_start();
if(!isset($_SESSION['cliente_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Buscador - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <script>window.USER_TARIFF = "<?php echo $_SESSION['cliente_tarifa']; ?>";</script>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <img src="img/cvtools.png" alt="Logo" class="provider-logo">
            <h1>Buscador y Pedidos</h1>
            <p style="font-size:0.9em; margin:0; color:#666;">Cliente: <strong><?php echo $_SESSION['cliente_nombre']; ?></strong></p>
        </header>

        <main>
            <div id="search-wrapper">
                 <input type="text" id="searchInput" placeholder="Buscar por referencia..." autocomplete="off">
            </div>
            
            <div id="resultsContainer"></div>
            
            <a href="index.php" class="back-link">← Volver al menú</a>
        </main>
    </div>

    <!-- Carrito Flotante -->
    <div id="budget-fab" class="budget-fab" onclick="toggleBudgetModal()">
        🛒 <span id="budget-count">0</span>
    </div>

    <!-- Modal Carrito -->
    <div id="budget-modal" class="modal hidden">
        <div class="modal-content">
            <span class="close-modal" onclick="toggleBudgetModal()" style="float:right; font-size:1.5rem; cursor:pointer;">&times;</span>
            <h2 style="margin-top:0;">Mi Carrito</h2>
            <div id="budget-items-container"></div>
            <div class="budget-footer" style="padding-top:15px;">
                <p style="font-size:1.1rem; margin-bottom:15px;">Tu Coste Total: <strong><span id="budget-total">0.00</span> €</strong></p>
                <div class="budget-actions">
                    <button onclick="openMarginModal('whatsapp')" class="btn-whatsapp">📲 Enviar Presupuesto WhatsApp</button>
                    <button onclick="sendOrderToCVTools()" class="btn-order">🏭 Enviar Pedido CV Tools</button>
                    <button onclick="clearBudget()" class="btn-danger">🗑️ Vaciar Carrito</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Margen -->
    <div id="margin-modal" class="modal hidden">
        <div class="margin-modal-content">
            <div class="margin-header">
                <h3>Margen Comercial</h3>
                <p>Indica el % de margen para tu cliente final.</p>
            </div>
            <div class="margin-input-container">
                <input type="number" id="margin-input" value="30" min="0">
                <span class="percentage-symbol">%</span>
            </div>
            <div class="margin-actions-grid">
                <button onclick="confirmMarginAction()" class="btn-confirm-margin">Generar y Copiar</button>
                <button onclick="closeMarginModal()" class="btn-close-margin">Volver</button>
            </div>
        </div>
    </div>

    <!-- Pop-up Stock -->
    <div id="stock-warning-modal" class="modal hidden">
        <div class="stock-warning-content" style="text-align:center;">
            <div style="font-size:3rem;">⚠️</div>
            <h3>Disponibilidad Limitada</h3>
            <p>No disponemos de stock suficiente para cubrir esta cantidad en este momento.</p>
            <div style="background:#fdf2e9; padding:15px; border-radius:10px; margin:15px 0;">
                Para consultar plazos de entrega:<br>
                <a href="tel:962920132" style="font-size:1.3rem; font-weight:bold; color:var(--primary); text-decoration:none;">📞 962 920 132</a>
            </div>
            <button onclick="closeStockWarning()" class="btn-close-margin" style="width:100%;">Entendido</button>
        </div>
    </div>

    <script src="logic/presupuesto.js"></script>
    <script src="logic/script.js"></script>
</body>
</html>