<?php
require_once 'includes/verificar_acceso.php';
// reportes.php (en la raíz del proyecto)
// Mostrar todos los errores para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si está autenticado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Incluir el controlador necesario
require_once 'controladores/ReporteController.php';
require_once 'controladores/AlumnoController.php';

// Inicializar controladores
$reporteController = new ReporteController();
$alumnoController = new AlumnoController();

// Obtener información del usuario
$nombreCompleto = $_SESSION['nombre_completo'] ?? 'Usuario';
$tipoRol = $_SESSION['tipo_rol'] ?? 'Usuario';
$idUsuario = $_SESSION['id_usuario'] ?? 0;

// Obtener lista de alumnos para el selector
$alumnos = $alumnoController->obtenerTodos();

// Variables para almacenar los datos del reporte
$datosReporte = [];
$tipoReporte = '';
$mensaje = '';
$exito = false;
$idReporteGenerado = 0;

// Procesar generación de reportes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener parámetros del formulario
    $tipoReporte = $_POST['tipo_reporte'] ?? '';
    $fechaInicio = $_POST['fecha_inicio'] ?? '';
    $fechaFin = $_POST['fecha_fin'] ?? '';
    $idAlumno = isset($_POST['alumno']) && $_POST['alumno'] !== '' ? (int)$_POST['alumno'] : null;
    $exportar = isset($_POST['exportar']) && $_POST['exportar'] === '1';

    // Validar datos
    if (empty($tipoReporte) || empty($fechaInicio) || empty($fechaFin)) {
        $mensaje = 'Por favor, complete todos los campos obligatorios.';
    } else {
        // Preparar parámetros
        $parametros = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'id_alumno' => $idAlumno
        ];

        // Generar reporte
        $resultado = $reporteController->generarReporte($idUsuario, $tipoReporte, $parametros, $exportar);

        if ($resultado['exito']) {
            $exito = true;
            $mensaje = $resultado['mensaje'];
            $datosReporte = $resultado['datos'];
            $idReporteGenerado = $resultado['id_reporte'];
        } else {
            $mensaje = $resultado['mensaje'];
        }
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
                        <a href="pagos.php" class="dashboard__menu-enlace">
                            <i class="fas fa-credit-card"></i>
                            <span class="dashboard__menu-texto">Pagos</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="reportes.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
                            <i class="fas fa-chart-bar"></i>
                            <span class="dashboard__menu-texto">Reportes</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <main class="dashboard__contenido">
                <h2 class="dashboard__heading">Generación de Reportes</h2>

                <?php if (!empty($mensaje)): ?>
                    <div class="alerta <?php echo $exito ? 'alerta-exito' : 'alerta-error'; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard__contenedor">
                    <form action="#" method="POST" class="reportes__formulario">
                        <div class="reportes__campo">
                            <label for="tipo_reporte" class="reportes__label">Tipo de Reporte:</label>
                            <select id="tipo_reporte" name="tipo_reporte" class="reportes__select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="Asistencia" <?php echo $tipoReporte === 'Asistencia' ? 'selected' : ''; ?>>Asistencia</option>
                                <option value="Pagos" <?php echo $tipoReporte === 'Pagos' ? 'selected' : ''; ?>>Pagos</option>
                                <option value="Accesos" <?php echo $tipoReporte === 'Accesos' ? 'selected' : ''; ?>>Accesos</option>
                                <option value="General" <?php echo $tipoReporte === 'General' ? 'selected' : ''; ?>>General</option>
                            </select>
                        </div>

                        <div class="reportes__campo">
                            <label for="alumno" class="reportes__label">Alumno (opcional):</label>
                            <select id="alumno" name="alumno" class="reportes__select">
                                <option value="">Todos los alumnos</option>
                                <?php foreach ($alumnos as $alumno): ?>
                                    <?php
                                    $selected = isset($_POST['alumno']) && $_POST['alumno'] == $alumno['id_alumno'] ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $alumno['id_alumno']; ?>" <?php echo $selected; ?>>
                                        <?php echo $alumno['nombre'] . ' ' . $alumno['apellidos']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="reportes__campo">
                            <label for="fecha_inicio" class="reportes__label">Fecha Inicio:</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="reportes__input" required value="<?php echo $_POST['fecha_inicio'] ?? ''; ?>">
                        </div>

                        <div class="reportes__campo">
                            <label for="fecha_fin" class="reportes__label">Fecha Fin:</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="reportes__input" required value="<?php echo $_POST['fecha_fin'] ?? ''; ?>">
                        </div>

                        <div class="reportes__acciones">
                            <button type="submit" class="reportes__submit">
                                <i class="fas fa-search"></i> Generar Reporte
                            </button>

                            <?php if ($exito && $idReporteGenerado > 0): ?>
                                <a href="exportar-reporte.php?id=<?php echo $idReporteGenerado; ?>" class="reportes__exportar">
                                    <i class="fas fa-file-export"></i> Exportar a Excel
                                </a>
                            <?php else: ?>
                                <button type="submit" name="exportar" value="1" class="reportes__exportar">
                                    <i class="fas fa-file-export"></i> Generar y Exportar
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if ($exito && !empty($datosReporte)): ?>
                        <div class="reportes__vista-previa">
                            <h3>Resultados del Reporte</h3>

                            <?php if ($tipoReporte === 'Asistencia'): ?>
                                <!-- Reporte de Asistencia -->
                                <table class="reportes__tabla">
                                    <thead class="reportes__thead">
                                        <tr>
                                            <th class="reportes__th">Alumno</th>
                                            <th class="reportes__th">Fecha</th>
                                            <th class="reportes__th">Entrada</th>
                                            <th class="reportes__th">Salida</th>
                                            <th class="reportes__th">Tipo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="reportes__tbody">
                                        <?php foreach ($datosReporte as $registro): ?>
                                            <tr class="reportes__tr">
                                                <td class="reportes__td"><?php echo $registro['nombre_alumno']; ?></td>
                                                <td class="reportes__td"><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></td>
                                                <td class="reportes__td"><?php echo $registro['hora_entrada'] ? date('H:i', strtotime($registro['hora_entrada'])) : '-'; ?></td>
                                                <td class="reportes__td"><?php echo $registro['hora_salida'] ? date('H:i', strtotime($registro['hora_salida'])) : '-'; ?></td>
                                                <td class="reportes__td"><?php echo $registro['tipo_asistencia']; ?></td>
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

                            <?php elseif ($tipoReporte === 'Pagos'): ?>
                                <!-- Reporte de Pagos -->
                                <table class="reportes__tabla">
                                    <thead class="reportes__thead">
                                        <tr>
                                            <th class="reportes__th">Alumno</th>
                                            <th class="reportes__th">Periodo</th>
                                            <th class="reportes__th">Monto</th>
                                            <th class="reportes__th">Fecha Pago</th>
                                            <th class="reportes__th">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="reportes__tbody">
                                        <?php foreach ($datosReporte as $registro): ?>
                                            <tr class="reportes__tr">
                                                <td class="reportes__td"><?php echo $registro['nombre_completo']; ?></td>
                                                <td class="reportes__td"><?php echo $registro['periodo']; ?></td>
                                                <td class="reportes__td">$<?php echo number_format($registro['monto'], 2); ?></td>
                                                <td class="reportes__td"><?php echo date('d/m/Y H:i', strtotime($registro['fecha_pago'])); ?></td>
                                                <td class="reportes__td"><?php echo $registro['estado_pago']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <!-- Gráfica de pagos -->
                                <div class="reportes__grafica">
                                    <canvas id="grafica-pagos"></canvas>
                                </div>

                                <script>
                                    // Preparar datos para la gráfica
                                    const datosPagos = {
                                        labels: ['Pagado', 'Pendiente', 'Vencido'],
                                        datasets: [{
                                            label: 'Estado de Pagos',
                                            data: [
                                                <?php
                                                // Contar los diferentes estados de pago
                                                $pagados = 0;
                                                $pendientes = 0;
                                                $vencidos = 0;

                                                foreach ($datosReporte as $registro) {
                                                    if ($registro['estado_pago'] === 'Pagado') {
                                                        $pagados++;
                                                    } elseif ($registro['estado_pago'] === 'Pendiente') {
                                                        $pendientes++;
                                                    } elseif ($registro['estado_pago'] === 'Vencido') {
                                                        $vencidos++;
                                                    }
                                                }

                                                echo "$pagados, $pendientes, $vencidos";
                                                ?>
                                            ],
                                            backgroundColor: [
                                                '#5CB85C', // Verde para pagados
                                                '#F0AD4E', // Amarillo para pendientes
                                                '#D9534F' // Rojo para vencidos
                                            ],
                                            borderWidth: 1
                                        }]
                                    };

                                    // Crear gráfica
                                    const ctxPagos = document.getElementById('grafica-pagos').getContext('2d');
                                    const graficaPagos = new Chart(ctxPagos, {
                                        type: 'doughnut',
                                        data: datosPagos,
                                        options: {
                                            responsive: true,
                                            plugins: {
                                                legend: {
                                                    position: 'top',
                                                },
                                                title: {
                                                    display: true,
                                                    text: 'Distribución de Estados de Pago'
                                                }
                                            }
                                        },
                                    });
                                </script>

                            <?php elseif ($tipoReporte === 'Accesos'): ?>
                                <!-- Reporte de Accesos -->
                                <table class="reportes__tabla">
                                    <thead class="reportes__thead">
                                        <tr>
                                            <th class="reportes__th">Alumno</th>
                                            <th class="reportes__th">Fecha</th>
                                            <th class="reportes__th">Hora</th>
                                            <th class="reportes__th">Tipo</th>
                                            <th class="reportes__th">Permitido</th>
                                        </tr>
                                    </thead>
                                    <tbody class="reportes__tbody">
                                        <?php foreach ($datosReporte as $registro): ?>
                                            <tr class="reportes__tr">
                                                <td class="reportes__td"><?php echo $registro['nombre_completo']; ?></td>
                                                <td class="reportes__td"><?php echo date('d/m/Y', strtotime($registro['fecha_hora'])); ?></td>
                                                <td class="reportes__td"><?php echo date('H:i', strtotime($registro['fecha_hora'])); ?></td>
                                                <td class="reportes__td"><?php echo $registro['tipo_acceso']; ?></td>
                                                <td class="reportes__td">
                                                    <?php if ($registro['permitido']): ?>
                                                        <span style="color: #5CB85C;"><i class="fas fa-check-circle"></i> Sí</span>
                                                    <?php else: ?>
                                                        <span style="color: #D9534F;"><i class="fas fa-times-circle"></i> No</span>
                                                        <?php if (!empty($registro['motivo_rechazo'])): ?>
                                                            <small>(<?php echo $registro['motivo_rechazo']; ?>)</small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <!-- Gráfica de accesos -->
                                <div class="reportes__grafica">
                                    <canvas id="grafica-accesos"></canvas>
                                </div>

                                <script>
                                    // Preparar datos para la gráfica
                                    const datosAccesos = {
                                        labels: ['Entradas', 'Salidas'],
                                        datasets: [{
                                            label: 'Tipo de Acceso',
                                            data: [
                                                <?php
                                                // Contar los diferentes tipos de acceso
                                                $entradas = 0;
                                                $salidas = 0;

                                                foreach ($datosReporte as $registro) {
                                                    if ($registro['tipo_acceso'] === 'Entrada') {
                                                        $entradas++;
                                                    } elseif ($registro['tipo_acceso'] === 'Salida') {
                                                        $salidas++;
                                                    }
                                                }

                                                echo "$entradas, $salidas";
                                                ?>
                                            ],
                                            backgroundColor: [
                                                '#5BC0DE', // Azul para entradas
                                                '#D84B16' // Naranja para salidas
                                            ],
                                            borderWidth: 1
                                        }]
                                    };

                                    // Crear gráfica
                                    const ctxAccesos = document.getElementById('grafica-accesos').getContext('2d');
                                    const graficaAccesos = new Chart(ctxAccesos, {
                                        type: 'bar',
                                        data: datosAccesos,
                                        options: {
                                            responsive: true,
                                            plugins: {
                                                legend: {
                                                    position: 'top',
                                                },
                                                title: {
                                                    display: true,
                                                    text: 'Distribución de Tipos de Acceso'
                                                }
                                            }
                                        },
                                    });
                                </script>

                            <?php elseif ($tipoReporte === 'General'): ?>
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
                                            <h4>Pagos</h4>
                                            <p>Total de Pagos: <strong><?php echo $datosReporte['pagos']['total']; ?></strong></p>
                                            <p>Monto Total: <strong>$<?php echo number_format($datosReporte['pagos']['monto_total'], 2); ?></strong></p>
                                            <p>Pagados: <strong><?php echo $datosReporte['pagos']['pagados']; ?></strong></p>
                                            <p>Pendientes: <strong><?php echo $datosReporte['pagos']['pendientes']; ?></strong></p>
                                        </div>

                                        <div class="reportes__estadistica">
                                            <h4>Accesos</h4>
                                            <p>Total de Accesos: <strong><?php echo $datosReporte['accesos']['total']; ?></strong></p>
                                            <p>Entradas: <strong><?php echo $datosReporte['accesos']['entradas']; ?></strong></p>
                                            <p>Salidas: <strong><?php echo $datosReporte['accesos']['salidas']; ?></strong></p>
                                            <p>Accesos Denegados: <strong><?php echo $datosReporte['accesos']['denegados']; ?></strong></p>
                                        </div>
                                    </div>

                                    <!-- Gráfica general -->
                                    <div class="reportes__grafica">
                                        <canvas id="grafica-general"></canvas>
                                    </div>

                                    <script>
                                        // Preparar datos para la gráfica general
                                        const meses = [
                                            <?php
                                            // Obtener los meses en el rango de fechas
                                            $meses = [];
                                            if (!empty($datosReporte['grafica']['meses'])) {
                                                foreach ($datosReporte['grafica']['meses'] as $mes) {
                                                    echo "'$mes', ";
                                                }
                                            }
                                            ?>
                                        ];

                                        const datosGeneral = {
                                            labels: meses,
                                            datasets: [{
                                                    label: 'Asistencias',
                                                    data: <?php echo json_encode($datosReporte['grafica']['asistencias'] ?? []); ?>,
                                                    borderColor: '#5CB85C',
                                                    backgroundColor: 'rgba(92, 184, 92, 0.2)',
                                                    borderWidth: 2,
                                                    pointRadius: 4
                                                },
                                                {
                                                    label: 'Pagos',
                                                    data: <?php echo json_encode($datosReporte['grafica']['pagos'] ?? []); ?>,
                                                    borderColor: '#F0AD4E',
                                                    backgroundColor: 'rgba(240, 173, 78, 0.2)',
                                                    borderWidth: 2,
                                                    pointRadius: 4
                                                },
                                                {
                                                    label: 'Accesos',
                                                    data: <?php echo json_encode($datosReporte['grafica']['accesos'] ?? []); ?>,
                                                    borderColor: '#5BC0DE',
                                                    backgroundColor: 'rgba(91, 192, 222, 0.2)',
                                                    borderWidth: 2,
                                                    pointRadius: 4
                                                }
                                            ]
                                        };

                                        // Crear gráfica general
                                        const ctxGeneral = document.getElementById('grafica-general').getContext('2d');
                                        const graficaGeneral = new Chart(ctxGeneral, {
                                            type: 'line',
                                            data: datosGeneral,
                                            options: {
                                                responsive: true,
                                                plugins: {
                                                    legend: {
                                                        position: 'top',
                                                    },
                                                    title: {
                                                        display: true,
                                                        text: 'Tendencias por Mes'
                                                    }
                                                },
                                                scales: {
                                                    y: {
                                                        beginAtZero: true
                                                    }
                                                }
                                            },
                                        });
                                    </script>
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