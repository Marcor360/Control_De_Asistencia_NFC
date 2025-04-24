<?php
// administrar_usuarios.php
require_once 'controladores/AuthController.php';
require_once 'controladores/UsuarioController.php';

// Inicializar controlador de autenticación
$auth = new AuthController();

// Verificar si está autenticado
if (!$auth->estaAutenticado()) {
    header('Location: login.php');
    exit;
}

// Verificar que sea un administrador
if ($_SESSION['tipo_rol'] !== 'Administrador') {
    header('Location: acceso_denegado.php');
    exit;
}

// Obtener información del usuario
$nombreCompleto = $_SESSION['nombre_completo'];
$tipoRol = $_SESSION['tipo_rol'];

// Inicializar controlador de usuarios
$usuarioController = new UsuarioController();

// Variables para mensajes
$mensaje = '';
$tipo = '';

// Procesar formulario de creación/edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_usuario'])) {
        // Recoger datos del formulario
        $datos = [
            'nombre' => filter_var($_POST['nombre'], FILTER_SANITIZE_SPECIAL_CHARS),
            'apellidos' => filter_var($_POST['apellidos'], FILTER_SANITIZE_SPECIAL_CHARS),
            'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            'tipo_rol' => filter_var($_POST['tipo_rol'], FILTER_SANITIZE_SPECIAL_CHARS),
            'nombre_usuario' => filter_var($_POST['nombre_usuario'], FILTER_SANITIZE_SPECIAL_CHARS),
            'contrasena' => $_POST['contrasena']
        ];

        // Validar campos requeridos
        if (
            empty($datos['nombre']) || empty($datos['apellidos']) || empty($datos['email']) ||
            empty($datos['tipo_rol']) || empty($datos['nombre_usuario']) || empty($datos['contrasena'])
        ) {
            $mensaje = "Todos los campos son obligatorios";
            $tipo = "error";
        } else {
            // Intentar crear usuario
            $resultado = $usuarioController->crearUsuario($datos);

            if ($resultado['exito']) {
                $mensaje = $resultado['mensaje'];
                $tipo = "exito";
            } else {
                $mensaje = $resultado['mensaje'];
                $tipo = "error";
            }
        }
    }
    // Procesar eliminación
    elseif (isset($_POST['eliminar_usuario'])) {
        $idUsuario = filter_var($_POST['id_usuario'], FILTER_SANITIZE_NUMBER_INT);
        $resultado = $usuarioController->eliminarUsuario($idUsuario);

        if ($resultado['exito']) {
            $mensaje = $resultado['mensaje'];
            $tipo = "exito";
        } else {
            $mensaje = $resultado['mensaje'];
            $tipo = "error";
        }
    }
}

// Obtener usuario para editar
$usuarioEditar = null;
if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $idUsuario = filter_var($_GET['editar'], FILTER_SANITIZE_NUMBER_INT);
    $usuarioEditar = $usuarioController->obtenerUsuarioPorId($idUsuario);
}

