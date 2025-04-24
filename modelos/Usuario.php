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

    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos()
    {
        $sql = "SELECT * FROM usuario ORDER BY id_usuario DESC";
        $resultado = $this->db->query($sql);

        $usuarios = [];
        if ($resultado->num_rows > 0) {
            while ($usuario = $resultado->fetch_assoc()) {
                $usuarios[] = $usuario;
            }
        }

        return $usuarios;
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM usuario WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }

        return null;
    }

    /**
     * Verificar si existe usuario con nombre de usuario o email
     */
    public function existeUsuario($nombreUsuario, $email)
    {
        $sql = "SELECT id_usuario FROM usuario WHERE nombre_usuario = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $nombreUsuario, $email);
        $stmt->execute();

        $resultado = $stmt->get_result();
        return $resultado->num_rows > 0;
    }

    /**
     * Crear un nuevo usuario
     */
    public function crear($datos)
    {
        // Encriptar la contraseña
        $contrasenaHash = hash('sha256', $datos['contrasena']);

        $sql = "INSERT INTO usuario (nombre, apellidos, email, tipo_rol, nombre_usuario, contraseña) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "ssssss",
            $datos['nombre'],
            $datos['apellidos'],
            $datos['email'],
            $datos['tipo_rol'],
            $datos['nombre_usuario'],
            $contrasenaHash
        );

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        return false;
    }

    /**
     * Actualizar un usuario existente
     */
    public function actualizar($id, $datos)
    {
        // Si se proporciona contraseña, actualizarla; de lo contrario, mantener la existente
        if (!empty($datos['contrasena'])) {
            $contrasenaHash = hash('sha256', $datos['contrasena']);

            $sql = "UPDATE usuario SET 
                    nombre = ?, 
                    apellidos = ?, 
                    email = ?, 
                    tipo_rol = ?, 
                    nombre_usuario = ?,
                    contraseña = ? 
                    WHERE id_usuario = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "ssssssi",
                $datos['nombre'],
                $datos['apellidos'],
                $datos['email'],
                $datos['tipo_rol'],
                $datos['nombre_usuario'],
                $contrasenaHash,
                $id
            );
        } else {
            $sql = "UPDATE usuario SET 
                    nombre = ?, 
                    apellidos = ?, 
                    email = ?, 
                    tipo_rol = ?, 
                    nombre_usuario = ? 
                    WHERE id_usuario = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                "sssssi",
                $datos['nombre'],
                $datos['apellidos'],
                $datos['email'],
                $datos['tipo_rol'],
                $datos['nombre_usuario'],
                $id
            );
        }

        return $stmt->execute();
    }

    /**
     * Eliminar un usuario
     */
    public function eliminar($id)
    {
        $sql = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}
