<?php
// controladores/AlumnoController.php
require_once __DIR__ . '/../modelos/Alumno.php';

class AlumnoController
{
    private $alumno;

    public function __construct()
    {
        $this->alumno = new Alumno();
    }

    /**
     * Obtener todos los alumnos
     */
    public function obtenerTodos()
    {
        return $this->alumno->obtenerTodos();
    }

    /**
     * Obtener un alumno por su ID
     */
    public function obtenerPorId($id)
    {
        return $this->alumno->obtenerPorId($id);
    }

    /**
     * Crear un nuevo alumno
     */
    public function crear($datos)
    {
        // Validar datos
        if (empty($datos['nombre']) || empty($datos['apellidos']) || empty($datos['carrera'])) {
            return false;
        }

        return $this->alumno->crear($datos);
    }

    /**
     * Actualizar un alumno existente
     */
    public function actualizar($id, $datos)
    {
        // Validar datos
        if (empty($id) || empty($datos['nombre']) || empty($datos['apellidos']) || empty($datos['carrera'])) {
            return false;
        }

        return $this->alumno->actualizar($id, $datos);
    }

    /**
     * Eliminar un alumno
     */
    public function eliminar($id)
    {
        if (empty($id)) {
            return false;
        }

        return $this->alumno->eliminar($id);
    }

    /**
     * Buscar alumnos por nombre o apellido
     */
    public function buscar($termino)
    {
        return $this->alumno->buscar($termino);
    }

    /**
     * Obtener estadísticas de alumnos para el dashboard
     */
    public function obtenerEstadisticas()
    {
        return $this->alumno->obtenerEstadisticas();
    }
}