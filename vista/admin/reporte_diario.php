<?php
// =====================================================
// vista/admin/reporte_diario.php — Reporte Diario
// =====================================================

require_once BASE_PATH . 'helpers/layout.php';
require_once BASE_PATH . 'control/ReporteControlador.php';

$paginaActual = 'reporte';
$fecha  = $_GET['fecha'] ?? date('Y-m-d');
$ctrl   = new ReporteControlador();
$datos  = $ctrl->reporteDiario($fecha);
$tot    = $datos['totales'];

abrirLayout('Reporte Diario', 'reporte');
?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px">
    <div class="page-header" style="margin-bottom:0">
        <h1>Reporte Diario</h1>
        <p>Resumen de operaciones del dia</p>
    </div>
    <input type="date" class="date-input" value="<?= $fecha ?>"
           onchange="location.href='reporte?fecha='+this.value">
</div>

<!-- Métricas -->
<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card">
        <div class="stat-icon orange">📈</div>
        <div>
            <div class="stat-label">Total Ventas</div>
            <div class="stat-value"><?= formatMXN($tot['total_ventas'] ?? 0) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">🛒</div>
        <div>
            <div class="stat-label">Transacciones</div>
            <div class="stat-value"><?= $tot['num_transacciones'] ?? 0 ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">🛒</div>
        <div>
            <div class="stat-label">Vtas. Efectivo</div>
            <div class="stat-value"><?= formatMXN($tot['efectivo'] ?? 0) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow">📦</div>
        <div>
            <div class="stat-label">Total Compras</div>
            <div class="stat-value"><?= formatMXN($datos['total_compras']) ?></div>
        </div>
    </div>
</div>

<div class="two-col">
    <!-- Desglose de ventas -->
    <div class="card">
        <div class="card-title">Desglose de Ventas</div>
        <div class="report-row">
            <span>Efectivo</span>
            <span><?= formatMXN($tot['efectivo'] ?? 0) ?></span>
        </div>
        <div class="report-row">
            <span>Transferencia</span>
            <span><?= formatMXN($tot['transferencia'] ?? 0) ?></span>
        </div>
        <div class="report-row total">
            <span>Total</span>
            <span class="price"><?= formatMXN($tot['total_ventas'] ?? 0) ?></span>
        </div>
    </div>

    <!-- Productos más vendidos -->
    <div class="card">
        <div class="card-title" style="color:var(--primary)">🏆 Productos Mas Vendidos</div>
        <?php if (empty($datos['mas_vendidos'])): ?>
            <div class="empty-state">Sin ventas en esta fecha</div>
        <?php else: ?>
            <?php foreach ($datos['mas_vendidos'] as $i => $mv): ?>
            <div class="report-row">
                <div>
                    <span style="color:#888;margin-right:8px">#<?= $i+1 ?></span>
                    <span style="font-weight:500"><?= htmlspecialchars($mv['nombre']) ?></span>
                </div>
                <div style="text-align:right">
                    <div style="font-weight:700"><?= $mv['total_vendido'] ?> uds.</div>
                    <div style="font-size:12px;color:#888"><?= formatMXN($mv['total_importe']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php cerrarLayout(); ?>
