<?php
// update-password.php

// En producción, desactiva la visualización de errores en pantalla
ini_set('display_errors', 0);
error_reporting(0);

// --- Configuración de BD (puedes cargar esto desde .env si lo prefieres) ---
$host       = getenv('DB_HOST')  ?: 'localhost';
$dbUser     = getenv('DB_USER')  ?: 'root';
$dbPass     = getenv('DB_PASS')  ?: 'root';
$dbName     = getenv('DB_NAME')  ?: 'controlasistenciautc';

// Conectar a MySQL
$mysqli = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_error) {
    die("<p style='color:red;'>Error de conexión a la base de datos.</p>");
}

$message = '';

// Si llega un POST para actualizar contraseña...
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['user'])) {
    $username    = trim($_POST['user']);
    $newPassword = trim($_POST['new_password'] ?? '');

    if ($newPassword === '') {
        $message = "<p style='color:red;'>La nueva contraseña no puede quedar vacía.</p>";
    } else {
        // Genera hash seguro (bcrypt u otro según tu PHP)
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($hash === false) {
            $message = "<p style='color:red;'>Error generando el hash de la contraseña.</p>";
        } else {
            $sql  = "UPDATE usuario SET contraseña = ? WHERE nombre_usuario = ?";
            $stmt = $mysqli->prepare($sql);
            if (! $stmt) {
                $message = "<p style='color:red;'>Error en prepare(): "
                    . htmlspecialchars($mysqli->error)
                    . "</p>";
            } else {
                $stmt->bind_param('ss', $hash, $username);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $message = "<p style='color:green;'>Contraseña de <strong>"
                        . htmlspecialchars($username)
                        . "</strong> actualizada correctamente.</p>";
                } else {
                    $message = "<p style='color:red;'>Usuario no encontrado o misma contraseña.</p>";
                }
                $stmt->close();
            }
        }
    }
}

// Si no hay ?user=..., mostramos la lista de usuarios
if (empty($_GET['user'])) {
    $result = $mysqli->query("SELECT nombre_usuario FROM usuario");
    $users  = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Seleccionar usuario</title>
        <style>
            body {
                font-family: sans-serif;
                padding: 2rem;
                background: #f5f5f5;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 1rem;
            }

            th,
            td {
                border: 1px solid #ccc;
                padding: .5rem;
            }

            th {
                background: #eee;
            }

            a.button {
                display: inline-block;
                padding: .4rem .8rem;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
        </style>
    </head>

    <body>
        <h1>Usuarios</h1>
        <?php if ($users): ?>
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['nombre_usuario']); ?></td>
                            <td>
                                <a
                                    class="button"
                                    href="?user=<?php echo urlencode($u['nombre_usuario']); ?>">Cambiar contraseña</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron usuarios.</p>
        <?php endif; ?>
    </body>

    </html>
<?php
} else {
    // Si vienen ?user=..., mostramos el formulario de cambio
    $selectedUser = $_GET['user'];
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Cambiar contraseña de <?php echo htmlspecialchars($selectedUser); ?></title>
        <style>
            body {
                font-family: sans-serif;
                padding: 2rem;
                background: #f5f5f5;
            }

            form {
                background: white;
                padding: 1.5rem;
                border-radius: 8px;
                max-width: 400px;
                margin: auto;
            }

            label {
                display: block;
                margin-top: 1rem;
            }

            input {
                width: 100%;
                padding: .5rem;
                margin-top: .25rem;
            }

            button {
                margin-top: 1.5rem;
                padding: .75rem 1rem;
            }

            .back {
                display: block;
                margin-bottom: 1rem;
                text-decoration: none;
                color: #007bff;
            }
        </style>
    </head>

    <body>
        <a class="back" href="update-password.php">&larr; Volver a lista de usuarios</a>
        <h1>Cambiar contraseña de <em><?php echo htmlspecialchars($selectedUser); ?></em></h1>

        <?php if ($message) echo $message; ?>

        <form method="post" action="">
            <input type="hidden" name="user" value="<?php echo htmlspecialchars($selectedUser); ?>" />

            <label for="new_password">Nueva contraseña</label>
            <input
                type="password"
                id="new_password"
                name="new_password"
                placeholder="********"
                required />

            <button type="submit">Actualizar</button>
        </form>
    </body>

    </html>
<?php
}

$mysqli->close();
