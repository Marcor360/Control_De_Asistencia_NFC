<?php
// modelos/Periodo.php
require_once __DIR__ . '/../includes/Database.php';

class Periodo
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Obtener todos los periodos
     */
    public function obtenerTodos()
    {
        $sql = "SELECT * FROM periodo_pago ORDER BY fecha_inicio DESC";
        $resultado = $this->db->query($sql);
        
        $periodos = [];
        if ($resultado->num_rows > 0) {
            while ($periodo = $resultado->fetch_assoc()) {
                $periodos[] = $periodo;
            }
        }
        
        return $periodos;
    }

    /**
     * Obtener periodos activos
     */
    public function obtenerActivos()
    {
        $sql = "SELECT * FROM periodo_pago WHERE estatus = 'Activo' ORDER BY fecha_inicio DESC";
        $resultado = $this->db->query($sql);
        
        $periodos = [];
        if ($resultado->num_rows > 0) {
            while ($periodo = $resultado->fetch_assoc()) {
                $periodos[] = $periodo;
            }
        }
        
        return $periodos;
    }

    /**
     * Obtener un periodo por su ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM periodo_pago WHERE id_periodo = ?";
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
     * Crear un nuevo periodo
     */
    public function crear($periodo)
    {
        // Verificar que el mes_año no exista
        if ($this->existeMesAño($periodo['mes_año'])) {
            return false;
        }

        $sql = "INSERT INTO periodo_pago (mes_año, fecha_inicio, fecha_fin, estatus) 
                VALUES (?, ?, ?, ?)";
        
        $estatus = 'Activo'; // Por defecto, un nuevo periodo es activo
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", 
            $periodo['mes_año'], 
            $periodo['fecha_inicio'], 
            $periodo['fecha_fin'], 
            $estatus
        );

        return $stmt->execute();
    }

    /**
     * Actualizar un periodo existente
     */
    public function actualizar($id, $periodo)
    {
        // Verificar que el periodo exista
        $periodoActual = $this->obtenerPorId($id);
        if (!$periodoActual) {
            return false;
        }

        // Verificar que el mes_año no exista (a menos que sea el mismo periodo)
        if ($periodoActual['mes_año'] !== $periodo['mes_año'] && $this->existeMesAño($periodo['mes_año'])) {
            return false;
        }

        $sql = "UPDATE periodo_pago SET 
                mes_año = ?, 
                fecha_inicio = ?, 
                fecha_fin = ?, 
                estatus = ? 
                WHERE id_periodo = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssi", 
            $periodo['mes_año'], 
            $periodo['fecha_inicio'], 
            $periodo['fecha_fin'], 
            $periodo['estatus'],
            $id
        );

        return $stmt->execute();
    }

    /**
     * Actualizar estado de un periodo
     */
    public function actualizarEstado($id, $estado)
    {
        $sql = "UPDATE periodo_pago SET estatus = ? WHERE id_periodo = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $estado, $id);
        
        return $stmt->execute();
    }

    /**
     * Verificar si existe un mes_año
     */
    private function existeMesAño($mesAño)
    {
        $sql = "SELECT id_periodo FROM periodo_pago WHERE mes_año = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $mesAño);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        return $resultado->num_rows > 0;
    }

    /**
     * Obtener periodo actual (el que incluye la fecha actual)
     */
    public function obtenerPeriodoActual()
    {
        $fechaActual = date('Y-m-d');
        
        $sql = "SELECT * FROM periodo_pago 
                WHERE ? BETWEEN fecha_inicio AND fecha_fin
                AND estatus = 'Activo'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $fechaActual);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        
        // Si no hay un periodo que incluya la fecha actual, buscar el siguiente periodo activo
        $sql = "SELECT * FROM periodo_pago 
                WHERE fecha_inicio > ? 
                AND estatus = 'Activo' 
                ORDER BY fecha_inicio ASC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $fechaActual);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        
        return null;
    }

    /**
     * Generar periodos para un año específico
     */
    public function generarPeriodosAnual($año)
    {
        $meses = [
            1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR', 
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO', 
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC'
        ];
        
        $periodos = [];
        
        // Iniciar transacción
        $this->db->begin_transaction();
        
        try {
            foreach ($meses as $numMes => $nombreMes) {
                $mesAño = $nombreMes . '-' . $año;
                
                // Verificar si ya existe
                if ($this->existeMesAño($mesAño)) {
                    continue;
                }
                
                // Crear fechas de inicio y fin del mes
                $fechaInicio = $año . '-' . str_pad($numMes, 2, '0', STR_PAD_LEFT) . '-01';
                $fechaFin = date('Y-m-t', strtotime($fechaInicio));
                
                // Determinar estatus (activo o cerrado)
                $estatus = 'Activo';
                if (strtotime($fechaFin) < time()) {
                    $estatus = 'Cerrado'; // Si la fecha fin ya pasó, el periodo está cerrado
                }
                
                // Crear el periodo
                $periodo = [
                    'mes_año' => $mesAño,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ];
                
                $sql = "INSERT INTO periodo_pago (mes_año, fecha_inicio, fecha_fin, estatus) 
                        VALUES (?, ?, ?, ?)";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ssss", 
                    $periodo['mes_año'], 
                    $periodo['fecha_inicio'], 
                    $periodo['fecha_fin'], 
                    $estatus
                );
                
                if ($stmt->execute()) {
                    $periodos[] = [
                        'id_periodo' => $stmt->insert_id,
                        'mes_año' => $periodo['mes_año'],
                        'fecha_inicio' => $periodo['fecha_inicio'],
                        'fecha_fin' => $periodo['fecha_fin'],
                        'estatus' => $estatus
                    ];
                } else {
                    throw new Exception("Error al crear el periodo: " . $stmt->error);
                }
            }
            
            // Confirmar la transacción
            $this->db->commit();
            return $periodos;
        } catch (Exception $e) {
            // Deshacer la transacción
            $this->db->rollback();
            return false;
        }
    }
}