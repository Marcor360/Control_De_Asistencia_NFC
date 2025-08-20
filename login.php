<?php
// login.php - Script de login para el sistema de asistencia NFC

// Mostrar errores para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Si ya está autenticado, redirigir según su rol
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['tipo_rol'] === 'Alumno') {
        header('Location: alumno_dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

// Incluir configuración de base de datos
require_once 'config/database.php';

// Variables para el formulario
$error = '';
$nombre_usuario = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($nombre_usuario) || empty($contrasena)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        // Verificar que el driver MySQL para PDO esté disponible
        if (!in_array('mysql', PDO::getAvailableDrivers())) {
            $error = 'Error del sistema: controlador de base de datos no disponible.';
        } else {
            try {
                // Conectar a la base de datos usando PDO
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                // Generar hash de la contraseña
                $contrasena_hash = hash('sha256', $contrasena);

                // Consultar usuario
                $query = "SELECT * FROM usuario WHERE nombre_usuario = :usuario AND contraseña = :contrasena";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':usuario', $nombre_usuario);
                $stmt->bindParam(':contrasena', $contrasena_hash);
                $stmt->execute();
                $usuario = $stmt->fetch();

                if ($usuario) {
                    // Usuario encontrado
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                    $_SESSION['nombre_completo'] = $usuario['nombre'] . ' ' . $usuario['apellidos'];
                    $_SESSION['tipo_rol'] = $usuario['tipo_rol'];

                    // Actualizar último acceso
                    $update = "UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = :id";
                    $update_stmt = $pdo->prepare($update);
                    $update_stmt->bindParam(':id', $usuario['id_usuario'], PDO::PARAM_INT);
                    $update_stmt->execute();

                    // Redirigir según el rol
                    if ($usuario['tipo_rol'] === 'Alumno') {
                        header('Location: alumno_dashboard.php');
                    } else {
                        header('Location: dashboard.php');
                    }
                    exit;
                } else {
                    $error = 'Nombre de usuario o contraseña incorrectos.';

                    // Verificar si el usuario existe
                    $check_query = "SELECT * FROM usuario WHERE nombre_usuario = :usuario";
                    $check_stmt = $pdo->prepare($check_query);
                    $check_stmt->bindParam(':usuario', $nombre_usuario);
                    $check_stmt->execute();
                    $check_result = $check_stmt->fetch();

                    if ($check_result) {
                        // Usuario existe pero contraseña incorrecta
                        $error = 'Contraseña incorrecta para el usuario "' . htmlspecialchars($nombre_usuario) . '".';
                    } else {
                        // Usuario no existe
                        $error = 'El usuario "' . htmlspecialchars($nombre_usuario) . '" no existe.';
                    }
                }

                // Cerrar conexión
                $pdo = null;
            } catch (PDOException $e) {
                $error = 'Error del sistema: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Asistencia NFC</title>
    <link rel="stylesheet" href="build/css/app.css">
    <!-- Fontawesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="login">
        <div class="login__contenedor">
            <div class="login__logo">
                <img src="build/img/logo-svg.svg" alt="Logo Sistema Asistencia">
            </div>

            <h2 class="login__heading">Control de Asistencia</h2>

            <?php if (!empty($error)): ?>
                <div class="alerta alerta-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form class="login__formulario" method="POST">
                <div class="login__campo">
                    <label class="login__label" for="usuario">Usuario:</label>
                    <input class="login__input" type="text" id="usuario" name="usuario" placeholder="Tu nombre de usuario" value="<?php echo htmlspecialchars($nombre_usuario); ?>">
                </div>

                <div class="login__campo">
                    <label class="login__label" for="contrasena">Contraseña:</label>
                    <input class="login__input" type="password" id="contrasena" name="contrasena" placeholder="Tu contraseña">
                </div>

                <input type="submit" class="login__submit" value="Iniciar Sesión">
            </form>

            <a href="index.php" class="login__enlace">
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
</body>

</html>