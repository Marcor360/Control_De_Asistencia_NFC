// vista_asistencia_alumno.php
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
$idAlumno = null; // Esto debería obtenerlo de la base de datos

// Inicializar controlador de asistencia
$asistenciaController = new AsistenciaController();

// Obtener asistencias del alumno
$asistencias = [];
if ($idAlumno) {
    $asistencias = $asistenciaController->obtenerAsistenciasPorAlumno($idAlumno, 30); // últimos 30 registros
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Asistencia - Sistema de Asistencia NFC</title>
    <link rel="stylesheet" href="build/css/app.css">
    <!-- Fontawesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js para gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a href="vista_alumno.php" class="dashboard__menu-enlace">
                            <i class="fas fa-home"></i>
                            <span class="dashboard__menu-texto">Inicio</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="vista_asistencia_alumno.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
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
                <h2 class="dashboard__heading">Mi Registro de Asistencia</h2>

                <div class="dashboard__contenedor">
                    <div class="dashboard__grid-secundario">
                        <div class="dashboard__grafica-container">
                            <h3 class="dashboard__subtitulo">
                                <i class="fas fa-chart-pie"></i>
                                Resumen de Asistencia
                            </h3>
                            <div class="dashboard__grafica">
                                <canvas id="graficaAsistencia"></canvas>
                            </div>
                        </div>

                        <div class="dashboard__estadisticas">
                            <h3 class="dashboard__subtitulo">
                                <i class="fas fa-percentage"></i>
                                Porcentajes
                            </h3>

                            <?php
                            // Contar tipos de asistencia
                            $total = count($asistencias);
                            $presentes = 0;
                            $retardos = 0;
                            $ausentes = 0;

                            foreach ($asistencias as $asistencia) {
                                if ($asistencia['tipo_asistencia'] === 'Presente') {
                                    $presentes++;
                                } elseif ($asistencia['tipo_asistencia'] === 'Retardo') {
                                    $retardos++;
                                } elseif ($asistencia['tipo_asistencia'] === 'Ausente') {
                                    $ausentes++;
                                }
                            }

                            // Calcular porcentajes
                            $porcentajePresentes = $total > 0 ? round(($presentes / $total) * 100) : 0;
                            $porcentajeRetardos = $total > 0 ? round(($retardos / $total) * 100) : 0;
                            $porcentajeAusentes = $total > 0 ? round(($ausentes / $total) * 100) : 0;
                            ?>

                            <div class="estadistica">
                                <span class="estadistica__etiqueta">Asistencias:</span>
                                <div class="estadistica__barra">
                                    <div class="estadistica__progreso estadistica__progreso--verde" style="width: <?php echo $porcentajePresentes; ?>%"></div>
                                </div>
                                <span class="estadistica__valor"><?php echo $porcentajePresentes; ?>%</span>
                            </div>

                            <div class="estadistica">
                                <span class="estadistica__etiqueta">Retardos:</span>
                                <div class="estadistica__barra">
                                    <div class="estadistica__progreso estadistica__progreso--amarillo" style="width: <?php echo $porcentajeRetardos; ?>%"></div>
                                </div>
                                <span class="estadistica__valor"><?php echo $porcentajeRetardos; ?>%</span>
                            </div>

                            <div class="estadistica">
                                <span class="estadistica__etiqueta">Ausencias:</span>
                                <div class="estadistica__barra">
                                    <div class="estadistica__progreso estadistica__progreso--rojo" style="width: <?php echo $porcentajeAusentes; ?>%"></div>
                                </div>
                                <span class="estadistica__valor"><?php echo $porcentajeAusentes; ?>%</span>
                            </div>
                        </div>
                    </div>

                    <h3>Historial de Asistencia</h3>

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
    <script>
        // Configurar gráfica de asistencia
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('graficaAsistencia').getContext('2d');
            const graficaAsistencia = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Asistencias', 'Retardos', 'Ausencias'],
                    datasets: [{
                        data: [<?php echo "$presentes, $retardos, $ausentes"; ?>],
                        backgroundColor: [
                            '#5CB85C', // Verde para presentes
                            '#F0AD4E', // Amarillo para retardos
                            '#D9534F' // Rojo para ausentes
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>