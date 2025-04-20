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
     * Registrar un nuevo pago
     */
    public function registrarPago($pago)
    {
        // Iniciar transacción
        $this->db->begin_transaction();

        try {
            // Insertar el pago
            $sql = "INSERT INTO pagos (id_alumno, id_periodo, monto, concepto, fecha_pago, metodo_pago, comprobante, estado_pago) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iidsssss", 
                $pago['id_alumno'], 
                $pago['id_periodo'], 
                $pago['monto'], 
                $pago['concepto'], 
                $pago['fecha_pago'], 
                $pago['metodo_pago'], 
                $pago['comprobante'], 
                $pago['estado_pago']
            );

            $resultado = $stmt->execute();

            if (!$resultado) {
                throw new Exception("Error al registrar el pago");
            }

            // Actualizar el estado de pago del alumno si es necesario
            // Solo si el estado de pago es diferente de "Al corriente"
            $sql = "SELECT estatus_pago FROM alumno WHERE id_alumno = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $pago['id_alumno']);
            $stmt->execute();
            $result = $stmt->get_result();
            $alumno = $result->fetch_assoc();

            if ($alumno['estatus_pago'] !== 'Al corriente') {
                $sql = "UPDATE alumno SET estatus_pago = 'Al corriente' WHERE id_alumno = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $pago['id_alumno']);
                $resultado = $stmt->execute();

                if (!$resultado) {
                    throw new Exception("Error al actualizar el estado del alumno");
                }
            }

            // Si todo está bien, confirmar la transacción
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Si hay un error, deshacer la transacción
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Obtener todos los pagos
     */
    public function obtenerTodos()
    {
        $sql = "SELECT p.*, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, pp.mes_año as periodo
                FROM pagos p
                JOIN alumno a ON p.id_alumno = a.id_alumno
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                ORDER BY p.fecha_pago DESC";
        
        $resultado = $this->db->query($sql);
        
        $pagos = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($pago = $resultado->fetch_assoc()) {
                $pagos[] = $pago;
            }
        }
        
        return $pagos;
    }

    /**
     * Obtener un pago por su ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT p.*, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, pp.mes_año as periodo
                FROM pagos p
                JOIN alumno a ON p.id_alumno = a.id_alumno
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                WHERE p.id_pago = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado && $resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        
        return null;
    }

    /**
     * Obtener pagos de un alumno específico
     */
    public function obtenerPagosPorAlumno($idAlumno)
    {
        $sql = "SELECT p.*, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, pp.mes_año as periodo
                FROM pagos p
                JOIN alumno a ON p.id_alumno = a.id_alumno
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                WHERE p.id_alumno = ?
                ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idAlumno);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $pagos = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($pago = $resultado->fetch_assoc()) {
                $pagos[] = $pago;
            }
        }
        
        return $pagos;
    }

    /**
     * Obtener pagos de un periodo específico
     */
    public function obtenerPagosPorPeriodo($idPeriodo)
    {
        $sql = "SELECT p.*, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, pp.mes_año as periodo
                FROM pagos p
                JOIN alumno a ON p.id_alumno = a.id_alumno
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                WHERE p.id_periodo = ?
                ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idPeriodo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $pagos = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($pago = $resultado->fetch_assoc()) {
                $pagos[] = $pago;
            }
        }
        
        return $pagos;
    }

    /**
     * Actualizar el estado de un pago
     */
    public function actualizarEstadoPago($idPago, $estado)
    {
        $sql = "UPDATE pagos SET estado_pago = ? WHERE id_pago = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $estado, $idPago);
        return $stmt->execute();
    }

    /**
     * Buscar pagos por término (nombre de alumno, concepto, etc.)
     */
    public function buscarPagos($termino)
    {
        $busqueda = "%$termino%";
        
        $sql = "SELECT p.*, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, pp.mes_año as periodo
                FROM pagos p
                JOIN alumno a ON p.id_alumno = a.id_alumno
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                WHERE a.nombre LIKE ? OR a.apellidos LIKE ? OR p.concepto LIKE ? OR pp.mes_año LIKE ?
                ORDER BY p.fecha_pago DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", $busqueda, $busqueda, $busqueda, $busqueda);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $pagos = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($pago = $resultado->fetch_assoc()) {
                $pagos[] = $pago;
            }
        }
        
        return $pagos;
    }

    /**
     * Obtener los últimos pagos registrados
     */
    public function obtenerUltimosPagos($limite = 10)
    {
        $sql = "SELECT p.*, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, pp.mes_año as periodo
                FROM pagos p
                JOIN alumno a ON p.id_alumno = a.id_alumno
                JOIN periodo_pago pp ON p.id_periodo = pp.id_periodo
                ORDER BY p.fecha_pago DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $pagos = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($pago = $resultado->fetch_assoc()) {
                $pagos[] = $pago;
            }
        }
        
        return $pagos;
    }

    /**
     * Obtener resumen de pagos para el dashboard
     */
    public function obtenerResumenPagos()
    {
        $resumen = [
            'total_mes' => 0,
            'pendientes' => 0,
            'bloqueados' => 0
        ];

        // Total recaudado en el mes actual
        $sql = "SELECT SUM(monto) as total 
                FROM pagos 
                WHERE MONTH(fecha_pago) = MONTH(CURRENT_DATE()) 
                AND YEAR(fecha_pago) = YEAR(CURRENT_DATE())
                AND estado_pago = 'Pagado'";
        
        $resultado = $this->db->query($sql);
        if ($resultado && $resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $resumen['total_mes'] = $row['total'] ? $row['total'] : 0;
        }

        // Alumnos con pagos pendientes
        $sql = "SELECT COUNT(*) as total FROM alumno WHERE estatus_pago = 'Pendiente'";
        $resultado = $this->db->query($sql);
        if ($resultado && $resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $resumen['pendientes'] = $row['total'];
        }

        // Alumnos bloqueados
        $sql = "SELECT COUNT(*) as total FROM alumno WHERE estatus_pago = 'Bloqueado'";
        $resultado = $this->db->query($sql);
        if ($resultado && $resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $resumen['bloqueados'] = $row['total'];
        }

        return $resumen;
    }
}