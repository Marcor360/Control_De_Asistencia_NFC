<?php
require_once 'controladores/AuthController.php';
require_once 'controladores/AsistenciaController.php';

// Inicializar controlador de autenticación
$auth = new AuthController();

// Verificar si está autenticado
if (!$auth->estaAutenticado()) {
    header('Location: login.php');
    exit;
}

// Verificar que sea un alumno
if ($_SESSION['tipo_rol'] !== 'Alumno') {
    header('Location: dashboard.php');
    exit;
}

// Obtener información del usuario
$nombreCompleto = $_SESSION['nombre_completo'];
$tipoRol = $_SESSION['tipo_rol'];
$idUsuario = $_SESSION['id_usuario'];

// Obtener ID del alumno relacionado con este usuario
// (Asumimos que existe una relación entre usuarios tipo 'Alumno' y la tabla 'alumno')
$idAlumno = null; // Esto debería obtenerlo de la base de datos

// Inicializar controlador de asistencia
$asistenciaController = new AsistenciaController();

// Obtener asistencias del alumno
$asistencias = [];
if ($idAlumno) {
    $asistencias = $asistenciaController->obtenerAsistenciasPorAlumno($idAlumno);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Alumno - Sistema de Asistencia NFC</title>
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
                        <a href="vista_alumno.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
                            <i class="fas fa-home"></i>
                            <span class="dashboard__menu-texto">Inicio</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="vista_asistencia_alumno.php" class="dashboard__menu-enlace">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="dashboard__menu-texto">Mi Asistencia</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="vista_reportes_alumno.php" class="dashboard__menu-enlace">
                            <i class="fas fa-chart-bar"></i>
                            <span class="dashboard__menu-texto">Reportes</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <main class="dashboard__contenido">
                <h2 class="dashboard__heading">Bienvenido a tu Portal de Alumno</h2>

                <div class="dashboard__contenedor">
                    <h3>Resumen de Asistencia</h3>

                    <?php if (empty($asistencias)): ?>
                        <p>No hay registros de asistencia disponibles.</p>
                    <?php else: ?>
                        <table class="asistencia__tabla">
                            <thead class="asistencia__thead">
                                <tr>
                                    <th class="asistencia__th">Fecha</th>
                                    <th class="asistencia__th">Entrada</th>
                                    <th class="asistencia__th">Salida</th>
                                    <th class="asistencia__th">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="asistencia__tbody">
                                <?php foreach ($asistencias as $asistencia): ?>
                                    <tr class="asistencia__tr">
                                        <td class="asistencia__td"><?php echo date('d/m/Y', strtotime($asistencia['fecha'])); ?></td>
                                        <td class="asistencia__td">
                                            <?php echo $asistencia['hora_entrada'] ? date('H:i', strtotime($asistencia['hora_entrada'])) : '-'; ?>
                                        </td>
                                        <td class="asistencia__td">
                                            <?php echo $asistencia['hora_salida'] ? date('H:i', strtotime($asistencia['hora_salida'])) : '-'; ?>
                                        </td>
                                        <td class="asistencia__td">
                                            <?php
                                            $clase = '';
                                            switch ($asistencia['tipo_asistencia']) {
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
                                                <?php echo $asistencia['tipo_asistencia']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="build/js/bundle.min.js"></script>
</body>

</html>