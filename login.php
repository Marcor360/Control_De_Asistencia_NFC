<?php
// login.php (en la raíz del proyecto)
require_once 'controladores/AuthController.php';

// Inicializar controlador
$auth = new AuthController();

// Verificar si ya está autenticado
if ($auth->estaAutenticado()) {
    header('Location: dashboard.php');
    exit;
}

// Procesar formulario de login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreUsuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($nombreUsuario) || empty($contrasena)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        if ($auth->login($nombreUsuario, $contrasena)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Nombre de usuario o contraseña incorrectos.';
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
                <img src="build/img/logo.png" alt="Logo Sistema Asistencia">
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
                    <input class="login__input" type="text" id="usuario" name="usuario" placeholder="Tu nombre de usuario" value="<?php echo $_POST['usuario'] ?? ''; ?>">
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