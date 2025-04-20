<?php
// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la base de datos - AJUSTA ESTOS VALORES
$host = 'localhost';  // Por lo general localhost o 127.0.0.1
$usuario = 'root';    // Tu usuario de base de datos
$password = 'MR360??'; // Tu contraseña de base de datos
$base_datos = 'controlasistenciautc';

echo "<h1>Prueba de conexión a la base de datos</h1>";

try {
    // Intentar conexión directa
    $conexion = new mysqli($host, $usuario, $password, $base_datos);

    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }

    echo "<p style='color:green;'>Conexión exitosa a la base de datos.</p>";

    // Intentar consultar usuarios
    $query = "SELECT id_usuario, nombre_usuario, nombre, apellidos, contraseña FROM usuario LIMIT 5";
    $resultado = $conexion->query($query);

    if ($resultado && $resultado->num_rows > 0) {
        echo "<p>Se encontraron " . $resultado->num_rows . " usuarios:</p>";
        echo "<table border='1'><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Contraseña (hash)</th></tr>";

        while ($row = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id_usuario'] . "</td>";
            echo "<td>" . $row['nombre_usuario'] . "</td>";
            echo "<td>" . $row['nombre'] . " " . $row['apellidos'] . "</td>";
            echo "<td>" . substr($row['contraseña'], 0, 10) . "...</td>";
            echo "</tr>";
        }

        echo "</table>";

        // Probemos con un usuario específico
        $admin_query = "SELECT * FROM usuario WHERE nombre_usuario = 'admin'";
        $admin_result = $conexion->query($admin_query);

        if ($admin_result && $admin_result->num_rows > 0) {
            $admin = $admin_result->fetch_assoc();
            echo "<p>Usuario admin encontrado:</p>";
            echo "<ul>";
            echo "<li>ID: " . $admin['id_usuario'] . "</li>";
            echo "<li>Nombre: " . $admin['nombre'] . " " . $admin['apellidos'] . "</li>";
            echo "<li>Hash de contraseña: " . $admin['contraseña'] . "</li>";
            echo "</ul>";

            // Verificar hash de contraseña
            $test_password = 'admin';
            $test_hash = hash('sha256', $test_password);

            echo "<p>Probando contraseña 'admin':</p>";
            echo "<ul>";
            echo "<li>Hash generado: " . $test_hash . "</li>";
            echo "<li>¿Coincide con la BD? " . ($test_hash === $admin['contraseña'] ? "SÍ" : "NO") . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color:red;'>No se encontró el usuario 'admin'.</p>";
        }
    } else {
        echo "<p style='color:red;'>No se encontraron usuarios en la base de datos.</p>";
    }

    $conexion->close();
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

// Mostrar información sobre PHP y extensiones
echo "<h2>Información de PHP</h2>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";

echo "<h3>Extensiones cargadas:</h3>";
echo "<ul>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    echo "<li>" . $ext . "</li>";
}
echo "</ul>";

// Mostrar información de phpinfo
echo "<h2>Información detallada de PHP</h2>";
phpinfo();
