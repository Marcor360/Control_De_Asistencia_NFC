<?php
// controladores/AuthController.php
require_once __DIR__ . '/../modelos/Usuario.php';
require_once __DIR__ . '/../includes/Permisos.php';

class AuthController
{
    private $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();

        // Iniciar la sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Procesar el login
    public function login($nombreUsuario, $contrasena)
    {
        if ($this->usuario->login($nombreUsuario, $contrasena)) {
            // Guardar información en la sesión
            $_SESSION['loggedin'] = true;
            $_SESSION['id_usuario'] = $this->usuario->getId();
            $_SESSION['nombre_usuario'] = $this->usuario->getNombreUsuario();
            $_SESSION['nombre_completo'] = $this->usuario->getNombreCompleto();
            $_SESSION['tipo_rol'] = $this->usuario->getTipoRol();

            return true;
        }

        return false;
    }

    // Verificar si el usuario está autenticado
    public function estaAutenticado()
    {
        return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
    }

    // Cerrar sesión
    public function logout()
    {
        // Destruir todas las variables de sesión
        $_SESSION = array();

        // Destruir la sesión
        session_destroy();
    }

    // Verificar si el usuario tiene permiso para realizar una acción en un módulo específico
    public function tienePermiso($modulo, $accion)
    {
        if (!$this->estaAutenticado()) {
            return false;
        }

        $rol = $_SESSION['tipo_rol'] ?? '';
        return Permisos::tienePermiso($rol, $modulo, $accion);
    }

    // Verificar si el usuario puede acceder a un módulo específico
    public function puedeAccederModulo($modulo)
    {
        if (!$this->estaAutenticado()) {
            return false;
        }

        $rol = $_SESSION['tipo_rol'] ?? '';
        return Permisos::puedeAccederModulo($rol, $modulo);
    }
}
