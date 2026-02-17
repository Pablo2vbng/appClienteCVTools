// logic/presupuesto.js

const URL_FICHAS_WEB = "https://cvtoolssl.github.io/App_Cliente/fichas.html"; 
const EMAIL_PEDIDOS = "pedidos@cvtools.es"; 

let budget = [];
const budgetModal = document.getElementById('budget-modal');
const marginModal = document.getElementById('margin-modal');
const stockWarningModal = document.getElementById('stock-warning-modal');
const budgetCountSpan = document.getElementById('budget-count');
const budgetItemsContainer = document.getElementById('budget-items-container');

let pendingAction = null; 

// Cargar carrito de localStorage al iniciar
document.addEventListener('DOMContentLoaded', () => {
    const savedCart = localStorage.getItem('cvtools_cart');
    if (savedCart) {
        budget = JSON.parse(savedCart);
        updateBudgetUI();
    }
});

function saveCartToStorage() {
    localStorage.setItem('cvtools_cart', JSON.stringify(budget));
}

function addToBudget(ref, desc, stdPrice, qtyInput, netInfo, minQty, netPriceVal, stockText, realStock) {
    let qty = parseInt(qtyInput) || 1;
    let available = parseInt(realStock) || 0;
    let finalStockText = stockText;
    let mostrarAviso = false;

    if (available < 900000) { 
        let limiteMaximo = Math.floor(available / 2);
        if (qty > limiteMaximo || available === 0) {
            mostrarAviso = true;
            finalStockText = "❌ SIN STOCK (Consultar plazo)";
        }
    }

    if (mostrarAviso) { showStockWarning(); }

    const existing = budget.find(i => i.ref === ref);
    if (existing) { 
        existing.qty += qty;
        if (available < 900000 && existing.qty > Math.floor(available / 2)) {
            existing.stockText = "❌ SIN STOCK (Consultar plazo)";
        }
    } else {
        budget.push({
            ref, desc, stdPrice, qty,
            netInfo, minQty, netPriceVal, 
            stockText: finalStockText
        });
    }
    
    updateBudgetUI();
    saveCartToStorage();
    animateFab();
}

function showStockWarning() { if (stockWarningModal) stockWarningModal.classList.remove('hidden'); }
function closeStockWarning() { if (stockWarningModal) stockWarningModal.classList.add('hidden'); }

function removeFromBudget(index) {
    budget.splice(index, 1);
    updateBudgetUI();
    saveCartToStorage();
}

function clearBudget() {
    if(confirm('¿Borrar todo el carrito?')) {
        budget = [];
        updateBudgetUI();
        localStorage.removeItem('cvtools_cart');
        toggleBudgetModal();
    }
}

function calculateItemCost(item) {
    if (item.minQty > 0 && item.netPriceVal > 0 && item.qty >= item.minQty) {
        return { unit: item.netPriceVal, total: item.netPriceVal * item.qty, isNet: true };
    }
    return { unit: item.stdPrice, total: item.stdPrice * item.qty, isNet: false };
}

function updateBudgetUI() {
    if (budgetCountSpan) budgetCountSpan.textContent = budget.length;
    let subtotal = 0;
    let html = '';
    budget.forEach((item, index) => {
        const cost = calculateItemCost(item);
        subtotal += cost.total;
        const stockStyle = item.stockText.includes("SIN STOCK") ? 'color:#d9534f; font-weight:bold;' : 'color:#555;';

        html += `
            <div class="budget-item">
                <div class="budget-item-info">
                    <strong>${item.desc}</strong>
                    <br><span style="font-size:0.85em; ${stockStyle}">${item.ref} | ${item.stockText}</span>
                </div>
                <div style="text-align:right">
                    <div>${item.qty} x ${cost.unit.toFixed(2)}€</div>
                    <strong>${cost.total.toFixed(2)} €</strong>
                </div>
                <button class="remove-btn" onclick="removeFromBudget(${index})">&times;</button>
            </div>`;
    });
    if (budgetItemsContainer) budgetItemsContainer.innerHTML = budget.length ? html : '<p class="empty-msg">Tu carrito está vacío.</p>';
    const totalDisplay = document.getElementById('budget-total');
    if (totalDisplay) totalDisplay.textContent = subtotal.toFixed(2);
}

function toggleBudgetModal() { if(budgetModal) budgetModal.classList.toggle('hidden'); }

function animateFab() {
    const fab = document.getElementById('budget-fab');
    if(fab) { fab.style.transform = 'scale(1.2)'; setTimeout(() => fab.style.transform = 'scale(1)', 200); }
}

function openMarginModal(action) {
    if (budget.length === 0) return alert("El carrito está vacío.");
    pendingAction = action; 
    marginModal.classList.remove('hidden');
}

function closeMarginModal() { marginModal.classList.add('hidden'); }

async function confirmMarginAction() {
    const input = document.getElementById('margin-input');
    let margin = parseFloat(input.value) || 0;
    let totalNeto = budget.reduce((acc, item) => acc + calculateItemCost(item).total, 0);

    try {
        await fetch('logic/guardar_datos.php', {
            method: 'POST',
            body: JSON.stringify({ tipo: 'presupuesto', total: totalNeto, items: budget, margen: margin })
        });
    } catch (e) { console.error("Error guardando presupuesto"); }

    if (pendingAction === 'whatsapp') { sendClientWhatsApp(margin); } 
    else if (pendingAction === 'email') { sendClientEmail(margin); }
    closeMarginModal();
}

