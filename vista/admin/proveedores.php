<?php
// =====================================================
// vista/admin/proveedores.php — Gestión de Proveedores
// CAMBIO: DiaVisita (antes dias_visita), telefono 10 dígitos con CHECK
// =====================================================

require_once BASE_PATH . 'helpers/layout.php';
require_once BASE_PATH . 'modelo/Proveedor.php';

$paginaActual = 'proveedores';
$proveedores  = (new Proveedor())->obtenerTodos();

abrirLayout('Proveedores', 'proveedores');
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <div class="page-header" style="margin-bottom:0">
        <h1>Proveedores</h1>
        <p>Gestiona tus proveedores</p>
    </div>
    <button class="btn btn-primary" onclick="Proveedores.abrirModal('crear')">+ Nuevo Proveedor</button>
</div>

<?php if (empty($proveedores)): ?>
    <div class="empty-state card">No hay proveedores registrados.</div>
<?php else: ?>
<div class="suppliers-grid">
    <?php foreach ($proveedores as $pv): ?>
    <div class="supplier-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:42px;height:42px;background:#fff3e8;border-radius:10px;
                            display:flex;align-items:center;justify-content:center;font-size:20px">🚚</div>
                <div>
                    <div class="supplier-name"><?= htmlspecialchars($pv['nombre']) ?></div>
                    <div class="supplier-tel">📞 <?= htmlspecialchars($pv['telefono'] ?? '—') ?></div>
                </div>
            </div>
            <div style="display:flex;gap:6px">
                <button class="btn-icon"
                        onclick='Proveedores.abrirModal("editar", <?= json_encode($pv) ?>)'>✏️</button>
                <button class="btn-icon del"
                        onclick="Proveedores.eliminar(<?= $pv['id'] ?>, '<?= addslashes($pv['nombre']) ?>')">🗑️</button>
            </div>
        </div>

        <?php if (!empty($pv['DiaVisita']) || !empty($pv['diavisita'])): ?>
        <div style="margin-top:14px;background:#f9f4ef;border-radius:8px;padding:10px 14px">
            <div class="supplier-days-label">Días de visita</div>
            <div class="supplier-days"><?= htmlspecialchars($pv['DiaVisita'] ?? $pv['diavisita']) ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Modal Proveedor ─────────────────────────────── -->
<div class="modal-overlay" id="modal-proveedor">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modal-prov-titulo">Nuevo Proveedor</span>
            <button class="modal-close" onclick="Proveedores.cerrarModal()">×</button>
        </div>

        <form id="form-proveedor">
            <input type="hidden" name="id" id="prov-id">

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" class="form-control" name="nombre" id="prov-nombre" required>
            </div>

            <div class="form-group">
                <label>Teléfono <small style="color:#888">(exactamente 10 dígitos)</small></label>
                <input type="tel" class="form-control" name="telefono" id="prov-telefono"
                       maxlength="10" pattern="[0-9]{10}"
                       placeholder="Ej: 2281234567">
                <div style="font-size:11px;color:#aaa;margin-top:3px">Solo números, sin guiones ni espacios.</div>
            </div>

            <div class="form-group">
                <label>Días de visita</label>
                <input type="text" class="form-control" name="DiaVisita" id="prov-dias"
                       placeholder="Ej: Lunes y Jueves">
            </div>
        </form>

        <div class="modal-footer">
            <button class="btn btn-outline" onclick="Proveedores.cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="Proveedores.guardar()">Guardar</button>
        </div>
    </div>
</div>

<script>
const Proveedores = (() => {
    function abrirModal(modo, datos = {}) {
        const modal = document.getElementById('modal-proveedor');
        document.getElementById('modal-prov-titulo').textContent =
            modo === 'crear' ? 'Nuevo Proveedor' : 'Editar Proveedor';
        document.getElementById('prov-id').value      = datos.id       ?? '';
        document.getElementById('prov-nombre').value  = datos.nombre   ?? '';
        document.getElementById('prov-telefono').value= datos.telefono ?? '';
        // PostgreSQL devuelve en minúsculas el nombre de columna
        document.getElementById('prov-dias').value    =
            datos.DiaVisita ?? datos.diavisita ?? '';
        modal.classList.add('open');
    }

    function cerrarModal() {
        document.getElementById('modal-proveedor')?.classList.remove('open');
    }

    async function guardar() {
        const tel = document.getElementById('prov-telefono').value.trim();
        if (!/^[0-9]{10}$/.test(tel)) {
            mostrarToast('El teléfono debe tener exactamente 10 dígitos numéricos.', 'err');
            return;
        }

        const id     = document.getElementById('prov-id').value;
        const accion = id ? 'actualizar' : 'crear';
        const form   = document.getElementById('form-proveedor');
        const data   = new FormData(form);

        try {
            const res  = await fetch(BASE + 'proveedores/' + accion, { method: 'POST', body: data });
            const resp = await res.json();
            mostrarToast(resp.mensaje, resp.ok ? 'ok' : 'err');
            if (resp.ok) { cerrarModal(); setTimeout(() => location.reload(), 900); }
        } catch(e) { mostrarToast('Error de conexión', 'err'); }
    }

    async function eliminar(id, nombre) {
        if (!confirm(`¿Eliminar proveedor "${nombre}"?`)) return;
        try {
            const res  = await fetch(BASE + `proveedores/eliminar/${id}`);
            const resp = await res.json();
            mostrarToast(resp.mensaje, resp.ok ? 'ok' : 'err');
            if (resp.ok) setTimeout(() => location.reload(), 900);
        } catch(e) { mostrarToast('Error de conexión', 'err'); }
    }

    return { abrirModal, cerrarModal, guardar, eliminar };
})();
</script>

<?php cerrarLayout(); ?>
