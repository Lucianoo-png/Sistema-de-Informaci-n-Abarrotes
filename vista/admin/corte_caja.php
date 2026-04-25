<?php
// =====================================================
// vista/admin/corte_caja.php — Corte de Caja
// =====================================================

require_once BASE_PATH . 'helpers/layout.php';
require_once BASE_PATH . 'control/ReporteControlador.php';

$paginaActual = 'corte';
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$ctrl  = new ReporteControlador();
$datos = $ctrl->corteDeCaja($fecha);

abrirLayout('Corte de Caja', 'corte');
?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px">
    <div class="page-header" style="margin-bottom:0">
        <h1>Corte de Caja</h1>
        <p>Resumen de ingresos y egresos</p>
    </div>
    <input type="date" class="date-input" value="<?= $fecha ?>"
           onchange="location.href='corte?fecha='+this.value">
</div>

<p style="color:#888;margin-bottom:20px"><?= fechaEspanol($fecha) ?></p>

<!-- Ingresos -->
<div class="card report-section ingresos" style="margin-bottom:16px">
    <h3>📈 Ingresos</h3>
    <div class="report-row">
        <div style="display:flex;align-items:center;gap:8px">
            <span>💵</span> Ventas en Efectivo
        </div>
        <span><?= formatMXN($datos['efectivo']) ?></span>
    </div>
    <div class="report-row">
        <div style="display:flex;align-items:center;gap:8px">
            <span>⇄</span> Ventas por Transferencia
        </div>
        <span><?= formatMXN($datos['transferencia']) ?></span>
    </div>
    <div class="report-row total">
        <strong>Total Ingresos</strong>
        <span class="amount-pos"><?= formatMXN($datos['total_ingresos']) ?></span>
    </div>
</div>

<!-- Egresos -->
<div class="card report-section egresos" style="margin-bottom:16px">
    <h3>📉 Egresos</h3>
    <div class="report-row">
        <div style="display:flex;align-items:center;gap:8px">
            <span>📦</span> Pagos a Proveedores
        </div>
        <span class="amount-neg">-<?= formatMXN($datos['total_compras']) ?></span>
    </div>
</div>

<!-- Balance final -->
<div class="balance-card card">
    <div>
        <div class="balance-label">💰 Balance Final</div>
        <div class="balance-sub"><?= $datos['num_ventas'] ?> ventas · <?php
            require_once BASE_PATH . 'modelo/Compra.php';
            $compraM = new Compra();
            $comprasHoy = $compraM->obtenerDelDia($fecha);
            echo count($comprasHoy);
        ?> compras</div>
    </div>
    <div class="balance-val" style="<?= $datos['balance_final'] < 0 ? 'color:var(--danger)' : '' ?>">
        <?= formatMXN($datos['balance_final']) ?>
    </div>
</div>

<?php cerrarLayout(); ?>
