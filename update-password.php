<?php
// update-password.php
// Este script actualizará la contraseña del usuario admin a un hash SHA-256

// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la base de datos - AJUSTA ESTOS VALORES si es necesario
$host = 'localhost';
$usuario = 'root';
$password = 'MR360??';
$base_datos = 'controlasistenciautc';

echo "<h1>Actualización de contraseña</h1>";

try {
    // Conexión a la base de datos
    $conexion = new mysqli($host, $usuario, $password, $base_datos);

    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }

    // Contraseña actual en texto plano y su hash equivalente
    $contrasena_actual = "Manzana123??";

    // Nueva contraseña y su hash
    $nueva_contrasena = "admin"; // Puedes cambiar esto a la contraseña que desees
    $nuevo_hash = hash('sha256', $nueva_contrasena);

    // Actualizar la contraseña del usuario admin
    $query = "UPDATE usuario SET contraseña = ? WHERE nombre_usuario = 'admin'";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $nuevo_hash);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Contraseña actualizada exitosamente.</p>";
        echo "<p>El usuario 'admin' ahora puede iniciar sesión con la contraseña: <strong>" . $nueva_contrasena . "</strong></p>";
        echo "<p>El hash almacenado es: " . $nuevo_hash . "</p>";
    } else {
        echo "<p style='color:red;'>Error al actualizar la contraseña: " . $stmt->error . "</p>";
    }

    $conexion->close();
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
