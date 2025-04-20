<?php
// login-debug.php - Coloca este archivo en la raíz del proyecto
// Este archivo te ayudará a diagnosticar problemas con el login

// Mostrar todos los errores para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Login</h1>";

// 1. Verificar la conexión a la base de datos
echo "<h2>1. Verificando conexión a la base de datos</h2>";
require_once 'config/database.php';

try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }
    
    echo "<p style='color:green;'>✅ Conexión exitosa a la base de datos.</p>";
    
    // 2. Verificar si existen usuarios en la base de datos
    echo "<h2>2. Verificando usuarios en la base de datos</h2>";
    
    $query = "SELECT id_usuario, nombre_usuario, nombre, apellidos, email, tipo_rol FROM usuario";
    $resultado = $conexion->query($query);
    
    if ($resultado && $resultado->num_rows > 0) {
        echo "<p style='color:green;'>✅ Se encontraron " . $resultado->num_rows . " usuarios en la base de datos.</p>";
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Apellidos</th><th>Email</th><th>Rol</th></tr>";
        
        while ($usuario = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $usuario['id_usuario'] . "</td>";
            echo "<td>" . $usuario['nombre_usuario'] . "</td>";
            echo "<td>" . $usuario['nombre'] . "</td>";
            echo "<td>" . $usuario['apellidos'] . "</td>";
            echo "<td>" . $usuario['email'] . "</td>";
            echo "<td>" . $usuario['tipo_rol'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color:red;'>⚠️ No se encontraron usuarios en la base de datos. Debes insertar al menos un usuario.</p>";
        
        // Sugerir la creación de un usuario de prueba
        echo "<h3>SQL para crear usuario de prueba:</h3>";
        echo "<pre>
INSERT INTO usuario (nombre, apellidos, email, tipo_rol, nombre_usuario, contraseña) 
VALUES ('Admin', 'Sistema', 'admin@example.com', 'Administrador', 'admin', '" . hash('sha256', 'admin123') . "');
        </pre>";
        
        echo "<p>Esto creará un usuario con:</p>";
        echo "<ul>";
        echo "<li>Usuario: <strong>admin</strong></li>";
        echo "<li>Contraseña: <strong>admin123</strong></li>";
        echo "</ul>";
    }
    
    // 3. Verificar la función de login
    echo "<h2>3. Probador de Login</h2>";
    
    echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
    echo "<div>";
    echo "<label>Usuario: </label>";
    echo "<input type='text' name='test_usuario' required>";
    echo "</div><br>";
    echo "<div>";
    echo "<label>Contraseña: </label>";
    echo "<input type='password' name='test_password' required>";
    echo "</div><br>";
    echo "<button type='submit' name='test_login'>Probar Login</button>";
    echo "</form>";
    
    if (isset($_POST['test_login'])) {
        $usuario = $_POST['test_usuario'];
        $password = $_POST['test_password'];
        
        echo "<h3>Resultados de la prueba:</h3>";
        
        // Generar el hash de la contraseña
        $passwordHash = hash('sha256', $password);
        echo "<p>Hash de contraseña generado: " . $passwordHash . "</p>";
        
        // Consultar usuario
        $query = "SELECT * FROM usuario WHERE nombre_usuario = ? AND contraseña = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ss", $usuario, $passwordHash);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $usuario_encontrado = $resultado->fetch_assoc();
            echo "<p style='color:green;'>✅ Login exitoso para: " . $usuario_encontrado['nombre'] . " " . $usuario_encontrado['apellidos'] . "</p>";
            echo "<p>Los datos de login son correctos. Deberías poder iniciar sesión normalmente.</p>";
        } else {
            echo "<p style='color:red;'>❌ Login fallido. El usuario o la contraseña son incorrectos.</p>";
            
            // Verificar si existe el usuario sin verificar contraseña
            $query = "SELECT * FROM usuario WHERE nombre_usuario = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $usuario_encontrado = $resultado->fetch_assoc();
                echo "<p>Usuario encontrado, pero la contraseña es incorrecta.</p>";
                echo "<p>Hash de contraseña almacenado: " . $usuario_encontrado['contraseña'] . "</p>";
            } else {
                echo "<p>El usuario <strong>" . htmlspecialchars($usuario) . "</strong> no existe en la base de datos.</p>";
            }
        }
    }
    
    $conexion->close();
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Verifica que los datos de conexión en el archivo config/database.php sean correctos:</p>";
    echo "<ul>";
    echo "<li>Host: " . DB_HOST . "</li>";
    echo "<li>Usuario: " . DB_USER . "</li>";
    echo "<li>Contraseña: ******** (oculta)</li>";
    echo "<li>Base de datos: " . DB_NAME . "</li>";
    echo "</ul>";
}

// 4. Verificar archivos relacionados con el login
echo "<h2>4. Verificando archivos de login</h2>";

$archivos_requeridos = [
    'login.php',
    'controladores/AuthController.php',
    'modelos/Usuario.php',
    'includes/Database.php',
    'config/database.php'
];

$todos_existen = true;

echo "<ul>";
foreach ($archivos_requeridos as $archivo) {
    if (file_exists($archivo)) {
        echo "<li style='color:green;'>✅ " . $archivo . " - Existe</li>";
    } else {
        echo "<li style='color:red;'>❌ " . $archivo . " - No encontrado</li>";
        $todos_existen = false;
    }
}
echo "</ul>";

if (!$todos_existen) {
    echo "<p style='color:red;'>⚠️ Faltan algunos archivos necesarios para el login.</p>";
} else {
    echo "<p style='color:green;'>✅ Todos los archivos necesarios existen.</p>";
}

// 5. Recomendaciones
echo "<h2>5. Recomendaciones</h2>";
echo "<ul>";
echo "<li>Si tienes problemas para iniciar sesión, asegúrate de que la base de datos contenga al menos un usuario válido.</li>";
echo "<li>Verifica que la contraseña esté correctamente hasheada con SHA-256.</li>";
echo "<li>Asegúrate de que los permisos de los archivos sean correctos.</li>";
echo "<li>Comprueba que no hay errores de PHP en el log del servidor.</li>";
echo "</ul>";

echo "<p>Una vez que hayas corregido los problemas, elimina este archivo de diagnóstico por seguridad.</p>";
?>
