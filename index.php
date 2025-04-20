<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Control de Asistencia NFC</title>
    <link rel="stylesheet" href="build/css/app.css">
    <!-- Fontawesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="landing">
        <header class="landing__header">
            <div class="landing__logo">
                <img src="build/img/logo-svg.svg" alt="Logo Sistema">
            </div>
        </header>

        <div class="landing__contenedor">
            <div class="landing__grid">
                <div class="landing__texto">
                    <h1 class="landing__heading">Sistema de Control de Asistencia con NFC</h1>
                    <p class="landing__descripcion">
                        Bienvenido al sistema de control de asistencia con tecnología NFC, una solución moderna
                        para la gestión de asistencia y pagos en instituciones educativas.
                    </p>

                    <div class="landing__botones">
                        <a href="login.php" class="boton">
                            <i class="fas fa-sign-in-alt"></i>
                            Iniciar Sesión
                        </a>
                        <a href="#contacto" class="boton-azul-claro">
                            <i class="fas fa-info-circle"></i>
                            Más Información
                        </a>
                    </div>
                </div>

                <div class="landing__imagen">
                    <img src="build/img/nfc-illustration-svg.svg" alt="Ilustración NFC">
                </div>
            </div>
        </div>

        <section class="caracteristicas">
            <div class="contenedor">
                <h2 class="caracteristicas__heading">Características Principales</h2>

                <div class="caracteristicas__grid">
                    <div class="caracteristica">
                        <div class="caracteristica__icono">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h3 class="caracteristica__titulo">Control con Tarjetas NFC</h3>
                        <p>Control de acceso y asistencia seguro mediante tarjetas NFC personalizadas.</p>
                    </div>

                    <div class="caracteristica">
                        <div class="caracteristica__icono">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="caracteristica__titulo">Reportes Detallados</h3>
                        <p>Genera reportes de asistencia y accesos para un mejor seguimiento.</p>
                    </div>

                    <div class="caracteristica">
                        <div class="caracteristica__icono">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="caracteristica__titulo">Gestión de Pagos</h3>
                        <p>Control y administración de pagos de los alumnos integrado al sistema.</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer" id="contacto">
            <div class="contenedor">
                <p>Sistema desarrollado para Control de Asistencia y Pagos UTC</p>
                <p>Universidad Tecnológica &copy; <?php echo date('Y'); ?></p>
            </div>
        </footer>
    </div>

    <script src="build/js/bundle.min.js"></script>
</body>

</html>