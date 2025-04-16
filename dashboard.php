<?php
// dashboard.php (en la raíz del proyecto)
require_once 'controladores/AuthController.php';

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
</head>

<body>
    <div class="dashboard">
        <header class="dashboard__header">
            <div class="dashboard__logo">
                <img src="build/img/logo.png" alt="Logo Sistema">
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
                <h2 class="dashboard__heading">Panel de Control</h2>

                <div class="dashboard__widgets">
                    <div class="dashboard__widget">
                        <h3 class="dashboard__widget-titulo">Total Alumnos</h3>
                        <p class="dashboard__widget-contenido">0</p>
                    </div>

                    <div class="dashboard__widget">
                        <h3 class="dashboard__widget-titulo">Asistencias Hoy</h3>
                        <p class="dashboard__widget-contenido">0</p>
                    </div>

                    <div class="dashboard__widget">
                        <h3 class="dashboard__widget-titulo">Pagos Pendientes</h3>
                        <p class="dashboard__widget-contenido">0</p>
                    </div>

                    <div class="dashboard__widget">
                        <h3 class="dashboard__widget-titulo">Tarjetas Activas</h3>
                        <p class="dashboard__widget-contenido">0</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="build/js/bundle.min.js"></script>
</body>

</html>