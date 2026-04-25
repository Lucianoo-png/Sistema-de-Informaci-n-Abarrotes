<?php
// =====================================================
// index.php — Router principal Abarrotes Angy
// NUEVO: sesión PHP + rutas login/logout/bitacora
// =====================================================

session_start();

define('BASE_PATH', __DIR__ . '/');
define('BASE_URL',  'http://' . $_SERVER['HTTP_HOST'] . '/AbarrotesAngy/');

// ── Carga modelos y controladores ─────────────────
require_once BASE_PATH . 'modelo/Conexion.php';
require_once BASE_PATH . 'control/ProductoControlador.php';
require_once BASE_PATH . 'control/VentaControlador.php';
require_once BASE_PATH . 'control/CompraControlador.php';
require_once BASE_PATH . 'control/ProveedorControlador.php';
require_once BASE_PATH . 'control/TransferenciaControlador.php';
require_once BASE_PATH . 'control/ReporteControlador.php';
require_once BASE_PATH . 'control/CuentaControlador.php';
require_once BASE_PATH . 'control/BitacoraControlador.php';

// ── Parsear URL ────────────────────────────────────
$url    = isset($_GET['url']) ? trim($_GET['url'], '/') : '';
$partes = array_filter(explode('/', $url));
$partes = array_values($partes);

$seccion = $partes[0] ?? 'panel';
$accion  = $partes[1] ?? 'index';
$param   = $partes[2] ?? '';

// ── Rutas públicas (sin autenticación) ────────────
if ($seccion === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ctrl = new CuentaControlador();
        $ctrl->login();
    } else {
        require_once BASE_PATH . 'vista/login.php';
    }
    exit;
}

if ($seccion === 'logout') {
    $ctrl = new CuentaControlador();
    $ctrl->logout();
    exit;
}

// ── Verificar sesión activa ────────────────────────
if (empty($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}

// ── Rutas protegidas ──────────────────────────────
switch ($seccion) {

    case '':
    case 'panel':
        require_once BASE_PATH . 'vista/admin/panel.php';
        break;

    case 'ventas':
        $ctrl = new VentaControlador();
        if ($accion === 'registrar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->registrar();
        } else {
            require_once BASE_PATH . 'vista/vendedor/ventas.php';
        }
        break;

    case 'compras':
        $ctrl = new CompraControlador();
        if ($accion === 'registrar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->registrar();
        } else {
            require_once BASE_PATH . 'vista/admin/compras.php';
        }
        break;

    case 'inventario':
        $ctrl = new ProductoControlador();
        if ($accion === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->crear();
        } elseif ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->actualizar();
        } elseif ($accion === 'eliminar' && $param) {
            $ctrl->eliminar(urldecode($param));
        } else {
            require_once BASE_PATH . 'vista/admin/inventario.php';
        }
        break;

    case 'proveedores':
        $ctrl = new ProveedorControlador();
        if ($accion === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->crear();
        } elseif ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->actualizar();
        } elseif ($accion === 'eliminar' && $param) {
            $ctrl->eliminar((int)$param);
        } else {
            require_once BASE_PATH . 'vista/admin/proveedores.php';
        }
        break;

    case 'transferencias':
        $ctrl = new TransferenciaControlador();
        if ($accion === 'registrar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->registrar();
        } elseif ($accion === 'eliminar' && $param) {
            $ctrl->eliminar((int)$param);
        } else {
            require_once BASE_PATH . 'vista/admin/transferencias.php';
        }
        break;

    case 'reporte':
        require_once BASE_PATH . 'vista/admin/reporte_diario.php';
        break;

    case 'corte':
        require_once BASE_PATH . 'vista/admin/corte_caja.php';
        break;

    case 'bitacora':
        require_once BASE_PATH . 'vista/admin/bitacora.php';
        break;

    // ── API JSON (AJAX interno) ───────────────────
    case 'api':
        header('Content-Type: application/json');
        switch ($accion) {
            case 'productos':
                $ctrl = new ProductoControlador();
                echo json_encode($ctrl->listarTodos());
                break;
            case 'proveedores':
                $ctrl = new ProveedorControlador();
                echo json_encode($ctrl->listarTodos());
                break;
            case 'panel':
                $ctrl = new ReporteControlador();
                echo json_encode($ctrl->resumenPanel());
                break;
            default:
                echo json_encode(['error' => 'Endpoint no encontrado']);
        }
        exit;

    default:
        http_response_code(404);
        require_once BASE_PATH . 'vista/404.php';
        break;
}
?>
