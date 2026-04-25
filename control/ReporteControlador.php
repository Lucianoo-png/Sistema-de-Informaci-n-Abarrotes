<?php
// =====================================================
// control/ReporteControlador.php
// =====================================================

require_once BASE_PATH . 'modelo/Venta.php';
require_once BASE_PATH . 'modelo/Compra.php';
require_once BASE_PATH . 'modelo/Producto.php';

class ReporteControlador {
    private Venta    $modeloVenta;
    private Compra   $modeloCompra;
    private Producto $modeloProducto;

    public function __construct() {
        $this->modeloVenta    = new Venta();
        $this->modeloCompra   = new Compra();
        $this->modeloProducto = new Producto();
    }

    public function resumenPanel(string $fecha = ''): array {
        $fecha   = $fecha ?: date('Y-m-d');
        $totV    = $this->modeloVenta->totalDelDia($fecha);
        $totC    = $this->modeloCompra->totalDelDia($fecha);
        $stkBajo = $this->modeloProducto->contarStockBajo();

        return [
            'ventas_dia'       => $totV['total_ventas']      ?? 0,
            'transacciones'    => $totV['num_transacciones']  ?? 0,
            'efectivo_dia'     => $totV['efectivo']           ?? 0,
            'transferencia_dia'=> $totV['transferencia']      ?? 0,
            'compras_dia'      => $totC,
            'balance'          => ($totV['total_ventas'] ?? 0) - $totC,
            'stock_bajo'       => $stkBajo,
            'ventas_recientes' => $this->modeloVenta->obtenerDelDia($fecha),
            'compras_recientes'=> $this->modeloCompra->obtenerDelDia($fecha),
        ];
    }

    public function reporteDiario(string $fecha = ''): array {
        $fecha = $fecha ?: date('Y-m-d');
        return [
            'fecha'        => $fecha,
            'totales'      => $this->modeloVenta->totalDelDia($fecha),
            'total_compras'=> $this->modeloCompra->totalDelDia($fecha),
            'mas_vendidos' => $this->modeloVenta->masVendidos($fecha),
        ];
    }

    public function corteDeCaja(string $fecha = ''): array {
        $fecha   = $fecha ?: date('Y-m-d');
        $totales = $this->modeloVenta->totalDelDia($fecha);
        $totC    = $this->modeloCompra->totalDelDia($fecha);

        return [
            'fecha'         => $fecha,
            'efectivo'      => $totales['efectivo']          ?? 0,
            'transferencia' => $totales['transferencia']      ?? 0,
            'total_ingresos'=> $totales['total_ventas']       ?? 0,
            'total_compras' => $totC,
            'balance_final' => ($totales['total_ventas'] ?? 0) - $totC,
            'num_ventas'    => $totales['num_transacciones']  ?? 0,
        ];
    }
}
?>
