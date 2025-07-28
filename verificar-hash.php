<?php
// verificar-hash.php - Colócalo en la raíz de tu proyecto
// Este script te permite verificar el hash SHA-256 de diferentes contraseñas

// Mostrar errores para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lista de contraseñas comunes para verificar
$passwords = ['admin', 'admin123', '123456', 'password', ''];

echo "<h1>Verificación de Hashes SHA-256</h1>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Contraseña</th><th>Hash SHA-256</th></tr>";

foreach ($passwords as $password) {
    $hash = hash('sha256', $password);
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($password) . "</code></td>";
    echo "<td><code>" . $hash . "</code></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Verificación personalizada</h2>";
echo "<form method='post'>";
echo "<div>";
echo "<label>Contraseña a verificar: </label>";
echo "<input type='text' name='custom_password'>";
echo "<input type='submit' value='Generar Hash'>";
echo "</div>";
echo "</form>";

if (isset($_POST['custom_password'])) {
    $custom_password = $_POST['custom_password'];
    $custom_hash = hash('sha256', $custom_password);

    echo "<div style='margin-top: 20px; padding: 10px; background-color: #f0f0f0; border: 1px solid #ddd;'>";
    echo "<p>Contraseña: <code>" . htmlspecialchars($custom_password) . "</code></p>";
    echo "<p>Hash SHA-256: <code>" . $custom_hash . "</code></p>";
    echo "</div>";

    echo "<div style='margin-top: 10px;'>";
    echo "<p><strong>SQL para insertar usuario con esta contraseña:</strong></p>";
    echo "<pre>";
    echo "INSERT INTO usuario (nombre, apellidos, email, tipo_rol, nombre_usuario, contraseña)\n";
    echo "VALUES ('Admin', 'Sistema', 'admin@sistema.com', 'Administrador', 'admin', '" . $custom_hash . "');";
    echo "</pre>";
    echo "</div>";
}

// Verificar la función hash
echo "<h2>Información de la función hash</h2>";
echo "<p>Algoritmos disponibles:</p>";
echo "<ul>";
foreach (hash_algos() as $algo) {
    echo "<li>" . $algo . "</li>";
}
echo "</ul>";

echo "<p>PHP Version: " . phpversion() . "</p>";
