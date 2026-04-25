<?php
// =====================================================
// modelo/Venta.php — PostgreSQL
// CAMBIO: RETURNING id en lugar de lastInsertId()
//         codigoprod VARCHAR(15) en venta_detalle
//         ENUM → CHECK constraint
// =====================================================

require_once __DIR__ . '/Conexion.php';

class Venta {
    private PDO $db;

    public function __construct() {
        $this->db = Conexion::obtener();
    }

    // CREATE — registra cabecera + detalle en transacción
    public function registrar(array $cabecera, array $detalle): int|false {
        try {
            $this->db->beginTransaction();

            // Insertar cabecera — PostgreSQL usa RETURNING para obtener el ID
            $stmt = $this->db->prepare(
                "INSERT INTO ventas (fecha, total, metodo_pago, nota)
                 VALUES (:fecha, :total, :metodo, :nota)
                 RETURNING id"
            );
            $stmt->execute([
                ':fecha'  => $cabecera['fecha'],
                ':total'  => $cabecera['total'],
                ':metodo' => $cabecera['metodo_pago'],
                ':nota'   => $cabecera['nota'] ?? null,
            ]);
            $ventaId = (int) $stmt->fetchColumn();

            // Detalle + reducir stock
            $stmtDet = $this->db->prepare(
                "INSERT INTO venta_detalle
                     (venta_id, codigoprod, cantidad, precio_unitario, subtotal)
                 VALUES (:vid, :cod, :cant, :pu, :sub)"
            );
            $stmtStk = $this->db->prepare(
                "UPDATE productos
                    SET stock = stock - :cant
                  WHERE codigoprod = :cod AND stock >= :cant"
            );

            foreach ($detalle as $it) {
                $stmtDet->execute([
                    ':vid'  => $ventaId,
                    ':cod'  => $it['codigoprod'],
                    ':cant' => $it['cantidad'],
                    ':pu'   => $it['precio_unitario'],
                    ':sub'  => $it['subtotal'],
                ]);
                $stmtStk->execute([
                    ':cant' => $it['cantidad'],
                    ':cod'  => $it['codigoprod'],
                ]);
            }

            $this->db->commit();
            return $ventaId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Venta::registrar — ' . $e->getMessage());
            return false;
        }
    }

    // READ — ventas del día con lista de productos
    public function obtenerDelDia(string $fecha): array {
        $stmt = $this->db->prepare(
            "SELECT v.*,
                    STRING_AGG(p.nombre, ', ') AS productos
               FROM ventas v
               LEFT JOIN venta_detalle vd ON v.id = vd.venta_id
               LEFT JOIN productos     p  ON vd.codigoprod = p.codigoprod
              WHERE v.fecha = :fecha
              GROUP BY v.id
              ORDER BY v.created_at DESC"
        );
        $stmt->execute([':fecha' => $fecha]);
        return $stmt->fetchAll();
    }

    // READ — detalle de una venta
    public function obtenerDetalle(int $ventaId): array {
        $stmt = $this->db->prepare(
            "SELECT vd.*, p.nombre, p.codigoprod
               FROM venta_detalle vd
               JOIN productos p ON vd.codigoprod = p.codigoprod
              WHERE vd.venta_id = :vid"
        );
        $stmt->execute([':vid' => $ventaId]);
        return $stmt->fetchAll();
    }

    // READ — totales del día
    public function totalDelDia(string $fecha): array {
        $stmt = $this->db->prepare(
            "SELECT
                 COALESCE(SUM(total), 0)                                           AS total_ventas,
                 COUNT(*)                                                           AS num_transacciones,
                 COALESCE(SUM(CASE WHEN metodo_pago='efectivo'      THEN total END), 0) AS efectivo,
                 COALESCE(SUM(CASE WHEN metodo_pago='transferencia' THEN total END), 0) AS transferencia
               FROM ventas WHERE fecha = :fecha"
        );
        $stmt->execute([':fecha' => $fecha]);
        return $stmt->fetch();
    }

    // READ — productos más vendidos en una fecha
    public function masVendidos(string $fecha, int $limite = 5): array {
        $stmt = $this->db->prepare(
            "SELECT p.nombre,
                    SUM(vd.cantidad) AS total_vendido,
                    SUM(vd.subtotal) AS total_importe
               FROM venta_detalle vd
               JOIN ventas    v ON vd.venta_id   = v.id
               JOIN productos p ON vd.codigoprod = p.codigoprod
              WHERE v.fecha = :fecha
              GROUP BY p.codigoprod, p.nombre
              ORDER BY total_vendido DESC
              LIMIT :lim"
        );
        $stmt->bindValue(':fecha', $fecha);
        $stmt->bindValue(':lim',   $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
