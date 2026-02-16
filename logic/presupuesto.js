// logic/presupuesto.js

const URL_FICHAS_WEB = "https://cvtoolssl.github.io/App_Cliente/fichas.html"; 
const EMAIL_PEDIDOS = "pedidos@cvtools.com"; 

let budget = [];
const budgetModal = document.getElementById('budget-modal');
const marginModal = document.getElementById('margin-modal');
const stockWarningModal = document.getElementById('stock-warning-modal');
const budgetCountSpan = document.getElementById('budget-count');
const budgetItemsContainer = document.getElementById('budget-items-container');

let pendingAction = null; 

// --- LÓGICA DE STOCK OCULTO Y POP-UP ---
function addToBudget(ref, desc, stdPrice, qtyInput, netInfo, minQty, netPriceVal, stockText, realStock) {
    let qty = parseInt(qtyInput) || 1;
    let available = parseInt(realStock) || 0;

    // VALIDACIÓN: 50% de stock (sin mostrar el stock total)
    if (available > 0 && available < 900000) { 
        let limiteMaximo = Math.floor(available / 2);
        if (qty > limiteMaximo) {
            showStockWarning(limiteMaximo); // Mostramos el pop-up chulo
            return;
        }
    } else if (available === 0 && stockText === "Sin stock") {
        alert("⚠️ No hay unidades disponibles actualmente.");
        return;
    }

    const existing = budget.find(i => i.ref === ref);
    if (existing) { existing.qty += qty; } 
    else {
        budget.push({
            ref, desc, stdPrice, qty,
            netInfo, minQty, netPriceVal, stockText: stockText || "Consultar"
        });
    }
    updateBudgetUI();
    animateFab();
}

function showStockWarning(max) {
    document.getElementById('stock-warning-msg').innerHTML = `El stock actual es <strong>${max} unidades</strong>. Solo puede añadir la cantidad señalada`;
    stockWarningModal.classList.remove('hidden');
}

function closeStockWarning() {
    stockWarningModal.classList.add('hidden');
}

// --- RESTO DE FUNCIONES (Sin cambios) ---
function removeFromBudget(index) {
    budget.splice(index, 1);
    updateBudgetUI();
}

function clearBudget() {
    if(confirm('¿Borrar todo el carrito?')) {
        budget = [];
        updateBudgetUI();
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
        html += `
            <div class="budget-item">
                <div class="budget-item-info">
                    <strong>${item.desc}</strong>
                    <br><span style="font-size:0.8em; color:#555">${item.ref} | ${item.stockText}</span>
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
        text += `📦 *${item.desc}*\n   Ref: ${item.ref}\n   Cant: ${item.qty} x ${pvpUnit.toFixed(2)} €\n   Subtotal: ${pvpTotal.toFixed(2)} €\n\n`;
    });
    text += `--------------------------------\n💶 *TOTAL: ${total.toFixed(2)} €*\n(Impuestos no incluidos)\n\n📥 *Fichas Técnicas:*\n${URL_FICHAS_WEB}`;
    return text;
}

function sendClientWhatsApp(margin) {
    const text = generateClientText(margin);
    navigator.clipboard.writeText(text).then(() => alert("✅ Copiado. Pégalo en el WhatsApp de tu cliente."));
}

function sendClientEmail(margin) {
    const body = generateClientText(margin);
    window.location.href = `mailto:?subject=Presupuesto Materiales&body=${encodeURIComponent(body)}`;
}

function sendOrderToCVTools() {
    if (budget.length === 0) return alert("Carrito vacío.");
    let text = `HOLA CVTOOLS, SOLICITO EL SIGUIENTE MATERIAL:\n\n`;
    let total = 0;
    budget.forEach(item => {
        const cost = calculateItemCost(item);
        total += cost.total;
        text += `[${item.ref}] ${item.desc} -> ${item.qty} uds\n`;
    });
    text += `\nTotal Coste (Neto): ${total.toFixed(2)} €\n\nDatos de mi empresa:\n(Escribir aquí nombre)\n`;
    window.location.href = `mailto:${EMAIL_PEDIDOS}?subject=NUEVO PEDIDO WEB&body=${encodeURIComponent(text)}`;
}