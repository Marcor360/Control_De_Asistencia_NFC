<?php
// includes/Database.php
require_once __DIR__ . '/../config/database.php';

class Database
{
    private $conexion;
    private static $instancia = null;

    // Constructor privado (patrón Singleton)
    private function __construct()
    {
        try {
            $this->conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($this->conexion->connect_error) {
                throw new Exception("Error de conexión: " . $this->conexion->connect_error);
            }

            $this->conexion->set_charset(DB_CHARSET);
        } catch (Exception $e) {
            die("Error en la conexión a la base de datos: " . $e->getMessage());
        }
    }

    // Método para obtener la instancia de la base de datos (Singleton)
    public static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new Database();
        }
        return self::$instancia;
    }

    // Obtener la conexión
    public function getConexion()
    {
        return $this->conexion;
    }

    // Consulta SQL
    public function query($sql)
    {
        return $this->conexion->query($sql);
    }

    // Preparar consulta
    public function prepare($sql)
    {
        return $this->conexion->prepare($sql);
    }

    // Escapar strings para prevenir SQL Injection
    public function escape($string)
    {
        return $this->conexion->real_escape_string($string);
    }

    // Obtener el último ID insertado
    public function lastId()
    {
        return $this->conexion->insert_id;
    }

    // Cerrar la conexión
    public function close()
    {
        $this->conexion->close();
    }
}
