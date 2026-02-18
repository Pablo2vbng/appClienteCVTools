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

    // FORMATEAR EL TEXTO SI ES UN NÚMERO
    if (!isNaN(stockText) && stockText !== "" && stockText !== null && typeof stockText !== 'boolean' && stockText !== "En stock" && !stockText.includes("días")) {
        finalStockText = `❌ SIN STOCK (Plazo aprox. ${stockText} días)`;
    }

    // VALIDACIÓN DE AVISO DE DISPONIBILIDAD
    if (available < 900000) { 
        let limiteMaximo = Math.floor(available / 2);
        if (qty > limiteMaximo || available === 0) {
            // Si el stock es insuficiente o es 0, mostramos el aviso
            if (finalStockText !== "En stock") {
                mostrarAviso = true;
            }
        }
    }

    if (mostrarAviso) { 
        if (stockWarningModal) stockWarningModal.classList.remove('hidden'); 
    }

    const existing = budget.find(i => i.ref === String(ref));
    if (existing) { 
        existing.qty += qty;
    } else {
        budget.push({ 
            ref: String(ref), 
            desc: String(desc), 
            stdPrice: parseFloat(stdPrice), 
            qty: qty, 
            netInfo, 
            minQty, 
            netPriceVal, 
            stockText: finalStockText 
        });
    }
    
    updateBudgetUI();
    saveCartToStorage();
    animateFab();
}

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
        if(budgetModal) budgetModal.classList.add('hidden');
    }
}

function calculateItemCost(item) {
    if (item.minQty > 0 && item.netPriceVal > 0 && item.qty >= item.minQty) {
        return { unit: item.netPriceVal, total: item.netPriceVal * item.qty };
    }
    return { unit: item.stdPrice, total: item.stdPrice * item.qty };
}

function updateBudgetUI() {
    if (budgetCountSpan) budgetCountSpan.textContent = budget.length;
    let subtotal = 0;
    let html = '';
    budget.forEach((item, index) => {
        const cost = calculateItemCost(item);
        subtotal += cost.total;
        const stockStyle = String(item.stockText).includes("SIN STOCK") ? 'color:#d9534f; font-weight:bold;' : 'color:#555;';
        
        html += `
        <div class="budget-item">
            <div class="budget-item-info">
                <strong>${item.desc}</strong><br>
                <span style="font-size:0.85em; ${stockStyle}">${item.ref} | ${item.stockText}</span>
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
function closeStockWarning() { if(stockWarningModal) stockWarningModal.classList.add('hidden'); }

function animateFab() {
    const fab = document.getElementById('budget-fab');
    if(fab) { fab.style.transform = 'scale(1.2)'; setTimeout(() => fab.style.transform = 'scale(1)', 200); }
}

async function confirmMarginAction() {
    const input = document.getElementById('margin-input');
    const margin = parseFloat(input.value) || 0;
    const totalNeto = budget.reduce((acc, item) => acc + calculateItemCost(item).total, 0);

    try {
        await fetch('logic/guardar_datos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo: 'presupuesto', total: totalNeto, items: budget, margen: margin })
        });
    } catch (e) { console.error("Error DB:", e); }

    if (pendingAction === 'whatsapp') {
        const text = generateClientText(margin);
        navigator.clipboard.writeText(text).then(() => {
            alert("✅ Presupuesto guardado y COPIADO.\n\nPégalo ahora en el WhatsApp de tu cliente.");
            closeMarginModal();
        });
    } else {
        const body = generateClientText(margin);
        window.location.href = `mailto:?subject=Presupuesto Materiales&body=${encodeURIComponent(body)}`;
        closeMarginModal();
    }
}

function generateClientText(margin) {
    const now = new Date();
    let text = `*📄 PRESUPUESTO COMERCIAL*\n*📅 Fecha:* ${now.toLocaleDateString()} ${now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}\n------------------------------------------\n\n`;
    let totalPVP = 0;
    budget.forEach(item => {
        const cost = calculateItemCost(item);
        const pvpUnit = cost.unit * (1 + (margin / 100));
        const pvpTotal = pvpUnit * item.qty;
        totalPVP += pvpTotal;
        text += `📦 *${item.desc}*\n   Ref: \`${item.ref}\`\n   Cant: ${item.qty} uds x ${pvpUnit.toFixed(2)} €\n   Disponibilidad: ${item.stockText}\n   *Subtotal: ${pvpTotal.toFixed(2)} €*\n\n`;
    });
    text += `------------------------------------------\n💰 *TOTAL: ${totalPVP.toFixed(2)} €*\n_(Impuestos no incluidos)_\n\n📥 *Fichas Técnicas:*\n${URL_FICHAS_WEB}`;
    return text;
}

async function sendOrderToCVTools() {
    if (budget.length === 0) return alert("Carrito vacío.");
    if (!confirm("¿Deseas enviar este pedido a CV Tools?")) return;

    const clientName = document.querySelector('.main-header p strong')?.innerText || "Cliente Web";
    const totalNeto = budget.reduce((acc, item) => acc + calculateItemCost(item).total, 0);

    try {
        await fetch('logic/guardar_datos.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo: 'pedido', total: totalNeto, items: budget })
        });
    } catch (e) { console.error("Error DB:", e); }

    let text = `╔══════════════════════════════════════════╗\n║       SOLICITUD DE PEDIDO CV TOOLS       ║\n╚══════════════════════════════════════════╝\n\n👤 CLIENTE: ${clientName}\n📅 FECHA:   ${new Date().toLocaleString()}\n────────────────────────────────────────────\n\n`;
    budget.forEach((item, index) => {
        const cost = calculateItemCost(item);
        text += `${index + 1}. [${item.ref}] ${item.desc}\n   CANTIDAD: ${item.qty} uds  |  P. UNIT: ${cost.unit.toFixed(2)}€\n   STOCK:    ${item.stockText}\n   SUBTOTAL: ${cost.total.toFixed(2)}€\n   --------------------------------------\n`;
    });
    text += `\n💰 TOTAL NETO PEDIDO: ${totalNeto.toFixed(2)} €\n\nGenerado desde el Portal Profesional CV Tools.`;
    
    window.location.href = `mailto:${EMAIL_PEDIDOS}?subject=${encodeURIComponent("NUEVO PEDIDO WEB - " + clientName)}&body=${encodeURIComponent(text)}`;

    budget = [];
    localStorage.removeItem('cvtools_cart');
    updateBudgetUI();
    toggleBudgetModal();
}