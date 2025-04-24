<?php
// dashboard.php (en la raíz del proyecto)
require_once 'controladores/AuthController.php';
require_once 'controladores/AlumnoController.php';
require_once 'controladores/AsistenciaController.php';
require_once 'controladores/PagoController.php';
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

// Inicializar controladores para estadísticas
$alumnoController = new AlumnoController();
$asistenciaController = new AsistenciaController();
$pagoController = new PagoController();
$tarjetaController = new TarjetaController();

// Obtener estadísticas para el dashboard - con manejo de posibles errores
try {
    $estadisticasAlumnos = method_exists($alumnoController, 'obtenerEstadisticas')
        ? $alumnoController->obtenerEstadisticas()
        : [
            'total' => 0,
            'al_corriente' => 0,
            'pendientes' => 0,
            'bloqueados' => 0
        ];
} catch (Exception $e) {
    // Si hay un error, establecer valores predeterminados
    $estadisticasAlumnos = [
        'total' => 0,
        'al_corriente' => 0,
        'pendientes' => 0,
        'bloqueados' => 0
    ];
}

try {
    $estadisticasAsistencia = method_exists($asistenciaController, 'obtenerEstadisticas')
        ? $asistenciaController->obtenerEstadisticas()
        : [
            'hoy' => [
                'total' => 0,
                'presentes' => 0,
                'retardos' => 0,
                'ausentes' => 0
            ]
        ];
} catch (Exception $e) {
    // Si hay un error, establecer valores predeterminados
    $estadisticasAsistencia = [
        'hoy' => [
            'total' => 0,
            'presentes' => 0,
            'retardos' => 0,
            'ausentes' => 0
        ]
    ];
}

try {
    $resumenPagos = method_exists($pagoController, 'obtenerResumenPagos')
        ? $pagoController->obtenerResumenPagos()
        : [
            'total_mes' => 0,
            'pendientes' => 0,
            'bloqueados' => 0
        ];
} catch (Exception $e) {
    // Si hay un error, establecer valores predeterminados
    $resumenPagos = [
        'total_mes' => 0,
        'pendientes' => 0,
        'bloqueados' => 0
    ];
}

// Obtener cantidad de tarjetas activas
try {
    $tarjetasActivas = method_exists($tarjetaController, 'contarTarjetasActivas')
        ? $tarjetaController->contarTarjetasActivas()
        : 0;
} catch (Exception $e) {
    $tarjetasActivas = 0;
}

// Obtener últimos registros para mostrar actividad reciente
try {
    $ultimasAsistencias = method_exists($asistenciaController, 'obtenerRegistrosRecientes')
        ? $asistenciaController->obtenerRegistrosRecientes(5)
        : [];
} catch (Exception $e) {
    $ultimasAsistencias = [];
}

try {
    $ultimosPagos = method_exists($pagoController, 'obtenerUltimosPagos')
        ? $pagoController->obtenerUltimosPagos(5)
        : [];
} catch (Exception $e) {
    $ultimosPagos = [];
}

