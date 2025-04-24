// includes/verificar_acceso.php
<?php
require_once __DIR__ . '/../controladores/AuthController.php';

// Determinar el módulo actual basado en el nombre del archivo
$rutaActual = basename($_SERVER['PHP_SELF'], '.php');

// Mapear nombres de archivo a módulos
$mapeoModulos = [
    'dashboard' => 'dashboard',
    'alumnos' => 'alumnos',
    'asistencia' => 'asistencia',
    'pagos' => 'pagos',
    'reportes' => 'reportes',
    // Agregar más según sea necesario
];

$modulo = $mapeoModulos[$rutaActual] ?? '';

// Inicializar el controlador de autenticación
$auth = new AuthController();

// Verificar autenticación
if (!$auth->estaAutenticado()) {
    header('Location: login.php');
    exit;
}

// Verificar permisos de acceso al módulo
if ($modulo && !$auth->puedeAccederModulo($modulo)) {
    // Redireccionar según el rol
    $rol = $_SESSION['tipo_rol'] ?? '';

    if ($rol == 'Alumno') {
        header('Location: vista_alumno.php');
        exit;
    } else {
        header('Location: acceso_denegado.php');
        exit;
    }
}
