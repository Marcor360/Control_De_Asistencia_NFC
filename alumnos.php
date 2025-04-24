<?php
// alumnos.php (en la raíz del proyecto)
require_once 'controladores/AuthController.php';
require_once 'controladores/AlumnoController.php';
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

// Inicializar controlador de alumnos
$alumnoController = new AlumnoController();

// Procesar formulario si se ha enviado
$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si es para eliminar un alumno
    if (isset($_POST['eliminar']) && !empty($_POST['id_alumno'])) {
        $id = filter_var($_POST['id_alumno'], FILTER_SANITIZE_NUMBER_INT);
        if ($alumnoController->eliminar($id)) {
            $mensaje = 'Alumno eliminado correctamente';
            $tipo = 'exito';
        } else {
            $mensaje = 'Error al eliminar el alumno';
            $tipo = 'error';
        }
    }
    // Verificar si es para agregar o actualizar un alumno
    elseif (isset($_POST['guardar'])) {
        $id = isset($_POST['id_alumno']) ? filter_var($_POST['id_alumno'], FILTER_SANITIZE_NUMBER_INT) : null;
        $alumno = [
            'nombre' => filter_var($_POST['nombre'], FILTER_SANITIZE_SPECIAL_CHARS),
            'apellidos' => filter_var($_POST['apellidos'], FILTER_SANITIZE_SPECIAL_CHARS),
            'carrera' => filter_var($_POST['carrera'], FILTER_SANITIZE_SPECIAL_CHARS),
            'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            'telefono' => filter_var($_POST['telefono'], FILTER_SANITIZE_SPECIAL_CHARS),
            'fecha_ingreso' => filter_var($_POST['fecha_ingreso'], FILTER_SANITIZE_SPECIAL_CHARS),
            'estatus_pago' => filter_var($_POST['estatus_pago'], FILTER_SANITIZE_SPECIAL_CHARS)
        ];

        if ($id) {
            // Actualizar alumno existente
            if ($alumnoController->actualizar($id, $alumno)) {
                $mensaje = 'Alumno actualizado correctamente';
                $tipo = 'exito';
            } else {
                $mensaje = 'Error al actualizar el alumno';
                $tipo = 'error';
            }
        } else {
            // Agregar nuevo alumno
            if ($alumnoController->crear($alumno)) {
                $mensaje = 'Alumno agregado correctamente';
                $tipo = 'exito';
            } else {
                $mensaje = 'Error al agregar el alumno';
                $tipo = 'error';
            }
        }
    }
}

// Obtener todos los alumnos
$alumnos = $alumnoController->obtenerTodos();

