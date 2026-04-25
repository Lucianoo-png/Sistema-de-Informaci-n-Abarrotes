<?php
// =====================================================
// vista/admin/panel.php — Panel Principal
// =====================================================

require_once BASE_PATH . 'helpers/layout.php';
require_once BASE_PATH . 'control/ReporteControlador.php';
require_once BASE_PATH . 'modelo/Producto.php';

$paginaActual = 'panel';
$ctrl   = new ReporteControlador();
$datos  = $ctrl->resumenPanel();
$hoy    = fechaEspanol();

abrirLayout('Panel Principal', 'panel');
?>

<div class="page-header">
    <h1>Panel Principal</h1>
    <p><?= $hoy ?></p>
</div>

<!-- Estadísticas del día -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon orange">📈</div>
        <div>
            <div class="stat-label">Ventas del día</div>
            <div class="stat-value"><?= formatMXN($datos['ventas_dia']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">🛒</div>
        <div>
            <div class="stat-label">Transacciones</div>
            <div class="stat-value"><?= $datos['transacciones'] ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow">📦</div>
        <div>
            <div class="stat-label">Compras del día</div>
            <div class="stat-value"><?= formatMXN($datos['compras_dia']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">💵</div>
        <div>
            <div class="stat-label">Balance en caja</div>
            <div class="stat-value"><?= formatMXN($datos['balance']) ?></div>
        </div>
    </div>
</div>

<!-- Alerta stock bajo -->
<?php if ($datos['stock_bajo'] > 0): ?>
<div class="alert-stock">
    ⚠️ <?= $datos['stock_bajo'] ?> producto<?= $datos['stock_bajo'] > 1 ? 's' : '' ?> con stock bajo. Revisa el inventario.
</div>
<?php endif; ?>

<!-- Ventas y Compras recientes -->
<div class="two-col">
    <div class="card">
        <div class="card-title">🕐 Ventas Recientes</div>
        <?php if (empty($datos['ventas_recientes'])): ?>
            <div class="empty-state">Sin ventas hoy</div>
        <?php else: ?>
            <?php foreach (array_slice($datos['ventas_recientes'], 0, 5) as $v): ?>
            <div class="report-row">
                <div>
                    <div style="font-weight:500"><?= htmlspecialchars($v['productos'] ?? 'Venta') ?></div>
                    <div style="font-size:12px;color:#888"><?= ucfirst($v['metodo_pago']) ?></div>
                </div>
                <div class="price"><?= formatMXN($v['total']) ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-title">📦 Compras Recientes</div>
        <?php if (empty($datos['compras_recientes'])): ?>
            <div class="empty-state">Sin compras registradas</div>
        <?php else: ?>
            <?php foreach (array_slice($datos['compras_recientes'], 0, 5) as $c): ?>
            <div class="report-row">
                <div>
                    <div style="font-weight:500"><?= htmlspecialchars($c['proveedor_nombre']) ?></div>
                    <div style="font-size:12px;color:#888"><?= ucfirst($c['tipo']) ?></div>
                </div>
                <div class="price"><?= formatMXN($c['total']) ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php cerrarLayout(); ?>
