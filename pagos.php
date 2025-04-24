<?php
// pagos.php (en la raíz del proyecto)
require_once 'controladores/AuthController.php';
require_once 'controladores/PagoController.php';
require_once 'controladores/AlumnoController.php';
require_once 'controladores/PeriodoController.php';
require_once 'includes/verificar_acceso.php';

// Inicializar controlador de autenticación
$auth = new AuthController();

// Verificar si está autenticado
if (!$auth->estaAutenticado()) {
    header('Location: login.php');
    exit;
}

// Obtener información del usuario
$nombreCompleto = $_SESSION['nombre_completo'];
$tipoRol = $_SESSION['tipo_rol'];

// Inicializar controladores
$pagoController = new PagoController();
$alumnoController = new AlumnoController();
$periodoController = new PeriodoController();

// Obtener alumnos y periodos para formularios
$alumnos = $alumnoController->obtenerTodos();
$periodos = $periodoController->obtenerActivos();

// Procesar formulario si se ha enviado
$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registrar un nuevo pago
    if (isset($_POST['registrar_pago'])) {
        $pago = [
            'id_alumno' => filter_var($_POST['id_alumno'], FILTER_SANITIZE_NUMBER_INT),
            'id_periodo' => filter_var($_POST['id_periodo'], FILTER_SANITIZE_NUMBER_INT),
            'monto' => filter_var($_POST['monto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'concepto' => filter_var($_POST['concepto'], FILTER_SANITIZE_SPECIAL_CHARS),
            'fecha_pago' => date('Y-m-d H:i:s'), // Fecha actual
            'metodo_pago' => filter_var($_POST['metodo_pago'], FILTER_SANITIZE_SPECIAL_CHARS),
            'comprobante' => '',
            'estado_pago' => 'Pagado'
        ];

        if ($pagoController->registrarPago($pago)) {
            $mensaje = 'Pago registrado correctamente';
            $tipo = 'exito';
        } else {
            $mensaje = 'Error al registrar el pago';
            $tipo = 'error';
        }
    }
}

// Buscar pagos
$termino = '';
$pagos = [];

if (isset($_GET['buscar']) && !empty($_GET['termino'])) {
    $termino = filter_var($_GET['termino'], FILTER_SANITIZE_SPECIAL_CHARS);
    $pagos = $pagoController->buscarPagos($termino);
} else {
    // Obtener los últimos pagos
    $pagos = $pagoController->obtenerUltimosPagos(10);
}

// Obtener resumen de pagos para el sidebar
$resumenPagos = $pagoController->obtenerResumenPagos();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos - Sistema de Asistencia NFC</title>
    <link rel="stylesheet" href="build/css/app.css">
    <!-- Fontawesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard">
        <header class="dashboard__header">
            <div class="dashboard__logo">
                <img src="build/img/logo-svg.svg" alt="Logo Sistema">
            </div>

            <nav class="dashboard__nav">
                <a href="#" class="dashboard__enlace">
                    <i class="fas fa-user"></i>
                    <?php echo $nombreCompleto; ?> (<?php echo $tipoRol; ?>)
                </a>
                <a href="logout.php" class="dashboard__enlace">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>
            </nav>
        </header>

        <div class="dashboard__grid">
            <aside class="dashboard__sidebar">
                <ul class="dashboard__menu">
                    <li class="dashboard__menu-item">
                        <a href="dashboard.php" class="dashboard__menu-enlace">
                            <i class="fas fa-home"></i>
                            <span class="dashboard__menu-texto">Dashboard</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="alumnos.php" class="dashboard__menu-enlace">
                            <i class="fas fa-users"></i>
                            <span class="dashboard__menu-texto">Alumnos</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="asistencia.php" class="dashboard__menu-enlace">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="dashboard__menu-texto">Asistencia</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="pagos.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
                            <i class="fas fa-credit-card"></i>
                            <span class="dashboard__menu-texto">Pagos</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="reportes.php" class="dashboard__menu-enlace">
                            <i class="fas fa-chart-bar"></i>
                            <span class="dashboard__menu-texto">Reportes</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <main class="dashboard__contenido">
                <h2 class="dashboard__heading">Gestión de Pagos</h2>

                <?php if (!empty($mensaje)) : ?>
                    <div class="alerta alerta-<?php echo $tipo; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <div class="pagos__buscador">
                    <form method="GET" action="" class="pagos__buscador">
                        <input type="text" class="pagos__busqueda" name="termino" placeholder="Buscar por nombre, apellido o concepto" value="<?php echo $termino; ?>">
                        <button type="submit" name="buscar" class="pagos__boton">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </form>
                </div>

                <div class="pagos__grid">
                    <div>
                        <h3>Historial de Pagos</h3>

                        <?php if (empty($pagos)) : ?>
                            <p>No se encontraron pagos registrados.</p>
                        <?php else : ?>
                            <table class="pagos__tabla">
                                <thead class="pagos__thead">
                                    <tr>
                                        <th class="pagos__th">Alumno</th>
                                        <th class="pagos__th">Periodo</th>
                                        <th class="pagos__th">Monto</th>
                                        <th class="pagos__th">Concepto</th>
                                        <th class="pagos__th">Fecha</th>
                                        <th class="pagos__th">Estado</th>
                                        <th class="pagos__th">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="pagos__tbody">
                                    <?php foreach ($pagos as $pago) : ?>
                                        <tr class="pagos__tr">
                                            <td class="pagos__td"><?php echo $pago['nombre_completo']; ?></td>
                                            <td class="pagos__td"><?php echo $pago['periodo']; ?></td>
                                            <td class="pagos__td">$<?php echo number_format($pago['monto'], 2); ?></td>
                                            <td class="pagos__td"><?php echo $pago['concepto']; ?></td>
                                            <td class="pagos__td"><?php echo date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?></td>
                                            <td class="pagos__td">
                                                <span class="pagos__estado pagos__estado--<?php echo strtolower($pago['estado_pago']); ?>">
                                                    <?php echo $pago['estado_pago']; ?>
                                                </span>
                                            </td>
                                            <td class="pagos__td">
                                                <div class="pagos__acciones">
                                                    <a href="historial_pagos.php?alumno=<?php echo $pago['id_alumno']; ?>" class="pagos__accion pagos__accion--historial">
                                                        <i class="fas fa-history"></i> Historial
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <div class="pagos__resumen">
                        <h3 class="pagos__resumen-titulo">Resumen de Pagos</h3>

                        <p class="pagos__resumen-texto">
                            Total Recaudado (Mes):
                            <span class="pagos__resumen-cantidad">$<?php echo number_format($resumenPagos['total_mes'], 2); ?></span>
                        </p>

                        <p class="pagos__resumen-texto">
                            Pagos Pendientes:
                            <span class="pagos__resumen-cantidad"><?php echo $resumenPagos['pendientes']; ?></span>
                        </p>

                        <p class="pagos__resumen-texto">
                            Alumnos con Bloqueo:
                            <span class="pagos__resumen-cantidad"><?php echo $resumenPagos['bloqueados']; ?></span>
                        </p>

                        <h3 class="pagos__resumen-titulo">Registrar Nuevo Pago</h3>

                        <form class="pagos__formulario" method="POST">
                            <div class="pagos__campo">
                                <label class="pagos__label" for="id_alumno">Alumno:</label>
                                <select class="pagos__select" id="id_alumno" name="id_alumno" required>
                                    <option value="">Seleccionar Alumno</option>
                                    <?php foreach ($alumnos as $alumno) : ?>
                                        <option value="<?php echo $alumno['id_alumno']; ?>">
                                            <?php echo $alumno['nombre'] . ' ' . $alumno['apellidos']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="pagos__campo">
                                <label class="pagos__label" for="id_periodo">Periodo:</label>
                                <select class="pagos__select" id="id_periodo" name="id_periodo" required>
                                    <option value="">Seleccionar Periodo</option>
                                    <?php foreach ($periodos as $periodo) : ?>
                                        <option value="<?php echo $periodo['id_periodo']; ?>">
                                            <?php echo $periodo['mes_año'] . ' (' . date('d/m/Y', strtotime($periodo['fecha_inicio'])) . ' - ' . date('d/m/Y', strtotime($periodo['fecha_fin'])) . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="pagos__campo">
                                <label class="pagos__label" for="monto">Monto:</label>
                                <input class="pagos__input" type="number" id="monto" name="monto" step="0.01" min="0" required>
                            </div>

                            <div class="pagos__campo">
                                <label class="pagos__label" for="concepto">Concepto:</label>
                                <input class="pagos__input" type="text" id="concepto" name="concepto" required>
                            </div>

                            <div class="pagos__campo">
                                <label class="pagos__label" for="metodo_pago">Método de Pago:</label>
                                <select class="pagos__select" id="metodo_pago" name="metodo_pago" required>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                    <option value="Transferencia">Transferencia</option>
                                </select>
                            </div>

                            <button type="submit" name="registrar_pago" class="pagos__submit">
                                <i class="fas fa-save"></i> Registrar Pago
                            </button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="build/js/bundle.min.js"></script>
</body>

</html>