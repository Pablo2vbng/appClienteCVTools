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

function confirmMarginAction() {
    const input = document.getElementById('margin-input');
    let margin = parseFloat(input.value) || 0;
    if (pendingAction === 'whatsapp') { sendClientWhatsApp(margin); } 
    else if (pendingAction === 'email') { sendClientEmail(margin); }
    closeMarginModal();
}

function generateClientText(margin) {
    let text = `📄 *PRESUPUESTO*\n📅 Fecha: ${new Date().toLocaleDateString()}\n--------------------------------\n\n`;
    let total = 0;
    budget.forEach(item => {
        const cost = calculateItemCost(item);
        const pvpUnit = cost.unit * (1 + (margin / 100));
        const pvpTotal = pvpUnit * item.qty;
        total += pvpTotal;
        text += `📦 *${item.desc}*\n   Ref: ${item.ref}\n   Cant: ${item.qty} x ${pvpUnit.toFixed(2)} €\n`;
        if (item.stockText.includes("SIN STOCK")) text += `   ⚠️ _${item.stockText}_\n`;
        text += `   Subtotal: ${pvpTotal.toFixed(2)} €\n\n`;
    });
    text += `--------------------------------\n💶 *TOTAL: ${total.toFixed(2)} €*\n(Impuestos no incluidos)\n\n📥 *Fichas Técnicas:*\n${URL_FICHAS_WEB}`;
    return text;
}

function sendClientWhatsApp(margin) {
    const text = generateClientText(margin);
    navigator.clipboard.writeText(text).then(() => {
        alert("✅ Presupuesto copiado. Pégalo en el chat de tu cliente.");
    });
}

function sendClientEmail(margin) {
    const body = generateClientText(margin);
    window.location.href = `mailto:?subject=Presupuesto Materiales&body=${encodeURIComponent(body)}`;
}

// FUNCIÓN CORREGIDA PARA GUARDAR Y ENVIAR
async function sendOrderToCVTools() {
    if (budget.length === 0) return alert("Carrito vacío.");
    if (!confirm("¿Deseas enviar este pedido a CV Tools?")) return;

    let totalNeto = 0;
    let text = `HOLA CVTOOLS, SOLICITO EL SIGUIENTE MATERIAL:\n\n`;
    
    budget.forEach(item => {
        const cost = calculateItemCost(item);
        totalNeto += cost.total;
        text += `[${item.ref}] ${item.desc} -> ${item.qty} uds`;
        if (item.stockText.includes("SIN STOCK")) text += " (SIN STOCK)";
        text += "\n";
    });

    text += `\nTotal Coste (Neto): ${totalNeto.toFixed(2)} €\n\nDatos de mi empresa:\n(Escribir aquí)\n`;

    // Guardar en Base de Datos (URL relativa a buscador.php)
    try {
        await fetch('logic/guardar_pedido.php', {
            method: 'POST',
            body: JSON.stringify({ total: totalNeto, items: budget })
        });
    } catch (error) { console.error("Error guardado"); }

    // Enviar Email
    window.location.href = `mailto:${EMAIL_PEDIDOS}?subject=NUEVO PEDIDO WEB&body=${encodeURIComponent(text)}`;

    // Limpiar carrito
    budget = [];
    localStorage.removeItem('cvtools_cart');
    updateBudgetUI();
    toggleBudgetModal();
}