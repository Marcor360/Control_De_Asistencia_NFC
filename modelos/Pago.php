<?php
// modelos/Pago.php
require_once __DIR__ . '/../includes/Database.php';

class Pago
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Registra un nuevo pago
     * @param array $datos Datos del pago
     * @return int|bool ID del pago creado o false si falla
     */
    public function registrarPago($datos)
    {
        $sql = "INSERT INTO pagos (id_alumno, id_periodo, monto, concepto, fecha_pago, metodo_pago, comprobante, estado_pago) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $fechaPago = $datos['fecha_pago'] ?? date('Y-m-d H:i:s');
        $estadoPago = $datos['estado_pago'] ?? 'Pagado';
        $comprobante = $datos['comprobante'] ?? '';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "iidssss",
            $datos['id_alumno'],
            $datos['id_periodo'],
            $datos['monto'],
            $datos['concepto'],
            $fechaPago,
            $datos['metodo_pago'],
            $comprobante,
            $estadoPago
        );

        if ($stmt->execute()) {
            // Si el pago se registra como pagado, actualizar estatus del alumno
            if ($estadoPago === 'Pagado') {
                $this->actualizarEstatusAlumno($datos['id_alumno']);
            }

            return $this->db->insert_id;
        }

        return false;
    }

    /**
     * Obtiene todos los pagos
     * @return array Lista de pagos
     */
    public function obtenerTodos()
    {
        $sql = "SELECT p.*, pp.mes_año as periodo, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo 
                FROM pagos p
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                JOIN alumno a ON p.id_alumno = a.id_alumno
                ORDER BY p.fecha_pago DESC";

        $resultado = $this->db->query($sql);
        $pagos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $pagos[] = $fila;
        }

        return $pagos;
    }

    /**
     * Obtiene un pago por su ID
     * @param int $id ID del pago
     * @return array|false Datos del pago o false si no existe
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT p.*, pp.mes_año as periodo, CONCAT(a.nombre, ' ', a.apellidos) as nombre_alumno 
                FROM pagos p
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                JOIN alumno a ON p.id_alumno = a.id_alumno
                WHERE p.id_pago = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }

        return false;
    }

    /**
     * Obtiene pagos por alumno
     * @param int $idAlumno ID del alumno
     * @param int $limite Número de pagos a obtener
     * @param int $offset Desplazamiento para paginación
     * @return array Lista de pagos
     */
    public function obtenerPagosPorAlumno($idAlumno, $limite = 10, $offset = 0)
    {
        $sql = "SELECT p.*, pp.mes_año as periodo 
                FROM pagos p
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                WHERE p.id_alumno = ? 
                ORDER BY p.fecha_pago DESC 
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $idAlumno, $limite, $offset);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $pagos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $pagos[] = $fila;
        }

        return $pagos;
    }

    /**
     * Obtiene pagos por período
     * @param int $idPeriodo ID del período
     * @return array Lista de pagos
     */
    public function obtenerPagosPorPeriodo($idPeriodo)
    {
        $sql = "SELECT p.*, CONCAT(a.nombre, ' ', a.apellidos) as nombre_alumno 
                FROM pagos p
                JOIN alumno a ON p.id_alumno = a.id_alumno
                WHERE p.id_periodo = ? 
                ORDER BY p.fecha_pago DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idPeriodo);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $pagos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $pagos[] = $fila;
        }

        return $pagos;
    }

    /**
     * Actualiza el estado de un pago
     * @param int $idPago ID del pago
     * @param string $nuevoEstado Nuevo estado del pago
     * @return bool Resultado de la operación
     */
    public function actualizarEstadoPago($idPago, $nuevoEstado)
    {
        $sql = "UPDATE pagos SET estado_pago = ? WHERE id_pago = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $nuevoEstado, $idPago);

        if ($stmt->execute()) {
            // Obtener el ID del alumno para actualizar su estatus
            $pago = $this->obtenerPorId($idPago);

            if ($pago) {
                $this->actualizarEstatusAlumno($pago['id_alumno']);
            }

            return true;
        }

        return false;
    }

    /**
     * Actualiza el estatus de pago de un alumno
     * @param int $idAlumno ID del alumno
     * @return bool Resultado de la operación
     */
    private function actualizarEstatusAlumno($idAlumno)
    {
        // Verificar si hay pagos pendientes
        $sql = "SELECT COUNT(*) as pendientes FROM pagos WHERE id_alumno = ? AND estado_pago = 'Pendiente'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idAlumno);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();

        // Determinar el nuevo estatus
        $nuevoEstatus = ($fila['pendientes'] > 0) ? 'Pendiente' : 'Al corriente';

        // Actualizar el estatus del alumno
        $sqlUpdate = "UPDATE alumno SET estatus_pago = ? WHERE id_alumno = ?";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->bind_param("si", $nuevoEstatus, $idAlumno);

        return $stmtUpdate->execute();
    }

    /**
     * Busca pagos por término (nombre de alumno, concepto, etc.)
     * @param string $termino Término de búsqueda
     * @return array Lista de pagos
     */
    public function buscarPagos($termino)
    {
        $busqueda = "%$termino%";

        $sql = "SELECT p.*, pp.mes_año as periodo, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo 
                FROM pagos p
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                JOIN alumno a ON p.id_alumno = a.id_alumno
                WHERE a.nombre LIKE ? OR a.apellidos LIKE ? OR p.concepto LIKE ?
                ORDER BY p.fecha_pago DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $busqueda, $busqueda, $busqueda);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $pagos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $pagos[] = $fila;
        }

        return $pagos;
    }

    /**
     * Obtiene los últimos pagos registrados
     * @param int $limite Número de pagos a obtener
     * @return array Lista de pagos
     */
    public function obtenerUltimosPagos($limite = 10)
    {
        $sql = "SELECT p.*, pp.mes_año as periodo, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo 
                FROM pagos p
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                JOIN alumno a ON p.id_alumno = a.id_alumno
                ORDER BY p.fecha_pago DESC 
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $pagos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $pagos[] = $fila;
        }

        return $pagos;
    }

    /**
     * Obtiene un resumen de pagos para el dashboard
     * @return array Resumen de pagos
     */
    public function obtenerResumenPagos()
    {
        $resumen = [
            'total_mes' => 0,
            'pendientes' => 0,
            'bloqueados' => 0
        ];

        // Obtener el total recaudado en el mes actual
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');

        $sql = "SELECT SUM(monto) as total FROM pagos 
                WHERE fecha_pago BETWEEN ? AND ? 
                AND estado_pago = 'Pagado'";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $inicioMes, $finMes);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $resumen['total_mes'] = $fila['total'] ?? 0;
        }

        // Obtener el número de pagos pendientes
        $sql = "SELECT COUNT(*) as pendientes FROM pagos WHERE estado_pago = 'Pendiente'";
        $resultado = $this->db->query($sql);

        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $resumen['pendientes'] = $fila['pendientes'];
        }

        // Obtener el número de alumnos bloqueados
        $sql = "SELECT COUNT(*) as bloqueados FROM alumno WHERE estatus_pago = 'Bloqueado'";
        $resultado = $this->db->query($sql);

        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $resumen['bloqueados'] = $fila['bloqueados'];
        }

        return $resumen;
    }

    /**
     * Obtiene pagos por rango de fechas
     * @param string $fechaInicio Fecha inicial (YYYY-MM-DD)
     * @param string $fechaFin Fecha final (YYYY-MM-DD)
     * @param int|null $idAlumno ID del alumno (opcional)
     * @return array Lista de pagos
     */
    public function obtenerPorFechas($fechaInicio, $fechaFin, $idAlumno = null)
    {
        // Ajustar las fechas para incluir todo el día
        $fechaInicio = $fechaInicio . ' 00:00:00';
        $fechaFin = $fechaFin . ' 23:59:59';

        if ($idAlumno) {
            // Filtrar por alumno si se especifica
            $sql = "SELECT p.*, pp.mes_año as periodo, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, 
                    a.id_alumno, a.estatus_pago as estatus_general 
                    FROM pagos p
                    JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                    JOIN alumno a ON p.id_alumno = a.id_alumno
                    WHERE p.fecha_pago BETWEEN ? AND ? 
                    AND p.id_alumno = ?
                    ORDER BY p.fecha_pago DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssi", $fechaInicio, $fechaFin, $idAlumno);
        } else {
            // Consulta sin filtro de alumno
            $sql = "SELECT p.*, pp.mes_año as periodo, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, 
                    a.id_alumno, a.estatus_pago as estatus_general 
                    FROM pagos p
                    JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                    JOIN alumno a ON p.id_alumno = a.id_alumno
                    WHERE p.fecha_pago BETWEEN ? AND ? 
                    ORDER BY p.fecha_pago DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        $pagos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $pagos[] = $fila;
        }

        return $pagos;
    }
}
