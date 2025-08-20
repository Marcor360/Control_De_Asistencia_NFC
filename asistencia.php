<?php
// asistencia.php (en la raíz del proyecto)
require_once 'controladores/AuthController.php';
require_once 'controladores/AsistenciaController.php';
require_once 'controladores/TarjetaController.php';
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
$asistenciaController = new AsistenciaController();
$tarjetaController = new TarjetaController();

// Variable para mostrar información del alumno
$alumnoInfo = null;
$mensaje = '';
$tipo = '';

// Procesar el registro de asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['registrar_entrada'])) {
        $codigoNFC = filter_var($_POST['codigo_nfc'], FILTER_SANITIZE_SPECIAL_CHARS);

        // Verificar que el código NFC exista
        $tarjeta = $tarjetaController->obtenerPorCodigo($codigoNFC);

        if ($tarjeta) {
            $resultado = $asistenciaController->registrarEntrada($tarjeta['id_tarjeta']);

            if ($resultado['exito']) {
                $mensaje = $resultado['mensaje'];
                $tipo = 'exito';
                $alumnoInfo = $asistenciaController->obtenerInfoAlumno($tarjeta['id_alumno']);
            } else {
                $mensaje = $resultado['mensaje'];
                $tipo = 'error';
            }
        } else {
            $mensaje = 'Tarjeta NFC no registrada en el sistema';
            $tipo = 'error';
        }
    } elseif (isset($_POST['registrar_salida'])) {
        $codigoNFC = filter_var($_POST['codigo_nfc'], FILTER_SANITIZE_SPECIAL_CHARS);

        // Verificar que el código NFC exista
        $tarjeta = $tarjetaController->obtenerPorCodigo($codigoNFC);

        if ($tarjeta) {
            $resultado = $asistenciaController->registrarSalida($tarjeta['id_tarjeta']);

            if ($resultado['exito']) {
                $mensaje = $resultado['mensaje'];
                $tipo = 'exito';
                $alumnoInfo = $asistenciaController->obtenerInfoAlumno($tarjeta['id_alumno']);
            } else {
                $mensaje = $resultado['mensaje'];
                $tipo = 'error';
            }
        } else {
            $mensaje = 'Tarjeta NFC no registrada en el sistema';
            $tipo = 'error';
        }
    }
}

// Obtener registros de asistencia recientes
$registrosRecientes = $asistenciaController->obtenerRegistrosRecientes(10);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia - Sistema de Asistencia NFC</title>
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
                        <a href="asistencia.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="dashboard__menu-texto">Asistencia</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="pagos.php" class="dashboard__menu-enlace">
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
                <h2 class="dashboard__heading">Control de Asistencia</h2>

                <?php if (!empty($mensaje)) : ?>
                    <div class="alerta alerta-<?php echo $tipo; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard__grid">
                    <div class="dashboard__contenedor">
                        <div class="asistencia__lector">
                            <h3>Lector de Tarjetas NFC</h3>
                            <p class="asistencia__instruccion">Ingrese el código NFC o acerque la tarjeta al lector</p>

                            <!-- Simulación de lector NFC -->
                            <div class="asistencia__nfc">
                                <i class="fas fa-id-card asistencia__nfc-icono"></i>
                            </div>

                            <!-- Estado del lector -->
                            <div class="asistencia__estado" id="estado-lector">
                                Listo para leer tarjeta...
                            </div>

                            <!-- Formulario manual para el código NFC -->
                            <form method="POST" class="asistencia__formulario" style="margin-top: 2rem;">
                                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                                    <input type="text" name="codigo_nfc" class="pagos__busqueda" placeholder="Ingrese código NFC manualmente" required>
                                </div>
                                <div style="display: flex; gap: 1rem;">
                                    <button type="submit" name="registrar_entrada" class="boton-verde">
                                        <i class="fas fa-sign-in-alt"></i> Registrar Entrada
                                    </button>
                                    <button type="submit" name="registrar_salida" class="boton-naranja">
                                        <i class="fas fa-sign-out-alt"></i> Registrar Salida
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Información del alumno (mostrar si se ha registrado asistencia) -->
                        <?php if ($alumnoInfo) : ?>
                            <div class="asistencia__alumno">
                                <h3 class="asistencia__alumno-nombre"><?php echo $alumnoInfo['nombre'] . ' ' . $alumnoInfo['apellidos']; ?></h3>
                                <div class="asistencia__registro">
                                    <span class="asistencia__hora"><?php echo date('H:i'); ?></span>
                                    <span class="asistencia__tipo asistencia__tipo--<?php echo $alumnoInfo['tipo_registro']; ?>">
                                        <?php echo ucfirst($alumnoInfo['tipo_registro']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="dashboard__contenedor">
                        <h3>Registros Recientes</h3>

                        <?php if (empty($registrosRecientes)) : ?>
                            <p>No hay registros de asistencia recientes.</p>
                        <?php else : ?>
                            <table class="asistencia__tabla">
                                <thead class="asistencia__thead">
                                    <tr>
                                        <th class="asistencia__th">Alumno</th>
                                        <th class="asistencia__th">Fecha</th>
                                        <th class="asistencia__th">Entrada</th>
                                        <th class="asistencia__th">Salida</th>
                                        <th class="asistencia__th">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="asistencia__tbody">
                                    <?php foreach ($registrosRecientes as $registro) : ?>
                                        <tr class="asistencia__tr">
                                            <td class="asistencia__td"><?php echo $registro['nombre_completo']; ?></td>
                                            <td class="asistencia__td"><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></td>
                                            <td class="asistencia__td">
                                                <?php echo $registro['hora_entrada'] ? date('H:i', strtotime($registro['hora_entrada'])) : '-'; ?>
                                            </td>
                                            <td class="asistencia__td">
                                                <?php echo $registro['hora_salida'] ? date('H:i', strtotime($registro['hora_salida'])) : '-'; ?>
                                            </td>
                                            <td class="asistencia__td">
                                                <?php
                                                $clase = '';
                                                switch ($registro['tipo_asistencia']) {
                                                    case 'Presente':
                                                        $clase = 'asistencia__tipo--entrada';
                                                        break;
                                                    case 'Retardo':
                                                        $clase = 'asistencia__tipo--retardo';
                                                        break;
                                                    case 'Ausente':
                                                        $clase = 'asistencia__tipo--ausente';
                                                        break;
                                                }
                                                ?>
                                                <span class="asistencia__tipo <?php echo $clase; ?>">
                                                    <?php echo $registro['tipo_asistencia']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="asistencia__paginacion">
                                <a href="asistencia_registros.php" class="asistencia__enlace">
                                    <i class="fas fa-list"></i> Ver todos los registros
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="build/js/bundle.min.js"></script>
</body>

</html>