<?php
// modelos/Usuario.php
require_once __DIR__ . '/../includes/Database.php';

class Usuario
{
    private $db;
    private $id;
    private $nombre;
    private $apellidos;
    private $email;
    private $tipoRol;
    private $nombreUsuario;
    private $contrasena;

    public function __construct()
    {
        $this->db = Database::getInstancia()->getConexion();
    }

    // Verificar login
    public function login($nombreUsuario, $contrasena)
    {
        // Encriptar la contraseña con SHA-256 (como está en la base de datos)
        $contrasenaHash = hash('sha256', $contrasena);

        $sql = "SELECT * FROM usuario WHERE nombre_usuario = ? AND contraseña = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $nombreUsuario, $contrasenaHash);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();

            // Guardar datos del usuario
            $this->id = $usuario['id_usuario'];
            $this->nombre = $usuario['nombre'];
            $this->apellidos = $usuario['apellidos'];
            $this->email = $usuario['email'];
            $this->tipoRol = $usuario['tipo_rol'];
            $this->nombreUsuario = $usuario['nombre_usuario'];

            // Actualizar último acceso
            $this->actualizarUltimoAcceso();

            return true;
        }

        return false;
    }

    // Actualizar último acceso
    private function actualizarUltimoAcceso()
    {
        $sql = "UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getNombre()
    {
        return $this->nombre;
    }
    public function getApellidos()
    {
        return $this->apellidos;
    }
    public function getNombreCompleto()
    {
        return $this->nombre . ' ' . $this->apellidos;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getTipoRol()
    {
        return $this->tipoRol;
    }
    public function getNombreUsuario()
    {
        return $this->nombreUsuario;
    }
}
