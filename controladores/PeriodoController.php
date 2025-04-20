<?php
// controladores/PeriodoController.php
require_once __DIR__ . '/../modelos/Periodo.php';

class PeriodoController
{
    private $periodo;

    public function __construct()
    {
        $this->periodo = new Periodo();
    }

    /**
     * Obtener todos los periodos
     */
    public function obtenerTodos()
    {
        return $this->periodo->obtenerTodos();
    }

    /**
     * Obtener periodos activos
     */
    public function obtenerActivos()
    {
        return $this->periodo->obtenerActivos();
    }

    /**
     * Obtener un periodo por su ID
     */
    public function obtenerPorId($id)
    {
        return $this->periodo->obtenerPorId($id);
    }

    /**
     * Crear un nuevo periodo
     */
    public function crear($datosPeriodo)
    {
        // Validar datos
        if (empty($datosPeriodo['mes_año']) || empty($datosPeriodo['fecha_inicio']) || 
            empty($datosPeriodo['fecha_fin'])) {
            return false;
        }

        return $this->periodo->crear($datosPeriodo);
    }

    /**
     * Actualizar un periodo existente
     */
    public function actualizar($id, $datosPeriodo)
    {
        // Validar datos
        if (empty($id) || empty($datosPeriodo['mes_año']) || 
            empty($datosPeriodo['fecha_inicio']) || empty($datosPeriodo['fecha_fin'])) {
            return false;
        }

        return $this->periodo->actualizar($id, $datosPeriodo);
    }

    /**
     * Actualizar estado de un periodo
     */
    public function actualizarEstado($id, $estado)
    {
        // Validar datos
        if (empty($id) || empty($estado)) {
            return false;
        }

        // Verificar que el estado sea válido
        $estadosValidos = ['Activo', 'Cerrado'];
        if (!in_array($estado, $estadosValidos)) {
            return false;
        }

        return $this->periodo->actualizarEstado($id, $estado);
    }

    /**
     * Obtener periodo actual
     */
    public function obtenerPeriodoActual()
    {
        return $this->periodo->obtenerPeriodoActual();
    }

    /**
     * Generar periodos para el próximo año
     */
    public function generarPeriodosAnual($año)
    {
        // Validar que el año sea válido
        if (empty($año) || !is_numeric($año) || $año < 2020 || $año > 2050) {
            return false;
        }

        return $this->periodo->generarPeriodosAnual($año);
    }
}