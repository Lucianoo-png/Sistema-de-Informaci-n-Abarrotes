<?php
// =====================================================
// vista/vendedor/ventas.php
// CAMBIO: usa codigoprod VARCHAR como identificador
// =====================================================

require_once BASE_PATH . 'helpers/layout.php';
require_once BASE_PATH . 'modelo/Producto.php';

$paginaActual = 'ventas';
$productos    = (new Producto())->obtenerTodos();

abrirLayout('Nueva Venta', 'ventas');
?>

<div style="display:flex;gap:0;min-height:calc(100vh - 64px);margin:-32px;margin-left:0">
<!-- ── Productos ─────────────────────────────── -->
<div style="flex:1;padding:32px;overflow-y:auto">
    <div class="page-header">
        <h1>Nueva Venta</h1>
        <p>Busca y agrega productos</p>
    </div>

    <div class="searchbar" style="margin-bottom:16px">
        <span class="searchbar-icon">🔍</span>
        <input type="text" placeholder="Buscar por nombre o código..."
               oninput="filtrarProductos(this.value)">
    </div>

    <div class="products-grid" id="grid-productos">
        <?php foreach ($productos as $p): ?>
        <?php $low = (int)$p['stock'] <= (int)$p['stock_minimo']; ?>
        <div class="product-card"
             onclick="<?= $p['stock']>0 ? "Carrito.agregar(".json_encode($p).")" : "mostrarToast('Sin stock','err')" ?>"
             style="<?= $p['stock']<=0 ? 'opacity:.5;cursor:not-allowed' : '' ?>">
            <span class="prod-price"><?= formatMXN($p['precio_venta']) ?></span>
            <div class="prod-name"><?= htmlspecialchars($p['nombre']) ?></div>
            <div class="prod-code"><?= $p['codigoprod'] ?></div>
            <div class="prod-cat"><?= $p['categoria'] ?></div>
            <div class="prod-stock <?= $low?'low':'' ?>"><?= $p['stock'] ?> disponibles</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── Carrito ──────────────────────────────── -->
<div class="cart-panel">
    <div class="cart-title">🛒 Carrito</div>
    <div class="cart-items" id="cart-items">
        <p class="cart-empty">Agrega productos al carrito</p>
    </div>

    <div style="margin-top:auto">
        <div class="cart-total">
            <span>Total</span>
            <span class="cart-total-val" id="cart-total">$0.00</span>
        </div>

        <div class="metodo-pago">
            <label>Metodo de Pago</label>
            <div class="metodo-btns">
                <button class="metodo-btn active" data-metodo="efectivo"
                        onclick="Carrito.setMetodo('efectivo')">Efectivo</button>
                <button class="metodo-btn" data-metodo="transferencia"
                        onclick="Carrito.setMetodo('transferencia')">Transferencia</button>
            </div>
        </div>

        <div class="form-group" style="margin-top:12px">
            <label style="font-size:12px;color:#888">Nota (opcional)</label>
            <textarea id="venta-nota" class="nota-input" placeholder="Comentario..."></textarea>
        </div>

        <button class="btn btn-primary" style="width:100%;margin-top:8px;justify-content:center"
                id="btn-registrar-venta" disabled onclick="Carrito.registrar()">
            Registrar Venta
        </button>
    </div>
</div>
</div>

<script>
function filtrarProductos(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#grid-productos .product-card').forEach(c => {
        c.style.display = c.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

// Sobreescribir Carrito para usar codigoprod
const Carrito = (() => {
    let items = {};
    let metodoPago = 'efectivo';

    function agregar(p) {
        const cod = p.codigoprod;
        if (items[cod]) {
            if (items[cod].cantidad < parseInt(p.stock)) {
                items[cod].cantidad++;
            } else { mostrarToast('No hay más stock disponible','err'); return; }
        } else {
            items[cod] = {
                codigoprod: cod,
                nombre:     p.nombre,
                precio:     parseFloat(p.precio_venta),
                cantidad:   1,
                stock:      parseInt(p.stock),
            };
        }
        renderCarrito();
    }

    function cambiar(cod, delta) {
        if (!items[cod]) return;
        items[cod].cantidad += delta;
        if (items[cod].cantidad <= 0) delete items[cod];
        renderCarrito();
    }

    function total() {
        return Object.values(items).reduce((s,i) => s + i.precio * i.cantidad, 0);
    }

    function renderCarrito() {
        const el  = document.getElementById('cart-items');
        const tot = document.getElementById('cart-total');
        const btn = document.getElementById('btn-registrar-venta');
        if (!el) return;
        if (!Object.keys(items).length) {
            el.innerHTML = '<p class="cart-empty">Agrega productos al carrito</p>';
            tot.textContent = '$0.00'; btn.disabled = true; return;
        }
        el.innerHTML = Object.entries(items).map(([cod,it]) => `
          <div class="cart-item">
            <div>
              <div class="cart-item-name">${it.nombre}</div>
              <div style="font-size:12px;color:#888">$${it.precio.toFixed(2)} c/u</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              <div class="cart-item-qty">
                <button onclick="Carrito.cambiar('${cod}',-1)">−</button>
                <span>${it.cantidad}</span>
                <button onclick="Carrito.cambiar('${cod}',+1)">+</button>
              </div>
              <span style="font-weight:700;min-width:60px;text-align:right">$${(it.precio*it.cantidad).toFixed(2)}</span>
            </div>
          </div>`).join('');
        tot.textContent = '$' + total().toFixed(2);
        btn.disabled = false;
    }

    async function registrar() {
        const btn  = document.getElementById('btn-registrar-venta');
        const nota = document.getElementById('venta-nota')?.value ?? '';
        btn.disabled = true; btn.textContent = 'Procesando...';

        const detalle = Object.values(items).map(i => ({
            codigoprod:      i.codigoprod,
            cantidad:        i.cantidad,
            precio_unitario: i.precio,
            subtotal:        i.precio * i.cantidad,
        }));

        try {
            const res  = await fetch(BASE + 'ventas/registrar', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ detalle, metodo_pago: metodoPago, nota }),
            });
            const data = await res.json();
            if (data.ok) {
                mostrarToast('✅ Venta registrada correctamente');
                items = {}; renderCarrito();
                setTimeout(() => location.reload(), 1200);
            } else {
                mostrarToast(data.mensaje || 'Error al registrar','err');
            }
        } catch(e) { mostrarToast('Error de conexión','err'); }
        finally { btn.disabled=false; btn.textContent='Registrar Venta'; }
    }

    function setMetodo(m) {
        metodoPago = m;
        document.querySelectorAll('.metodo-btn').forEach(b =>
            b.classList.toggle('active', b.dataset.metodo===m));
    }
    return { agregar, cambiar, registrar, setMetodo };
})();
</script>

<?php cerrarLayout(); ?>
