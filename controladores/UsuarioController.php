<?php
// controladores/UsuarioController.php
require_once __DIR__ . '/../modelos/Usuario.php';

class UsuarioController
{
    private $usuario;

    public function __construct()
    {
        $this->usuario = new Usuario();
    }

    /**
     * Crear un nuevo usuario
     */
    public function crearUsuario($datos)
    {
        $resultado = [
            'exito' => false,
            'mensaje' => '',
            'id_usuario' => 0
        ];

        // Validar que el nombre de usuario y email no existan
        if ($this->usuario->existeUsuario($datos['nombre_usuario'], $datos['email'])) {
            $resultado['mensaje'] = 'Ya existe un usuario con ese nombre de usuario o email.';
            return $resultado;
        }

        // Crear usuario
        $idUsuario = $this->usuario->crear($datos);

        if ($idUsuario) {
            $resultado['exito'] = true;
            $resultado['mensaje'] = 'Usuario creado correctamente.';
            $resultado['id_usuario'] = $idUsuario;

            // Si es un alumno, también podríamos crear un registro en la tabla alumno
            if ($datos['tipo_rol'] === 'Alumno') {
                // Aquí podrías llamar a un método que cree el registro en la tabla alumno
                // y lo vincule con el id_usuario
            }
        } else {
            $resultado['mensaje'] = 'Error al crear el usuario. Inténtelo nuevamente.';
        }

        return $resultado;
    }

    /**
     * Actualizar un usuario existente
     */
    public function actualizarUsuario($idUsuario, $datos)
    {
        $resultado = [
            'exito' => false,
            'mensaje' => ''
        ];

        // Verificar si el usuario existe
        $usuarioExistente = $this->usuario->obtenerPorId($idUsuario);
        if (!$usuarioExistente) {
            $resultado['mensaje'] = 'El usuario no existe.';
            return $resultado;
        }

        // Verificar si el nuevo nombre de usuario o email ya existen (si fueron cambiados)
        if (($datos['nombre_usuario'] !== $usuarioExistente['nombre_usuario'] ||
                $datos['email'] !== $usuarioExistente['email']) &&
            $this->usuario->existeUsuario($datos['nombre_usuario'], $datos['email'])
        ) {

            $resultado['mensaje'] = 'Ya existe un usuario con ese nombre de usuario o email.';
            return $resultado;
        }

        // Actualizar usuario
        if ($this->usuario->actualizar($idUsuario, $datos)) {
            $resultado['exito'] = true;
            $resultado['mensaje'] = 'Usuario actualizado correctamente.';

            // Si el rol ha cambiado a Alumno, verificar si necesitamos crear un registro en alumno
            if ($datos['tipo_rol'] === 'Alumno' && $usuarioExistente['tipo_rol'] !== 'Alumno') {
                // Crear registro en tabla alumno
            }
            // Si el rol ha cambiado desde Alumno, podríamos manejar esa transición también
        } else {
            $resultado['mensaje'] = 'Error al actualizar el usuario. Inténtelo nuevamente.';
        }

        return $resultado;
    }

    /**
     * Eliminar un usuario
     */
    public function eliminarUsuario($idUsuario)
    {
        $resultado = [
            'exito' => false,
            'mensaje' => ''
        ];

        // Verificar si el usuario existe
        $usuarioExistente = $this->usuario->obtenerPorId($idUsuario);
        if (!$usuarioExistente) {
            $resultado['mensaje'] = 'El usuario no existe.';
            return $resultado;
        }

        // No permitir eliminar al usuario de sesión actual
        if ($idUsuario == $_SESSION['id_usuario']) {
            $resultado['mensaje'] = 'No puede eliminar su propio usuario.';
            return $resultado;
        }

        // Eliminar usuario
        if ($this->usuario->eliminar($idUsuario)) {
            $resultado['exito'] = true;
            $resultado['mensaje'] = 'Usuario eliminado correctamente.';
        } else {
            $resultado['mensaje'] = 'Error al eliminar el usuario. Inténtelo nuevamente.';
        }

        return $resultado;
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos()
    {
        return $this->usuario->obtenerTodos();
    }

    /**
     * Obtener un usuario por su ID
     */
    public function obtenerUsuarioPorId($idUsuario)
    {
        return $this->usuario->obtenerPorId($idUsuario);
    }
}
