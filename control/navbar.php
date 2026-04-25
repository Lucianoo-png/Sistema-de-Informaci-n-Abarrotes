<?php
// =====================================================
// control/navbar.php — Sidebar Abarrotes Angy
// NUEVO: enlace a Bitácora
// =====================================================

$paginaActual = $paginaActual ?? 'panel';
$base = defined('BASE_URL') ? BASE_URL : './';
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🏪</div>
        <div class="brand-text">
            <strong>Abarrotes Angy</strong>
            <span>Sistema de Información</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= $base ?>panel"
           class="nav-item <?= $paginaActual==='panel'?'active':'' ?>">
            <span class="nav-icon">⊞</span> Panel Principal
        </a>
        <a href="<?= $base ?>ventas"
           class="nav-item <?= $paginaActual==='ventas'?'active':'' ?>">
            <span class="nav-icon">🛒</span> Ventas
        </a>
        <a href="<?= $base ?>compras"
           class="nav-item <?= $paginaActual==='compras'?'active':'' ?>">
            <span class="nav-icon">📦</span> Compras
        </a>
        <a href="<?= $base ?>inventario"
           class="nav-item <?= $paginaActual==='inventario'?'active':'' ?>">
            <span class="nav-icon">🏷️</span> Inventario
        </a>
        <a href="<?= $base ?>proveedores"
           class="nav-item <?= $paginaActual==='proveedores'?'active':'' ?>">
            <span class="nav-icon">🚚</span> Proveedores
        </a>
        <a href="<?= $base ?>transferencias"
           class="nav-item <?= $paginaActual==='transferencias'?'active':'' ?>">
            <span class="nav-icon">⇄</span> Transferencias
        </a>
        <a href="<?= $base ?>reporte"
           class="nav-item <?= $paginaActual==='reporte'?'active':'' ?>">
            <span class="nav-icon">📋</span> Reporte Diario
        </a>
        <a href="<?= $base ?>corte"
           class="nav-item <?= $paginaActual==='corte'?'active':'' ?>">
            <span class="nav-icon">🧾</span> Corte de Caja
        </a>

        <div class="nav-divider"></div>

        <a href="<?= $base ?>bitacora"
           class="nav-item <?= $paginaActual==='bitacora'?'active':'' ?>">
            <span class="nav-icon">📒</span> Bitácora
        </a>
    </nav>

    <div class="sidebar-footer">v1.0 — 2026</div>
</aside>
