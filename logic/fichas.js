// logic/script.js

const TARIFF_FILE = window.USER_TARIFF || 'Tarifa_General.json'; 
const searchInput = document.getElementById('searchInput');
const resultsContainer = document.getElementById('resultsContainer');
const PHOTOS_FILE = 'Foto_Articulos.json';

// --- NUEVO: Botón Volver Arriba (Inyectado dinámicamente) ---
const btnToTop = document.createElement('button');
btnToTop.innerHTML = '↑';
btnToTop.id = 'backToTop';
// Estilos integrados para no depender del CSS de fuera
btnToTop.style.cssText = "display:none; position:fixed; bottom:90px; right:20px; z-index:99; background:#007aff; color:white; border:none; width:50px; height:50px; border-radius:50%; font-size:24px; cursor:pointer; box-shadow:0 4px 15px rgba(0,0,0,0.3); transition: 0.3s; opacity: 0.9;";

document.body.appendChild(btnToTop);

window.onscroll = function() {
    if (document.body.scrollTop > 500 || document.documentElement.scrollTop > 500) {
        btnToTop.style.display = "block";
    } else {
        btnToTop.style.display = "none";
    }
};

btnToTop.onclick = function() {
    window.scrollTo({top: 0, behavior: 'smooth'});
};
// -----------------------------------------------------------

let allProducts = [];
let stockMap = new Map();
let photosMap = new Map();

function extractMinQty(text) {
    if (!text || typeof text !== 'string') return 0;
    const match = text.toLowerCase().match(/(\d+)\s*(uds?|unid|pzs?|pza|cjs?)/);
    return match ? parseInt(match[1]) : 0;
}
function extractNetPrice(text) {
    if (!text || typeof text !== 'string') return 0;
    let match = text.match(/(\d+[.,]?\d*)/);
    return match ? parseFloat(match[1].replace(',', '.')) : 0;
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const [stockRes, tariffRes, photosRes] = await Promise.all([
            fetch(`src/Stock.json?v=${Date.now()}`),
            fetch(`src/${TARIFF_FILE}?v=${Date.now()}`),
            fetch(`src/${PHOTOS_FILE}?v=${Date.now()}`)
        ]);

        const stockData = await stockRes.json();
        if(stockData.Stock) stockData.Stock.forEach(i => stockMap.set(String(i.Artículo), i));

        const photosData = await photosRes.json();
        if (Array.isArray(photosData)) {
            photosData.forEach(item => {
                const id = item.url.match(/\/d\/([a-zA-Z0-9_-]+)/);
                if(id) photosMap.set(item.nombre.split('.')[0].toUpperCase(), `https://lh3.googleusercontent.com/d/${id[1]}`);
            });
        }

        const tariffData = await tariffRes.json();
        const sheet = Object.keys(tariffData)[0];
        allProducts = tariffData[sheet];

    } catch (error) {
        console.error(error);
        resultsContainer.innerHTML = '<p style="text-align:center; padding:20px; color:red;">Error cargando datos.</p>';
    }
});

searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase().trim();
    if (query.length < 2) { resultsContainer.innerHTML = ''; return; }
    const filtered = allProducts.filter(p => {
        const d = p.Descripcion ? p.Descripcion.toLowerCase() : '';
        const r = p.Referencia ? String(p.Referencia).toLowerCase() : '';
        return d.includes(query) || r.includes(query);
    });
    displayResults(filtered);
});

function displayResults(products) {
    if (!products.length) { resultsContainer.innerHTML = '<p style="text-align:center; padding:20px;">Sin resultados.</p>'; return; }
    let html = '';
    
    products.forEach((p, idx) => {
        let precioStd = parseFloat(p.PRECIO_GRUPO1 || p.PRECIO_ESTANDAR || p.PRECIO_GRUPO3 || p.PRECIO_CECOFERSA || p.PRECIO || 0);
        let netoRaw = p.CONDICIONES_NETO || p.CONDICION_NETO_GC || '';
        let netVal = extractNetPrice(netoRaw);

        const sInfo = stockMap.get(String(p.Referencia));
        let sHtml = '<div class="stock-badge stock-ko">📞 Consultar</div>';
        let stockDisponibleNum = 0; 
        let stockTextoParaPresupuesto = "Consultar";

        if (sInfo) {
            stockDisponibleNum = parseInt(String(sInfo.Stock).replace(/\D/g, '')) || 0;
            let estadoRaw = String(sInfo.Estado).toLowerCase().trim();

            if (estadoRaw === 'si') {
                sHtml = stockDisponibleNum > 0 
                    ? '<div class="stock-badge stock-ok">✅ En stock</div>' 
                    : '<div class="stock-badge stock-ko">❌ Sin stock</div>';
                stockTextoParaPresupuesto = stockDisponibleNum > 0 ? "En stock" : "Sin stock";
            } else if (estadoRaw === 'fab') {
                sHtml = '<div class="stock-badge stock-fab">🏭 3-5 días</div>';
                stockTextoParaPresupuesto = "3-5 días";
                stockDisponibleNum = 999999;
            } else if (!isNaN(estadoRaw) && estadoRaw !== "") {
                // NUEVA LÓGICA: Si es un número (días)
                sHtml = `<div class="stock-badge stock-ko" style="background:#ffebee; color:#c62828; border:1px solid #ffcdd2;">❌ ${estadoRaw} días</div>`;
                stockTextoParaPresupuesto = estadoRaw; // Pasamos el número bruto al presupuesto
            }
        }

        const imgUrl = photosMap.get(String(p.Referencia).toUpperCase());
        const imgHtml = imgUrl ? `<img src="${imgUrl}" class="product-img">` : '<span>Sin foto</span>';

        html += `
            <div class="product-card-single">
                <div class="card-header">
                    <div class="product-image-container">${imgHtml}</div>
                    <div class="header-text">
                        <h2>${p.Descripcion}</h2>
                        <span class="ref-text">Ref: ${p.Referencia}</span>
                    </div>
                    ${sHtml}
                </div>
                <div class="price-box">
                    <div class="row-price">Tu Coste: <strong>${precioStd.toFixed(2)} €</strong></div>
                    ${netVal > 0 ? `<div class="row-neto">Neto: ${netVal.toFixed(2)} € <small>(${netoRaw})</small></div>` : ''}
                </div>
                <div class="add-controls">
                    <input type="number" id="qty_${idx}" class="qty-input" value="1" min="1">
                    <button class="add-budget-btn" onclick="addToBudget('${p.Referencia}', '${p.Descripcion.replace(/'/g, "")}', ${precioStd}, document.getElementById('qty_${idx}').value, '${netoRaw}', ${extractMinQty(netoRaw)}, ${netVal}, '${stockTextoParaPresupuesto}', ${stockDisponibleNum})">
                        + Añadir
                    </button>
                </div>
            </div>`;
    });
    resultsContainer.innerHTML = html;
}