// Comprobar si hay un ID para editar
$alumnoEditar = null;
if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $id = filter_var($_GET['editar'], FILTER_SANITIZE_NUMBER_INT);
    $alumnoEditar = $alumnoController->obtenerPorId($id);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos - Sistema de Asistencia NFC</title>
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
                        <a href="alumnos.php" class="dashboard__menu-enlace dashboard__menu-enlace--activo">
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
                <h2 class="dashboard__heading">Administración de Alumnos</h2>

                <?php if (!empty($mensaje)) : ?>
                    <div class="alerta alerta-<?php echo $tipo; ?>">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard__contenedor">
                    <h3><?php echo $alumnoEditar ? 'Editar Alumno' : 'Agregar Nuevo Alumno'; ?></h3>

                    <form class="alumnos__formulario" method="POST">
                        <?php if ($alumnoEditar) : ?>
                            <input type="hidden" name="id_alumno" value="<?php echo $alumnoEditar['id_alumno']; ?>">
                        <?php endif; ?>

                        <div class="alumnos__campo">
                            <label class="alumnos__label" for="nombre">Nombre:</label>
                            <input class="alumnos__input" type="text" id="nombre" name="nombre" value="<?php echo $alumnoEditar ? $alumnoEditar['nombre'] : ''; ?>" required>
                        </div>

                        <div class="alumnos__campo">
                            <label class="alumnos__label" for="apellidos">Apellidos:</label>
                            <input class="alumnos__input" type="text" id="apellidos" name="apellidos" value="<?php echo $alumnoEditar ? $alumnoEditar['apellidos'] : ''; ?>" required>
                        </div>

                        <div class="alumnos__campo">
                            <label class="alumnos__label" for="carrera">Carrera:</label>
                            <input class="alumnos__input" type="text" id="carrera" name="carrera" value="<?php echo $alumnoEditar ? $alumnoEditar['carrera'] : ''; ?>" required>
                        </div>

                        <div class="alumnos__campo">
                            <label class="alumnos__label" for="email">Email:</label>
                            <input class="alumnos__input" type="email" id="email" name="email" value="<?php echo $alumnoEditar ? $alumnoEditar['email'] : ''; ?>">
                        </div>

                        <div class="alumnos__campo">
                            <label class="alumnos__label" for="telefono">Teléfono:</label>
                            <input class="alumnos__input" type="tel" id="telefono" name="telefono" value="<?php echo $alumnoEditar ? $alumnoEditar['telefono'] : ''; ?>">
                        </div>

                        <div class="alumnos__campo">
                            <label class="alumnos__label" for="fecha_ingreso">Fecha de Ingreso:</label>
                            <input class="alumnos__input" type="date" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo $alumnoEditar ? $alumnoEditar['fecha_ingreso'] : ''; ?>" required>
                        </div>

                        <div class="alumnos__campo">
                            <label class="alumnos__label" for="estatus_pago">Estatus de Pago:</label>
                            <select class="alumnos__input" id="estatus_pago" name="estatus_pago" required>
                                <option value="Al corriente" <?php echo ($alumnoEditar && $alumnoEditar['estatus_pago'] == 'Al corriente') ? 'selected' : ''; ?>>Al corriente</option>
                                <option value="Pendiente" <?php echo ($alumnoEditar && $alumnoEditar['estatus_pago'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="Bloqueado" <?php echo ($alumnoEditar && $alumnoEditar['estatus_pago'] == 'Bloqueado') ? 'selected' : ''; ?>>Bloqueado</option>
                            </select>
                        </div>

                        <button type="submit" name="guardar" class="alumnos__submit">
                            <i class="fas fa-save"></i> <?php echo $alumnoEditar ? 'Actualizar Alumno' : 'Guardar Alumno'; ?>
                        </button>

                        <?php if ($alumnoEditar) : ?>
                            <a href="alumnos.php" class="boton-rojo" style="text-align: center; margin-top: 1rem;">
                                <i class="fas fa-times"></i> Cancelar Edición
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <h3 class="dashboard__heading">Listado de Alumnos</h3>

                <div class="alumnos__grid">
                    <?php if (empty($alumnos)) : ?>
                        <p class="text-center">No hay alumnos registrados aún.</p>
                    <?php else : ?>
                        <?php foreach ($alumnos as $alumno) : ?>
                            <div class="alumnos__card">
                                <h4 class="alumnos__nombre"><?php echo $alumno['nombre'] . ' ' . $alumno['apellidos']; ?></h4>

                                <ul class="alumnos__datos">
                                    <li class="alumnos__dato">
                                        <i class="fas fa-graduation-cap"></i> <?php echo $alumno['carrera']; ?>
                                    </li>
                                    <li class="alumnos__dato">
                                        <i class="fas fa-envelope"></i> <?php echo $alumno['email']; ?>
                                    </li>
                                    <li class="alumnos__dato">
                                        <i class="fas fa-phone"></i> <?php echo $alumno['telefono']; ?>
                                    </li>
                                    <li class="alumnos__dato">
                                        <i class="fas fa-calendar-alt"></i> Ingreso: <?php echo date('d/m/Y', strtotime($alumno['fecha_ingreso'])); ?>
                                    </li>
                                </ul>

                                <div class="alumnos__estado alumnos__estado--<?php echo $alumno['estatus_pago'] === 'Al corriente' ? 'activo' : 'inactivo'; ?>">
                                    <?php echo $alumno['estatus_pago']; ?>
                                </div>

                                <div class="alumnos__acciones">
                                    <a href="?editar=<?php echo $alumno['id_alumno']; ?>" class="alumnos__accion alumnos__accion--editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <form method="POST" onsubmit="return confirm('¿Está seguro de eliminar a este alumno?');">
                                        <input type="hidden" name="id_alumno" value="<?php echo $alumno['id_alumno']; ?>">
                                        <button type="submit" name="eliminar" class="alumnos__accion alumnos__accion--eliminar">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="build/js/bundle.min.js"></script>
</body>

</html>