<?php
session_start();
if(!isset($_SESSION['cliente_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador de Fichas Técnicas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="page-fichas">
    <style>
        /* --- ESTILOS PARA LA PÁGINA DE FICHAS TÉCNICAS --- */

.page-fichas {
    background-color: #f4f7f9; /* Fondo gris muy claro para que resalten las tarjetas */
}

/* Contenedor de resultados en cuadrícula */
#resultsContainer {
    display: grid;
    grid-template-columns: 1fr; /* Una columna por defecto (móvil) */
    gap: 20px;
    margin-top: 20px;
    padding-bottom: 40px;
}

/* Si la pantalla es ancha (Tablet/PC), ponemos 2 columnas */
@media (min-width: 768px) {
    #resultsContainer {
        grid-template-columns: 1fr 1fr;
    }
}

/* Tarjeta de cada Ficha Técnica */
.tech-sheet-card {
    background: #ffffff;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.tech-sheet-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

/* Título del producto */
.tech-sheet-card h2 {
    font-size: 1.1rem;
    color: #2c3e50;
    margin-top: 0;
    margin-bottom: 12px;
    line-height: 1.3;
    border-bottom: 2px solid #f0f4f8;
    padding-bottom: 10px;
}

/* Información técnica interna */
.tech-sheet-info {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 20px;
}

.tech-sheet-info p {
    margin: 5px 0;
}

.tech-sheet-info strong {
    color: #333;
}

/* Botón de Descarga */
.tech-sheet-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background-color: #007aff; /* Azul profesional */
    color: white;
    text-decoration: none;
    padding: 12px;
    border-radius: 10px;
    font-weight: bold;
    transition: background 0.3s;
    text-align: center;
}

.tech-sheet-button:hover {
    background-color: #0056b3;
}

.tech-sheet-button svg {
    flex-shrink: 0;
}

/* Mensaje de estado cuando no hay enlace */
.tech-sheet-status {
    color: #d9534f;
    font-size: 0.85rem;
    font-style: italic;
    text-align: center;
}

/* Estilo para el buscador específico en Fichas */
#search-wrapper input {
    border: 2px solid #eee;
    box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    padding: 15px 20px;
    font-size: 1rem;
    border-radius: 12px;
}

#search-wrapper input:focus {
    border-color: #007aff;
    outline: none;
}
    </style>
    <div class="container">
        <!-- HEADER CORREGIDO -->
        <header class="main-header">
            <div class="logo-container">
                <img src="img/cvtools.png" alt="CV Tools Logo" class="provider-logo">
            </div>
            <h1>Fichas Técnicas</h1>
        </header>

        <main>
            <div id="search-wrapper">
                 <input type="text" id="searchInput" placeholder="Buscar por referencia o descripción...">
            </div>
            <div id="resultsContainer"></div>
        
        </main>
    </div>
    <script src="logic/fichas.js"></script>
</body>
</html>