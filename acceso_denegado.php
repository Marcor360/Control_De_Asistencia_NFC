<?php
// acceso_denegado.php
require_once 'controladores/AuthController.php';

// Inicializar controlador de autenticación
$auth = new AuthController();

// Verificar si está autenticado
if (!$auth->estaAutenticado()) {
    header('Location: login.php');
    exit;
}

// Obtener información del usuario
$nombreCompleto = $_SESSION['nombre_completo'] ?? 'Usuario';
$tipoRol = $_SESSION['tipo_rol'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - Sistema de Asistencia NFC</title>
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

        <div class="acceso-denegado">
            <div class="acceso-denegado__contenedor">
                <i class="fas fa-exclamation-triangle acceso-denegado__icono"></i>
                <h2 class="acceso-denegado__titulo">Acceso Denegado</h2>
                <p class="acceso-denegado__mensaje">No tienes permiso para acceder a esta sección.</p>

                <?php if ($tipoRol === 'Alumno'): ?>
                    <a href="vista_alumno.php" class="boton">
                        <i class="fas fa-home"></i> Ir al Portal de Alumno
                    </a>
                <?php else: ?>
                    <a href="dashboard.php" class="boton">
                        <i class="fas fa-home"></i> Ir al Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="build/js/bundle.min.js"></script>
</body>

</html>