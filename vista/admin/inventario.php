<?php
// =====================================================
// vista/admin/inventario.php
// CAMBIO: PK es codigoprod VARCHAR(15), no id INT
// =====================================================

require_once BASE_PATH . 'helpers/layout.php';
require_once BASE_PATH . 'modelo/Producto.php';

$paginaActual = 'inventario';
$modelo       = new Producto();

$soloStockBajo = ($_GET['filtro'] ?? '') === 'stock_bajo';
$buscar        = trim($_GET['buscar'] ?? '');

$productos = $soloStockBajo
    ? $modelo->stockBajo()
    : ($buscar ? $modelo->buscar($buscar) : $modelo->obtenerTodos());

abrirLayout('Inventario', 'inventario');
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
    <div class="page-header" style="margin-bottom:0">
        <h1>Inventario</h1>
        <p>Catálogo de productos</p>
    </div>
    <button class="btn btn-primary" onclick="Inventario.abrirModal('crear')">+ Nuevo Producto</button>
</div>

<!-- Filtros -->
<div class="top-bar">
    <div class="searchbar" style="flex:1">
        <span class="searchbar-icon">🔍</span>
        <input type="text" placeholder="Buscar por nombre o código..."
               value="<?= htmlspecialchars($buscar) ?>"
               oninput="filtrarTabla(this.value)">
    </div>
    <a href="?filtro=stock_bajo"
       class="btn btn-outline <?= $soloStockBajo?'active':'' ?>">⚠️ Stock Bajo</a>
    <?php if ($soloStockBajo || $buscar): ?>
    <a href="inventario" class="btn btn-outline">✕ Limpiar</a>
    <?php endif; ?>
</div>

<!-- Tabla -->
<div class="card">
    <div class="table-wrapper">
        <table id="tabla-productos">
            <thead>
                <tr>
                    <th>Código</th><th>Nombre</th><th>Categoría</th>
                    <th>P. Compra</th><th>P. Venta</th><th>Stock</th><th>Unidad</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                <?php $low = (int)$p['stock'] <= (int)$p['stock_minimo']; ?>
                <tr>
                    <td><code style="background:#f5f0eb;padding:2px 6px;border-radius:4px;font-size:12px">
                        <?= htmlspecialchars($p['codigoprod']) ?></code></td>
                    <td style="font-weight:600"><?= htmlspecialchars($p['nombre']) ?></td>
                    <td style="color:#888"><?= $p['categoria'] ?></td>
                    <td><?= formatMXN($p['precio_compra']) ?></td>
                    <td class="price"><?= formatMXN($p['precio_venta']) ?></td>
                    <td class="<?= $low?'stock-low':'stock-ok' ?>"><?= $p['stock'] ?></td>
                    <td style="color:#888;font-size:13px"><?= $p['unidad'] ?></td>
                    <td style="white-space:nowrap">
                        <button class="btn-icon"
                            onclick='Inventario.abrirModal("editar", <?= json_encode($p) ?>)'>✏️</button>
                        <button class="btn-icon del"
                            onclick="Inventario.eliminar('<?= addslashes($p['codigoprod']) ?>', '<?= addslashes($p['nombre']) ?>')">🗑️</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($productos)): ?>
                <tr><td colspan="8" class="empty-state">Sin productos que mostrar</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Producto -->
<div class="modal-overlay" id="modal-producto">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modal-prod-titulo">Nuevo Producto</span>
            <button class="modal-close" onclick="Inventario.cerrarModal()">×</button>
        </div>
        <form id="form-producto">
            <!-- codigoprod es el PK VARCHAR(15) -->
            <input type="hidden" name="codigoprod" id="prod-codigoprod">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>Código <small style="color:#888">(max 15 chars)</small></label>
                    <input type="text" class="form-control" id="prod-codigo-visible"
                           maxlength="15" placeholder="Ej: 001, COC001"
                           oninput="document.getElementById('prod-codigoprod').value=this.value.toUpperCase();this.value=this.value.toUpperCase()">
                </div>
                <div class="form-group">
                    <label>Unidad</label>
                    <select class="form-control" name="unidad" id="prod-unidad">
                        <option>pieza</option><option>kg</option><option>litro</option>
                        <option>bolsa</option><option>caja</option><option>paquete</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Nombre del producto</label>
                <input type="text" class="form-control" name="nombre" id="prod-nombre" required>
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <input type="text" class="form-control" name="categoria" id="prod-categoria">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group">
                    <label>P. Compra</label>
                    <input type="number" class="form-control" name="precio_compra" id="prod-p-compra" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>P. Venta</label>
                    <input type="number" class="form-control" name="precio_venta" id="prod-p-venta" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Stock actual</label>
                    <input type="number" class="form-control" name="stock" id="prod-stock" min="0">
                </div>
                <div class="form-group">
                    <label>Stock mínimo</label>
                    <input type="number" class="form-control" name="stock_minimo" id="prod-stock-min" min="1">
                </div>
            </div>
        </form>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="Inventario.cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="Inventario.guardar()">Guardar</button>
        </div>
    </div>
</div>

<script>
function filtrarTabla(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#tabla-productos tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

// Override Inventario para usar codigoprod
const Inventario = (() => {
    function abrirModal(modo, datos = {}) {
        const modal = document.getElementById('modal-producto');
        document.getElementById('modal-prod-titulo').textContent = modo==='crear' ? 'Nuevo Producto' : 'Editar Producto';
        const cod = datos.codigoprod ?? '';
        document.getElementById('prod-codigoprod').value      = cod;
        document.getElementById('prod-codigo-visible').value  = cod;
        document.getElementById('prod-codigo-visible').readOnly= modo==='editar';
        document.getElementById('prod-nombre').value          = datos.nombre       ?? '';
        document.getElementById('prod-categoria').value       = datos.categoria    ?? '';
        document.getElementById('prod-p-compra').value        = datos.precio_compra?? '';
        document.getElementById('prod-p-venta').value         = datos.precio_venta ?? '';
        document.getElementById('prod-stock').value           = datos.stock        ?? '';
        document.getElementById('prod-stock-min').value       = datos.stock_minimo ?? 3;
        document.getElementById('prod-unidad').value          = datos.unidad       ?? 'pieza';
        modal.classList.add('open');
    }
    function cerrarModal() { document.getElementById('modal-producto')?.classList.remove('open'); }

    async function guardar() {
        const cod    = document.getElementById('prod-codigoprod').value.trim();
        const accion = cod ? 'actualizar' : 'crear';
        const form   = document.getElementById('form-producto');
        const data   = new FormData(form);
        try {
            const res  = await fetch(BASE + 'inventario/' + accion, { method:'POST', body:data });
            const resp = await res.json();
            mostrarToast(resp.mensaje, resp.ok ? 'ok' : 'err');
            if (resp.ok) { cerrarModal(); setTimeout(()=>location.reload(), 900); }
        } catch(e) { mostrarToast('Error de conexión','err'); }
    }

    async function eliminar(codigo, nombre) {
        if (!confirm(`¿Eliminar "${nombre}"? Esta acción no se puede deshacer.`)) return;
        try {
            const res  = await fetch(BASE + 'inventario/eliminar/' + encodeURIComponent(codigo));
            const resp = await res.json();
            mostrarToast(resp.mensaje, resp.ok ? 'ok' : 'err');
            if (resp.ok) setTimeout(()=>location.reload(), 900);
        } catch(e) { mostrarToast('Error de conexión','err'); }
    }
    return { abrirModal, cerrarModal, guardar, eliminar };
})();
</script>

<?php cerrarLayout(); ?>
