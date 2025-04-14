<?php

/**
 * Conexión a la base de datos
 */
function conectarDB(): mysqli
{
    $db = new mysqli('localhost', 'root', '', 'controlasistenciautc');

    if (!$db) {
        echo "Error: No se pudo conectar a MySQL.";
        echo "errno de depuración: " . mysqli_connect_errno();
        echo "error de depuración: " . mysqli_connect_error();
        exit;
    }

    // Establecer la codificación de caracteres
    $db->set_charset('utf8');

    return $db;
}
