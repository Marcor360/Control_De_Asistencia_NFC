<?php
// controladores/PagoController.php
require_once __DIR__ . '/../modelos/Pago.php';

class PagoController
{
    private $pago;

    public function __construct()
    {
        $this->pago = new Pago();
    }

    /**
     * Registrar un nuevo pago
     */
    public function registrarPago($datosPago)
    {
        // Validar datos
        if (empty($datosPago['id_alumno']) || empty($datosPago['id_periodo']) || 
            empty($datosPago['monto']) || empty($datosPago['concepto']) || 
            empty($datosPago['metodo_pago'])) {
            return false;
        }

        // Registrar el pago
        return $this->pago->registrarPago($datosPago);
    }

    /**
     * Obtener todos los pagos
     */
    public function obtenerTodos()
    {
        return $this->pago->obtenerTodos();
    }

    /**
     * Obtener un pago por su ID
     */
    public function obtenerPorId($id)
    {
        return $this->pago->obtenerPorId($id);
    }

    /**
     * Obtener pagos de un alumno específico
     */
    public function obtenerPagosPorAlumno($idAlumno)
    {
        return $this->pago->obtenerPagosPorAlumno($idAlumno);
    }

    /**
     * Obtener pagos de un periodo específico
     */
    public function obtenerPagosPorPeriodo($idPeriodo)
    {
        return $this->pago->obtenerPagosPorPeriodo($idPeriodo);
    }

    /**
     * Actualizar el estado de un pago
     */
    public function actualizarEstadoPago($idPago, $estado)
    {
        return $this->pago->actualizarEstadoPago($idPago, $estado);
    }

    /**
     * Buscar pagos por término (nombre de alumno, concepto, etc.)
     */
    public function buscarPagos($termino)
    {
        return $this->pago->buscarPagos($termino);
    }

    /**
     * Obtener los últimos pagos registrados
     */
    public function obtenerUltimosPagos($limite = 10)
    {
        return $this->pago->obtenerUltimosPagos($limite);
    }

    /**
     * Obtener resumen de pagos para el dashboard
     */
    public function obtenerResumenPagos()
    {
        return $this->pago->obtenerResumenPagos();
    }
}