<?php
// vista_reportes_alumno.php
require_once 'controladores/AuthController.php';
require_once 'controladores/ReporteController.php';

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

// Inicializar controlador de reportes
$reporteController = new ReporteController();

// Variables para almacenar resultados
$reportes = [];
$datosReporte = [];
$mensaje = '';
$exito = false;

// Procesar solicitud de ver reporte
if (isset($_GET['ver_reporte']) && !empty($_GET['tipo']) && !empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
    $tipoReporte = $_GET['tipo'];
    $fechaInicio = $_GET['fecha_inicio'];
    $fechaFin = $_GET['fecha_fin'];

    // Generar el reporte (solo para visualización, no guardar en BD)
    $parametros = [
        'fecha_inicio' => $fechaInicio,
        'fecha_fin' => $fechaFin,
        'id_alumno' => $idAlumno // Limitar a solo este alumno
    ];

    $resultado = $reporteController->generarReporteSoloVista($tipoReporte, $parametros);

    if ($resultado['exito']) {
        $exito = true;
        $mensaje = 'Reporte generado correctamente.';
        $datosReporte = $resultado['datos'];
    } else {
        $mensaje = $resultado['mensaje'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Sistema de Asistencia NFC</title>
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
                        <a href="vista_asistencia_alumno.php" class="dashboard__menu-enlace">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="dashboard__menu-texto">Mi Asistencia</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="vista_reportes_alumno.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
                            <i class="fas fa-chart-bar"></i>
                            <span class="dashboard__menu-texto">Reportes</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <main class="dashboard__contenido">
                <h2 class="dashboard__heading">Reportes de Asistencia</h2>

                <?php if (!empty($mensaje)): ?>
                    <div class="alerta <?php echo $exito ? 'alerta-exito' : 'alerta-error'; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard__contenedor">
                    <form method="GET" class="reportes__formulario">
                        <div class="reportes__campo">
                            <label for="tipo" class="reportes__label">Tipo de Reporte:</label>
                            <select id="tipo" name="tipo" class="reportes__select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="Asistencia" <?php echo isset($_GET['tipo']) && $_GET['tipo'] === 'Asistencia' ? 'selected' : ''; ?>>Asistencia</option>
                                <option value="General" <?php echo isset($_GET['tipo']) && $_GET['tipo'] === 'General' ? 'selected' : ''; ?>>General</option>
                            </select>
                        </div>

                        <div class="reportes__campo">
                            <label for="fecha_inicio" class="reportes__label">Fecha Inicio:</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="reportes__input" required value="<?php echo $_GET['fecha_inicio'] ?? ''; ?>">
                        </div>

                        <div class="reportes__campo">
                            <label for="fecha_fin" class="reportes__label">Fecha Fin:</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="reportes__input" required value="<?php echo $_GET['fecha_fin'] ?? ''; ?>">
                        </div>

                        <div class="reportes__acciones">
                            <button type="submit" name="ver_reporte" value="1" class="reportes__submit">
                                <i class="fas fa-search"></i> Ver Reporte
                            </button>
                        </div>
                    </form>

                    <?php if ($exito && !empty($datosReporte)): ?>
                        <div class="reportes__vista-previa">
                            <h3>Resultados del Reporte</h3>

                            <?php if (isset($_GET['tipo']) && $_GET['tipo'] === 'Asistencia'): ?>
                                <!-- Reporte de Asistencia -->
                                <table class="reportes__tabla">
                                    <thead class="reportes__thead">
                                        <tr>
                                            <th class="reportes__th">Fecha</th>
                                            <th class="reportes__th">Entrada</th>
                                            <th class="reportes__th">Salida</th>
                                            <th class="reportes__th">Tipo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="reportes__tbody">
                                        <?php foreach ($datosReporte as $registro): ?>
                                            <tr class="reportes__tr">
                                                <td class="reportes__td"><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></td>
                                                <td class="reportes__td"><?php echo $registro['hora_entrada'] ? date('H:i', strtotime($registro['hora_entrada'])) : '-'; ?></td>
                                                <td class="reportes__td"><?php echo $registro['hora_salida'] ? date('H:i', strtotime($registro['hora_salida'])) : '-'; ?></td>
                                                <td class="reportes__td">
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

                                <!-- Gráfica de asistencia -->
                                <div class="reportes__grafica">
                                    <canvas id="grafica-asistencia"></canvas>
                                </div>

                                <script>
                                    // Preparar datos para la gráfica
                                    const datosAsistencia = {
                                        labels: ['Presente', 'Retardo', 'Ausente'],
                                        datasets: [{
                                            label: 'Asistencias',
                                            data: [
                                                <?php
                                                // Contar los diferentes tipos de asistencia
                                                $presentes = 0;
                                                $retardos = 0;
                                                $ausentes = 0;

                                                foreach ($datosReporte as $registro) {
                                                    if ($registro['tipo_asistencia'] === 'Presente') {
                                                        $presentes++;
                                                    } elseif ($registro['tipo_asistencia'] === 'Retardo') {
                                                        $retardos++;
                                                    } elseif ($registro['tipo_asistencia'] === 'Ausente') {
                                                        $ausentes++;
                                                    }
                                                }

                                                echo "$presentes, $retardos, $ausentes";
                                                ?>
                                            ],
                                            backgroundColor: [
                                                '#5CB85C', // Verde para presentes
                                                '#F0AD4E', // Amarillo para retardos
                                                '#D9534F' // Rojo para ausentes
                                            ],
                                            borderWidth: 1
                                        }]
                                    };

                                    // Crear gráfica
                                    const ctx = document.getElementById('grafica-asistencia').getContext('2d');
                                    const graficaAsistencia = new Chart(ctx, {
                                        type: 'pie',
                                        data: datosAsistencia,
                                        options: {
                                            responsive: true,
                                            plugins: {
                                                legend: {
                                                    position: 'top',
                                                },
                                                title: {
                                                    display: true,
                                                    text: 'Distribución de Asistencias'
                                                }
                                            }
                                        },
                                    });
                                </script>

                            <?php elseif (isset($_GET['tipo']) && $_GET['tipo'] === 'General'): ?>
                                <!-- Reporte General -->
                                <div class="reportes__resumen">
                                    <h3>Resumen General</h3>

                                    <div class="reportes__estadisticas">
                                        <div class="reportes__estadistica">
                                            <h4>Asistencia</h4>
                                            <p>Total de Registros: <strong><?php echo $datosReporte['asistencia']['total']; ?></strong></p>
                                            <p>Presentes: <strong><?php echo $datosReporte['asistencia']['presentes']; ?></strong></p>
                                            <p>Retardos: <strong><?php echo $datosReporte['asistencia']['retardos']; ?></strong></p>
                                            <p>Ausentes: <strong><?php echo $datosReporte['asistencia']['ausentes']; ?></strong></p>
                                        </div>

                                        <div class="reportes__estadistica">
                                            <h4>Porcentajes</h4>
                                            <?php
                                            $totalAsistencias = $datosReporte['asistencia']['total'];
                                            $porcentajePresentes = $totalAsistencias > 0 ? round(($datosReporte['asistencia']['presentes'] / $totalAsistencias) * 100) : 0;
                                            $porcentajeRetardos = $totalAsistencias > 0 ? round(($datosReporte['asistencia']['retardos'] / $totalAsistencias) * 100) : 0;
                                            $porcentajeAusentes = $totalAsistencias > 0 ? round(($datosReporte['asistencia']['ausentes'] / $totalAsistencias) * 100) : 0;
                                            ?>
                                            <div class="estadistica">
                                                <span class="estadistica__etiqueta">Presentes:</span>
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

                                    <!-- Gráfica general -->
                                    <div class="reportes__grafica">
                                        <canvas id="grafica-general"></canvas>
                                    </div>

                                    <script>
                                        // Preparar datos para la gráfica general
                                        const datosGeneral = {
                                            labels: ['Presentes', 'Retardos', 'Ausentes'],
                                            datasets: [{
                                                data: [
                                                    <?php echo $datosReporte['asistencia']['presentes']; ?>,
                                                    <?php echo $datosReporte['asistencia']['retardos']; ?>,
                                                    <?php echo $datosReporte['asistencia']['ausentes']; ?>
                                                ],
                                                backgroundColor: [
                                                    '#5CB85C', // Verde para presentes
                                                    '#F0AD4E', // Amarillo para retardos
                                                    '#D9534F' // Rojo para ausentes
                                                ],
                                                borderWidth: 1
                                            }]
                                        };

                                        // Crear gráfica general
                                        const ctxGeneral = document.getElementById('grafica-general').getContext('2d');
                                        const graficaGeneral = new Chart(ctxGeneral, {
                                            type: 'doughnut',
                                            data: datosGeneral,
                                            options: {
                                                responsive: true,
                                                plugins: {
                                                    legend: {
                                                        position: 'top',
                                                    },
                                                    title: {
                                                        display: true,
                                                        text: 'Distribución de Asistencias'
                                                    }
                                                }
                                            },
                                        });
                                    </script>

                                    <!-- Tabla de registro de asistencias recientes -->
                                    <h4 class="mt-4">Registro de Asistencias Recientes</h4>
                                    <?php if (!empty($datosReporte['ultimas_asistencias'])): ?>
                                        <table class="reportes__tabla">
                                            <thead class="reportes__thead">
                                                <tr>
                                                    <th class="reportes__th">Fecha</th>
                                                    <th class="reportes__th">Entrada</th>
                                                    <th class="reportes__th">Salida</th>
                                                    <th class="reportes__th">Tipo</th>
                                                </tr>
                                            </thead>
                                            <tbody class="reportes__tbody">
                                                <?php foreach ($datosReporte['ultimas_asistencias'] as $registro): ?>
                                                    <tr class="reportes__tr">
                                                        <td class="reportes__td"><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></td>
                                                        <td class="reportes__td"><?php echo $registro['hora_entrada'] ? date('H:i', strtotime($registro['hora_entrada'])) : '-'; ?></td>
                                                        <td class="reportes__td"><?php echo $registro['hora_salida'] ? date('H:i', strtotime($registro['hora_salida'])) : '-'; ?></td>
                                                        <td class="reportes__td">
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
                                    <?php else: ?>
                                        <p>No hay registros de asistencia recientes.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="build/js/bundle.min.js"></script>
</body>

</html>