// Fecha actual para mostrar
$fechaActual = date('d/m/Y');
$diaActual = date('l'); // Día de la semana en inglés
$diasSemana = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];
$diaEspanol = $diasSemana[$diaActual];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Asistencia NFC</title>
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
                        <a href="dashboard.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
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
                        <a href="reportes.php" class="dashboard__menu-enlace">
                            <i class="fas fa-chart-bar"></i>
                            <span class="dashboard__menu-texto">Reportes</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <main class="dashboard__contenido">
                <div class="dashboard__fecha">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?php echo $diaEspanol . ' ' . $fechaActual; ?></span>
                </div>

                <h2 class="dashboard__heading">Panel de Control</h2>

                <div class="dashboard__widgets">
                    <div class="dashboard__widget">
                        <h3 class="dashboard__widget-titulo">
                            <i class="fas fa-users"></i>
                            Total Alumnos
                        </h3>
                        <p class="dashboard__widget-contenido"><?php echo $estadisticasAlumnos['total']; ?></p>
                        <div class="dashboard__widget-detalles">
                            <div class="dashboard__widget-detalle">
                                <span class="dashboard__widget-etiqueta">
                                    <i class="fas fa-check-circle"></i> Al corriente:
                                </span>
                                <span class="dashboard__widget-valor"><?php echo $estadisticasAlumnos['al_corriente']; ?></span>
                            </div>
                            <div class="dashboard__widget-detalle">
                                <span class="dashboard__widget-etiqueta">
                                    <i class="fas fa-clock"></i> Pendientes:
                                </span>
                                <span class="dashboard__widget-valor"><?php echo $estadisticasAlumnos['pendientes']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard__widget">
                        <h3 class="dashboard__widget-titulo">
                            <i class="fas fa-clipboard-check"></i>
                            Asistencias Hoy
                        </h3>
                        <p class="dashboard__widget-contenido"><?php echo isset($estadisticasAsistencia['hoy']) ? $estadisticasAsistencia['hoy']['total'] : 0; ?></p>
                        <div class="dashboard__widget-detalles">
                            <div class="dashboard__widget-detalle">
                                <span class="dashboard__widget-etiqueta">
                                    <i class="fas fa-clock"></i> Retardos:
                                </span>
                                <span class="dashboard__widget-valor"><?php echo isset($estadisticasAsistencia['hoy']) ? $estadisticasAsistencia['hoy']['retardos'] : 0; ?></span>
                            </div>
                            <div class="dashboard__widget-detalle">
                                <span class="dashboard__widget-etiqueta">
                                    <i class="fas fa-times-circle"></i> Ausentes:
                                </span>
                                <span class="dashboard__widget-valor"><?php echo isset($estadisticasAsistencia['hoy']) ? $estadisticasAsistencia['hoy']['ausentes'] : 0; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard__widget">
                        <h3 class="dashboard__widget-titulo">
                            <i class="fas fa-credit-card"></i>
                            Pagos Pendientes
                        </h3>
                        <p class="dashboard__widget-contenido"><?php echo isset($resumenPagos['pendientes']) ? $resumenPagos['pendientes'] : 0; ?></p>
                        <div class="dashboard__widget-detalles">
                            <div class="dashboard__widget-detalle">
                                <span class="dashboard__widget-etiqueta">
                                    <i class="fas fa-money-bill-wave"></i> Total Mes:
                                </span>
                                <span class="dashboard__widget-valor">$<?php echo isset($resumenPagos['total_mes']) ? number_format($resumenPagos['total_mes'], 2) : '0.00'; ?></span>
                            </div>
                            <div class="dashboard__widget-detalle">
                                <span class="dashboard__widget-etiqueta">
                                    <i class="fas fa-ban"></i> Bloqueados:
                                </span>
                                <span class="dashboard__widget-valor"><?php echo isset($resumenPagos['bloqueados']) ? $resumenPagos['bloqueados'] : 0; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard__widget">
                        <h3 class="dashboard__widget-titulo">
                            <i class="fas fa-id-card"></i>
                            Tarjetas Activas
                        </h3>
                        <p class="dashboard__widget-contenido"><?php echo $tarjetasActivas; ?></p>
                        <div class="dashboard__widget-detalles">
                            <div class="dashboard__widget-detalle">
                                <span class="dashboard__widget-etiqueta">
                                    <i class="fas fa-percentage"></i> Cobertura:
                                </span>
                                <span class="dashboard__widget-valor">
                                    <?php
                                    $porcentajeCobertura = $estadisticasAlumnos['total'] > 0
                                        ? round(($tarjetasActivas / $estadisticasAlumnos['total']) * 100)
                                        : 0;
                                    echo $porcentajeCobertura . '%';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard__grid-secundario">
                    <div class="dashboard__grafica-container">
                        <h3 class="dashboard__subtitulo">
                            <i class="fas fa-chart-bar"></i>
                            Asistencia Semanal
                        </h3>
                        <div class="dashboard__grafica">
                            <canvas id="graficaAsistencia"></canvas>
                        </div>
                    </div>

                    <div class="dashboard__actividad">
                        <h3 class="dashboard__subtitulo">
                            <i class="fas fa-history"></i>
                            Actividad Reciente
                        </h3>

                        <div class="dashboard__tabs">
                            <button class="dashboard__tab dashboard__tab--activo" data-tab="asistencias">
                                <i class="fas fa-clipboard-check"></i> Asistencias
                            </button>
                            <button class="dashboard__tab" data-tab="pagos">
                                <i class="fas fa-credit-card"></i> Pagos
                            </button>
                        </div>

                        <div class="dashboard__tab-content dashboard__tab-content--activo" id="tab-asistencias">
                            <?php if (empty($ultimasAsistencias)): ?>
                                <p class="dashboard__sin-datos">No hay registros de asistencia recientes.</p>
                            <?php else: ?>
                                <ul class="dashboard__lista-actividad">
                                    <?php foreach ($ultimasAsistencias as $asistencia): ?>
                                        <li class="dashboard__actividad-item">
                                            <div class="dashboard__actividad-icono">
                                                <?php if (isset($asistencia['tipo_asistencia']) && $asistencia['tipo_asistencia'] === 'Presente'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php elseif (isset($asistencia['tipo_asistencia']) && $asistencia['tipo_asistencia'] === 'Retardo'): ?>
                                                    <i class="fas fa-clock"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="dashboard__actividad-info">
                                                <p class="dashboard__actividad-nombre">
                                                    <?php echo isset($asistencia['nombre_completo']) ? $asistencia['nombre_completo'] : 'Alumno'; ?>
                                                </p>
                                                <p class="dashboard__actividad-detalle">
                                                    <?php
                                                    echo isset($asistencia['tipo_asistencia']) ? $asistencia['tipo_asistencia'] . ' - ' : '';
                                                    echo isset($asistencia['fecha']) ? date('d/m/Y', strtotime($asistencia['fecha'])) . ' ' : '';
                                                    echo isset($asistencia['hora_entrada']) ? date('H:i', strtotime($asistencia['hora_entrada'])) : '';
                                                    ?>
                                                </p>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="asistencia.php" class="dashboard__ver-mas">Ver todos los registros</a>
                            <?php endif; ?>
                        </div>

                        <div class="dashboard__tab-content" id="tab-pagos">
                            <?php if (empty($ultimosPagos)): ?>
                                <p class="dashboard__sin-datos">No hay registros de pagos recientes.</p>
                            <?php else: ?>
                                <ul class="dashboard__lista-actividad">
                                    <?php foreach ($ultimosPagos as $pago): ?>
                                        <li class="dashboard__actividad-item">
                                            <div class="dashboard__actividad-icono">
                                                <?php if (isset($pago['estado_pago']) && $pago['estado_pago'] === 'Pagado'): ?>
                                                    <i class="fas fa-check-circle"></i>
                                                <?php elseif (isset($pago['estado_pago']) && $pago['estado_pago'] === 'Pendiente'): ?>
                                                    <i class="fas fa-clock"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-circle"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="dashboard__actividad-info">
                                                <p class="dashboard__actividad-nombre">
                                                    <?php echo isset($pago['nombre_completo']) ? $pago['nombre_completo'] : 'Alumno'; ?>
                                                </p>
                                                <p class="dashboard__actividad-detalle">
                                                    $<?php echo isset($pago['monto']) ? number_format($pago['monto'], 2) : '0.00'; ?> -
                                                    <?php echo isset($pago['estado_pago']) ? $pago['estado_pago'] : 'Desconocido'; ?> -
                                                    <?php echo isset($pago['fecha_pago']) ? date('d/m/Y H:i', strtotime($pago['fecha_pago'])) : ''; ?>
                                                </p>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <a href="pagos.php" class="dashboard__ver-mas">Ver todos los pagos</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="build/js/bundle.min.js"></script>
    <script>
        // Gráfica de asistencia semanal
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar datos para la gráfica de asistencia
            const datosAsistencia = {
                labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
                datasets: [{
                        label: 'Presentes',
                        backgroundColor: 'rgba(92, 184, 92, 0.5)',
                        borderColor: '#5CB85C',
                        borderWidth: 2,
                        data: [
                            <?php
                            // Aquí se deberían obtener datos reales de asistencia semanal
                            // Por ahora colocamos datos de ejemplo
                            echo rand(15, 25) . ', ' .
                                rand(18, 28) . ', ' .
                                rand(20, 30) . ', ' .
                                rand(15, 25) . ', ' .
                                rand(10, 20);
                            ?>
                        ]
                    },
                    {
                        label: 'Retardos',
                        backgroundColor: 'rgba(240, 173, 78, 0.5)',
                        borderColor: '#F0AD4E',
                        borderWidth: 2,
                        data: [
                            <?php
                            // Datos de ejemplo para retardos
                            echo rand(2, 8) . ', ' .
                                rand(1, 6) . ', ' .
                                rand(3, 7) . ', ' .
                                rand(2, 5) . ', ' .
                                rand(3, 9);
                            ?>
                        ]
                    },
                    {
                        label: 'Ausentes',
                        backgroundColor: 'rgba(217, 83, 79, 0.5)',
                        borderColor: '#D9534F',
                        borderWidth: 2,
                        data: [
                            <?php
                            // Datos de ejemplo para ausencias
                            echo rand(3, 10) . ', ' .
                                rand(2, 8) . ', ' .
                                rand(1, 5) . ', ' .
                                rand(3, 7) . ', ' .
                                rand(5, 12);
                            ?>
                        ]
                    }
                ]
            };

            // Configurar la gráfica
            const ctx = document.getElementById('graficaAsistencia').getContext('2d');
            const graficaAsistencia = new Chart(ctx, {
                type: 'bar',
                data: datosAsistencia,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Asistencia Semanal'
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    }
                }
            });

            // Manejar las pestañas de actividad reciente
            const tabs = document.querySelectorAll('.dashboard__tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Quitar clase activa de todas las pestañas
                    tabs.forEach(t => t.classList.remove('dashboard__tab--activo'));

                    // Agregar clase activa a la pestaña actual
                    this.classList.add('dashboard__tab--activo');

                    // Mostrar el contenido correspondiente
                    const tabId = this.getAttribute('data-tab');
                    document.querySelectorAll('.dashboard__tab-content').forEach(content => {
                        content.classList.remove('dashboard__tab-content--activo');
                    });
                    document.getElementById('tab-' + tabId).classList.add('dashboard__tab-content--activo');
                });
            });
        });
    </script>
</body>

</html>