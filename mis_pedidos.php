<?php
session_start();
require_once 'logic/db.php';
if(!isset($_SESSION['cliente_id'])) { header("Location: login.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY fecha DESC");
$stmt->execute([$_SESSION['cliente_id']]);
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - CV Tools</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .order-card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .order-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
        .order-item { font-size: 0.9rem; color: #555; margin-bottom: 5px; display: flex; justify-content: space-between; }
        .btn-repeat { background: #e7f3ff; color: #007aff; border: none; padding: 8px 15px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; }
        .btn-repeat:hover { background: #007aff; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <img src="img/cvtools.png" alt="Logo" class="provider-logo">
            <h1>Historial de Pedidos</h1>
        </header>

        <main>
            <?php if (!$pedidos): ?>
                <p style="text-align:center; padding:40px; color:#999;">Aún no has realizado pedidos por la web.</p>
            <?php else: ?>
                <?php foreach ($pedidos as $p): 
                    $items = json_decode($p['detalle'], true);
                ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <strong>Pedido #<?= $p['id'] ?></strong><br>
                                <span style="font-size:0.8rem; color:#888;"><?= date("d/m/Y H:i", strtotime($p['fecha'])) ?></span>
                            </div>
                            <div style="text-align:right">
                                <span style="color:var(--primary); font-weight:bold;"><?= number_format($p['total'], 2) ?> €</span>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <?php foreach ($items as $item): ?>
                                <div class="order-item">
                                    <span><?= $item['qty'] ?>x <?= htmlspecialchars($item['desc']) ?></span>
                                    <span style="color:#999;"><?= $item['ref'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button class="btn-repeat" onclick='repeatOrder(<?= $p['detalle'] ?>)'>
                            🔄 Cargar este pedido en el carrito
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <a href="index.php" class="back-link">← Volver al menú</a>
        </main>
    </div>

    <script>
        function repeatOrder(items) {
            if(confirm('Esto sustituirá tu carrito actual por los productos de este pedido. ¿Continuar?')) {
                localStorage.setItem('cvtools_cart', JSON.stringify(items));
                window.location.href = 'buscador.php';
            }
        }
    </script>
</body>
</html>