<?php
session_start();
require_once 'logic/db.php';

if(!isset($_SESSION['cliente_id'])) { header("Location: login.php"); exit(); }

$cliente_id = $_SESSION['cliente_id'];

// Consultar Pedidos enviados a CV Tools
$stmt_ped = $conn->prepare("SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY fecha DESC");
$stmt_ped->execute([$cliente_id]);
$pedidos = $stmt_ped->fetchAll();

// Consultar Presupuestos generados (WhatsApp/Email)
$stmt_pre = $conn->prepare("SELECT * FROM presupuestos WHERE cliente_id = ? ORDER BY fecha DESC");
$stmt_pre->execute([$cliente_id]);
$presupuestos = $stmt_pre->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Historial - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { flex: 1; padding: 12px; border: none; background: #eee; border-radius: 10px; cursor: pointer; font-weight: bold; font-family: inherit; }
        .tab-btn.active { background: #007aff; color: white; }
        .history-section { display: none; }
        .history-section.active { display: block; }
        
        .history-card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 10px; }
        .item-row { font-size: 0.85rem; color: #555; margin-bottom: 4px; display: flex; justify-content: space-between; }
        .btn-repeat { background: #e7f3ff; color: #007aff; border: none; padding: 10px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 15px; }
        .tag-order { background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; }
        .tag-budget { background: #e8f5e9; color: #2e7d32; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; }
    </style>
</head>
<body style="background:#f4f7f9;">
    <div class="container">
        <header class="main-header">
            <img src="img/cvtools.png" alt="Logo" class="provider-logo">
            <h1>Mi Historial</h1>
            <p style="color:#666;">Gestiona tus compras y presupuestos</p>
        </header>

        <main>
            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('sec-pedidos', this)">📦 Pedidos</button>
                <button class="tab-btn" onclick="showTab('sec-presupuestos', this)">📑 Presupuestos</button>
            </div>

            <!-- SECCIÓN PEDIDOS -->
            <section id="sec-pedidos" class="history-section active">
                <?php if (!$pedidos): ?>
                    <p style="text-align:center; padding:30px; color:#999;">No has realizado pedidos aún.</p>
                <?php else: ?>
                    <?php foreach ($pedidos as $p): 
                        $items = json_decode($p['detalle'], true);
                    ?>
                        <div class="history-card">
                            <div class="card-header">
                                <div>
                                    <span class="tag-order">PEDIDO CV TOOLS</span>
                                    <span style="display:block; font-size:0.75rem; color:#999; margin-top:5px;"><?= date("d/m/Y H:i", strtotime($p['fecha'])) ?></span>
                                </div>
                                <b style="color:#007aff;"><?= number_format($p['total'], 2) ?> €</b>
                            </div>
                            <?php foreach ($items as $i): ?>
                                <div class="item-row">
                                    <span><?= $i['qty'] ?>x <?= htmlspecialchars($i['desc']) ?></span>
                                    <span style="color:#bbb; font-size:0.7rem;"><?= $i['ref'] ?></span>
                                </div>
                            <?php endforeach; ?>
                            <button class="btn-repeat" onclick='reloadCart(<?= json_encode($items) ?>)'>🔄 Repetir este pedido</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <!-- SECCIÓN PRESUPUESTOS -->
            <section id="sec-presupuestos" class="history-section">
                <?php if (!$presupuestos): ?>
                    <p style="text-align:center; padding:30px; color:#999;">No has generado presupuestos aún.</p>
                <?php else: ?>
                    <?php foreach ($presupuestos as $pr): 
                        $items = json_decode($pr['detalle'], true);
                    ?>
                        <div class="history-card">
                            <div class="card-header">
                                <div>
                                    <span class="tag-budget">PRESUPUESTO WHATSAPP</span>
                                    <span style="display:block; font-size:0.75rem; color:#999; margin-top:5px;"><?= date("d/m/Y H:i", strtotime($pr['fecha'])) ?></span>
                                </div>
                                <b style="color:#2e7d32;"><?= number_format($pr['total'], 2) ?> € (Coste)</b>
                            </div>
                            <?php foreach ($items as $i): ?>
                                <div class="item-row">
                                    <span><?= $i['qty'] ?>x <?= htmlspecialchars($i['desc']) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <button class="btn-repeat" style="color:#2e7d32; background:#e8f5e9;" onclick='reloadCart(<?= json_encode($items) ?>)'>🔄 Recuperar al carrito</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <a href="index.php" class="back-link" style="display:block; text-align:center; margin-top:20px;">← Volver al menú</a>
        </main>
    </div>

    <script>
        function showTab(id, btn) {
            document.querySelectorAll('.history-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            btn.classList.add('active');
        }

        function reloadCart(items) {
            if(confirm('¿Quieres cargar estos productos en tu carrito actual?')) {
                localStorage.setItem('cvtools_cart', JSON.stringify(items));
                window.location.href = 'buscador.php';
            }
        }
    </script>
</body>
</html>