// --- WHATSAPP MEJORADO (CON MÁS INFO) ---
function generateClientText(margin) {
    const now = new Date();
    const fecha = now.toLocaleDateString();
    const hora = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    let text = `*📄 PRESUPUESTO COMERCIAL*\n`;
    text += `*📅 Fecha:* ${fecha}  *⏰ Hora:* ${hora}\n`;
    text += `------------------------------------------\n\n`;
    
    let totalPVP = 0;
    budget.forEach(item => {
        const cost = calculateItemCost(item);
        const pvpUnit = cost.unit * (1 + (margin / 100));
        const pvpTotal = pvpUnit * item.qty;
        totalPVP += pvpTotal;
        
        text += `📦 *${item.desc}*\n`;
        text += `   Ref: \`${item.ref}\`\n`;
        text += `   Cant: ${item.qty} uds x ${pvpUnit.toFixed(2)} €\n`;
        
        if (item.stockText.includes("SIN STOCK")) {
            text += `   ⚠️ _${item.stockText}_\n`;
        } else {
            text += `   ✅ _En stock para envío inmediato_\n`;
        }
        
        text += `   *Subtotal: ${pvpTotal.toFixed(2)} €*\n\n`;
    });

    text += `------------------------------------------\n`;
    text += `💰 *TOTAL PRESUPUESTO: ${totalPVP.toFixed(2)} €*\n`;
    text += `_(Impuestos no incluidos)_\n\n`;
    text += `📂 *Fichas Técnicas y Certificados:*\n${URL_FICHAS_WEB}\n\n`;
    text += `_Presupuesto generado vía CV Tools App._`;
    
    return text;
}

function sendClientWhatsApp(margin) {
    const text = generateClientText(margin);
    navigator.clipboard.writeText(text).then(() => {
        alert("✅ Presupuesto guardado y copiado.\n\nAhora pégalo en el WhatsApp de tu cliente.");
    });
}

function sendClientEmail(margin) {
    const body = generateClientText(margin);
    window.location.href = `mailto:?subject=Presupuesto Materiales&body=${encodeURIComponent(body)}`;
}

// --- PEDIDO CV TOOLS MEJORADO (MAQUETACIÓN TIPO FACTURA) ---
async function sendOrderToCVTools() {
    if (budget.length === 0) return alert("Carrito vacío.");
    if (!confirm("¿Deseas enviar este pedido a CV Tools?")) return;

    const now = new Date();
    let totalNeto = 0;
    
    // Cabecera "Cool" para el cuerpo del mail
    let text = `==========================================\n`;
    text += `🚀 SOLICITUD DE PEDIDO - CV TOOLS WEB APP\n`;
    text += `==========================================\n\n`;
    text += `📅 FECHA: ${now.toLocaleDateString()}   ⏰ HORA: ${now.toLocaleTimeString()}\n`;
    text += `------------------------------------------\n\n`;
    text += `DESGLOSE DEL MATERIAL:\n\n`;

    budget.forEach((item, index) => {
        const cost = calculateItemCost(item);
        totalNeto += cost.total;
        
        text += `${index + 1}. [${item.ref}] ${item.desc}\n`;
        text += `   CANTIDAD: ${item.qty} uds\n`;
        text += `   PRECIO UNID: ${cost.unit.toFixed(2)} €\n`;
        text += `   SUBTOTAL: ${cost.total.toFixed(2)} €\n`;
        text += `   ESTADO: ${item.stockText}\n`;
        text += `   --------------------------------------\n`;
    });

    text += `\n💰 TOTAL COSTE PEDIDO (NETO): ${totalNeto.toFixed(2)} €\n`;
    text += `(Sujeto a confirmación de condiciones comerciales)\n\n`;
    text += `------------------------------------------\n`;
    text += `🏢 DATOS DEL CLIENTE SOLICITANTE:\n`;
    text += `   Nombre: ____________________\n`;
    text += `   Empresa: ___________________\n`;
    text += `   Teléfono: __________________\n`;
    text += `------------------------------------------\n\n`;
    text += `Observaciones adicionales:\n\n\n`;
    text += `Generado automáticamente por el Portal de Clientes CV Tools.`;

    // 1. Guardar en Base de Datos (Pedidos)
    try {
        await fetch('logic/guardar_datos.php', {
            method: 'POST',
            body: JSON.stringify({ tipo: 'pedido', total: totalNeto, items: budget })
        });
    } catch (e) { console.error("Error guardando pedido"); }

    // 2. Abrir Mail
    window.location.href = `mailto:${EMAIL_PEDIDOS}?subject=NUEVO PEDIDO WEB - CV TOOLS&body=${encodeURIComponent(text)}`;

    // 3. Limpiar y cerrar
    budget = [];
    localStorage.removeItem('cvtools_cart');
    updateBudgetUI();
    toggleBudgetModal();
}