// Obtener lista de usuarios
$usuarios = $usuarioController->obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Usuarios - Sistema de Asistencia NFC</title>
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
                        <a href="reportes.php" class="dashboard__menu-enlace">
                            <i class="fas fa-chart-bar"></i>
                            <span class="dashboard__menu-texto">Reportes</span>
                        </a>
                    </li>
                    <li class="dashboard__menu-item">
                        <a href="administrar_usuarios.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
                            <i class="fas fa-user-cog"></i>
                            <span class="dashboard__menu-texto">Usuarios</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <main class="dashboard__contenido">
                <h2 class="dashboard__heading">Administración de Usuarios</h2>

                <?php if (!empty($mensaje)): ?>
                    <div class="alerta alerta-<?php echo $tipo; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard__contenedor">
                    <h3><?php echo $usuarioEditar ? 'Editar Usuario' : 'Crear Nuevo Usuario'; ?></h3>

                    <form class="formulario" method="POST">
                        <?php if ($usuarioEditar): ?>
                            <input type="hidden" name="id_usuario" value="<?php echo $usuarioEditar['id_usuario']; ?>">
                        <?php endif; ?>

                        <div class="formulario__campo">
                            <label for="nombre" class="formulario__label">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" class="formulario__input" value="<?php echo $usuarioEditar ? $usuarioEditar['nombre'] : ''; ?>" required>
                        </div>

                        <div class="formulario__campo">
                            <label for="apellidos" class="formulario__label">Apellidos:</label>
                            <input type="text" id="apellidos" name="apellidos" class="formulario__input" value="<?php echo $usuarioEditar ? $usuarioEditar['apellidos'] : ''; ?>" required>
                        </div>

                        <div class="formulario__campo">
                            <label for="email" class="formulario__label">Email:</label>
                            <input type="email" id="email" name="email" class="formulario__input" value="<?php echo $usuarioEditar ? $usuarioEditar['email'] : ''; ?>" required>
                        </div>

                        <div class="formulario__campo">
                            <label for="nombre_usuario" class="formulario__label">Nombre de Usuario:</label>
                            <input type="text" id="nombre_usuario" name="nombre_usuario" class="formulario__input" value="<?php echo $usuarioEditar ? $usuarioEditar['nombre_usuario'] : ''; ?>" required>
                        </div>

                        <div class="formulario__campo">
                            <label for="contrasena" class="formulario__label">Contraseña:</label>
                            <input type="password" id="contrasena" name="contrasena" class="formulario__input" <?php echo $usuarioEditar ? '' : 'required'; ?>>
                            <?php if ($usuarioEditar): ?>
                                <p class="formulario__texto">Dejar en blanco para mantener la contraseña actual</p>
                            <?php endif; ?>
                        </div>

                        <div class="formulario__campo">
                            <label for="tipo_rol" class="formulario__label">Tipo de Rol:</label>
                            <select id="tipo_rol" name="tipo_rol" class="formulario__select" required>
                                <option value="">-- Seleccionar Rol --</option>
                                <option value="Administrador" <?php echo ($usuarioEditar && $usuarioEditar['tipo_rol'] === 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="Profesor" <?php echo ($usuarioEditar && $usuarioEditar['tipo_rol'] === 'Profesor') ? 'selected' : ''; ?>>Profesor</option>
                                <option value="Coordinador" <?php echo ($usuarioEditar && $usuarioEditar['tipo_rol'] === 'Coordinador') ? 'selected' : ''; ?>>Coordinador</option>
                                <option value="Alumno" <?php echo ($usuarioEditar && $usuarioEditar['tipo_rol'] === 'Alumno') ? 'selected' : ''; ?>>Alumno</option>
                            </select>
                        </div>

                        <div class="formulario__acciones">
                            <?php if ($usuarioEditar): ?>
                                <button type="submit" name="actualizar_usuario" class="formulario__submit">
                                    <i class="fas fa-save"></i> Actualizar Usuario
                                </button>
                                <a href="administrar_usuarios.php" class="boton-rojo">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            <?php else: ?>
                                <button type="submit" name="crear_usuario" class="formulario__submit">
                                    <i class="fas fa-user-plus"></i> Crear Usuario
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <h3 class="dashboard__heading">Listado de Usuarios</h3>

                <div class="dashboard__contenedor">
                    <?php if (empty($usuarios)): ?>
                        <p>No hay usuarios registrados.</p>
                    <?php else: ?>
                        <table class="tabla">
                            <thead class="tabla__thead">
                                <tr>
                                    <th class="tabla__th">ID</th>
                                    <th class="tabla__th">Nombre</th>
                                    <th class="tabla__th">Usuario</th>
                                    <th class="tabla__th">Email</th>
                                    <th class="tabla__th">Rol</th>
                                    <th class="tabla__th">Último Acceso</th>
                                    <th class="tabla__th">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="tabla__tbody">
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr class="tabla__tr">
                                        <td class="tabla__td"><?php echo $usuario['id_usuario']; ?></td>
                                        <td class="tabla__td"><?php echo $usuario['nombre'] . ' ' . $usuario['apellidos']; ?></td>
                                        <td class="tabla__td"><?php echo $usuario['nombre_usuario']; ?></td>
                                        <td class="tabla__td"><?php echo $usuario['email']; ?></td>
                                        <td class="tabla__td"><?php echo $usuario['tipo_rol']; ?></td>
                                        <td class="tabla__td">
                                            <?php echo $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca'; ?>
                                        </td>
                                        <td class="tabla__td tabla__acciones">
                                            <a href="?editar=<?php echo $usuario['id_usuario']; ?>" class="tabla__accion tabla__accion--editar">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>

                                            <!-- No permitir eliminar al propio usuario -->
                                            <?php if ($usuario['id_usuario'] != $_SESSION['id_usuario']): ?>
                                                <form method="POST" onsubmit="return confirm('¿Está seguro de eliminar este usuario?');" style="display: inline;">
                                                    <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                                    <button type="submit" name="eliminar_usuario" class="tabla__accion tabla__accion--eliminar">
                                                        <i class="fas fa-trash-alt"></i> Eliminar
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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