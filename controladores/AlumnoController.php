<?php
// controladores/AlumnoController.php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../modelos/Alumno.php';

class AlumnoController
{
    private $alumno;
    private $db;

    public function __construct()
    {
        $this->alumno = new Alumno();
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Obtiene todos los alumnos registrados
     * @return array Lista de alumnos
     */
    public function obtenerTodos()
    {
        return $this->alumno->obtenerTodos();
    }

    /**
     * Busca alumnos por nombre o apellido
     * @param string $termino Término de búsqueda
     * @return array Lista de alumnos que coinciden con la búsqueda
     */
    public function buscar($termino)
    {
        return $this->alumno->buscar($termino);
    }

    /**
     * Obtiene un alumno por su ID
     * @param int $id ID del alumno
     * @return array|false Datos del alumno o false si no existe
     */
    public function obtenerPorId($id)
    {
        return $this->alumno->obtenerPorId($id);
    }

    /**
     * Registra un nuevo alumno
     * @param array $datos Datos del alumno
     * @return array Resultado de la operación
     */
    public function registrar($datos)
    {
        $resultado = [
            'exito' => false,
            'mensaje' => '',
            'id_alumno' => 0
        ];

        // Validar datos
        if (empty($datos['nombre']) || empty($datos['apellidos']) || empty($datos['carrera'])) {
            $resultado['mensaje'] = 'Los campos Nombre, Apellidos y Carrera son obligatorios.';
            return $resultado;
        }

        // Registrar alumno
        $idAlumno = $this->alumno->registrar($datos);

        if ($idAlumno) {
            $resultado['exito'] = true;
            $resultado['mensaje'] = 'Alumno registrado correctamente.';
            $resultado['id_alumno'] = $idAlumno;
        } else {
            $resultado['mensaje'] = 'Error al registrar el alumno. Inténtelo nuevamente.';
        }

        return $resultado;
    }

    /**
     * Alias para el método registrar (solución para el error)
     * @param array $datos Datos del alumno
     * @return bool Resultado de la operación
     */
    public function crear($datos)
    {
        // Adaptamos el resultado del método registrar para devolver solo un booleano
        $resultado = $this->registrar($datos);
        return $resultado['exito'];
    }

    /**
     * Actualiza los datos de un alumno
     * @param int $id ID del alumno
     * @param array $datos Nuevos datos del alumno
     * @return array Resultado de la operación
     */
    public function actualizar($id, $datos)
    {
        $resultado = [
            'exito' => false,
            'mensaje' => ''
        ];

        // Validar datos
        if (empty($datos['nombre']) || empty($datos['apellidos']) || empty($datos['carrera'])) {
            $resultado['mensaje'] = 'Los campos Nombre, Apellidos y Carrera son obligatorios.';
            return $resultado;
        }

        // Verificar si el alumno existe
        $alumnoExistente = $this->alumno->obtenerPorId($id);
        if (!$alumnoExistente) {
            $resultado['mensaje'] = 'El alumno no existe.';
            return $resultado;
        }

        // Actualizar alumno
        if ($this->alumno->actualizar($id, $datos)) {
            $resultado['exito'] = true;
            $resultado['mensaje'] = 'Datos del alumno actualizados correctamente.';
        } else {
            $resultado['mensaje'] = 'Error al actualizar los datos del alumno. Inténtelo nuevamente.';
        }

        return $resultado;
    }

    /**
     * Elimina un alumno
     * @param int $id ID del alumno a eliminar
     * @return array Resultado de la operación
     */
    public function eliminar($id)
    {
        $resultado = [
            'exito' => false,
            'mensaje' => ''
        ];

        // Verificar si el alumno existe
        $alumnoExistente = $this->alumno->obtenerPorId($id);
        if (!$alumnoExistente) {
            $resultado['mensaje'] = 'El alumno no existe.';
            return $resultado;
        }

        // Eliminar alumno
        if ($this->alumno->eliminar($id)) {
            $resultado['exito'] = true;
            $resultado['mensaje'] = 'Alumno eliminado correctamente.';
        } else {
            $resultado['mensaje'] = 'Error al eliminar el alumno. Inténtelo nuevamente.';
        }

        return $resultado;
    }

    /**
     * Actualiza el estatus de pago de un alumno
     * @param int $id ID del alumno
     * @param string $nuevoEstatus Nuevo estatus de pago
     * @return array Resultado de la operación
     */
    public function actualizarEstatusPago($id, $nuevoEstatus)
    {
        $resultado = [
            'exito' => false,
            'mensaje' => ''
        ];

        // Verificar si el estatus es válido
        $estatusValidos = ['Al corriente', 'Pendiente', 'Bloqueado'];
        if (!in_array($nuevoEstatus, $estatusValidos)) {
            $resultado['mensaje'] = 'El estatus de pago no es válido.';
            return $resultado;
        }

        // Verificar si el alumno existe
        $alumnoExistente = $this->alumno->obtenerPorId($id);
        if (!$alumnoExistente) {
            $resultado['mensaje'] = 'El alumno no existe.';
            return $resultado;
        }

        // Actualizar estatus
        if ($this->alumno->actualizarEstatusPago($id, $nuevoEstatus)) {
            $resultado['exito'] = true;
            $resultado['mensaje'] = 'Estatus de pago actualizado correctamente.';
        } else {
            $resultado['mensaje'] = 'Error al actualizar el estatus de pago. Inténtelo nuevamente.';
        }

        return $resultado;
    }
}
