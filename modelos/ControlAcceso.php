<?php
// modelos/ControlAcceso.php
require_once __DIR__ . '/../includes/Database.php';

class ControlAcceso {
    private $db;

    public function __construct() {
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Registra un nuevo acceso
     * @param array $datos Datos del acceso
     * @return int|bool ID del acceso creado o false si falla
     */
    public function registrar($datos) {
        $sql = "INSERT INTO control_acceso (id_tarjeta, fecha_hora, tipo_acceso, permitido, motivo_rechazo, dispositivo, ubicacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $fechaHora = $datos['fecha_hora'] ?? date('Y-m-d H:i:s');
        $permitido = isset($datos['permitido']) ? (int)$datos['permitido'] : 1;
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ississs", 
            $datos['id_tarjeta'], 
            $fechaHora, 
            $datos['tipo_acceso'],
            $permitido,
            $datos['motivo_rechazo'],
            $datos['dispositivo'],
            $datos['ubicacion']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }

    /**
     * Obtiene un acceso por su ID
     * @param int $id ID del acceso
     * @return array|false Datos del acceso o false si no existe
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM control_acceso WHERE id_acceso = ?";
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
     * Obtiene accesos por tarjeta
     * @param int $idTarjeta ID de la tarjeta
     * @param int $limite Número de accesos a obtener
     * @param int $offset Desplazamiento para paginación
     * @return array Lista de accesos
     */
    public function obtenerPorTarjeta($idTarjeta, $limite = 10, $offset = 0) {
        $sql = "SELECT * FROM control_acceso 
                WHERE id_tarjeta = ? 
                ORDER BY fecha_hora DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $idTarjeta, $limite, $offset);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        $accesos = [];
        
        while ($fila = $resultado->fetch_assoc()) {
            $accesos[] = $fila;
        }
        
        return $accesos;
    }

    /**
     * Obtiene accesos por alumno
     * @param int $idAlumno ID del alumno
     * @param int $limite Número de accesos a obtener
     * @param int $offset Desplazamiento para paginación
     * @return array Lista de accesos
     */
    public function obtenerPorAlumno($idAlumno, $limite = 10, $offset = 0) {
        $sql = "SELECT ca.* 
                FROM control_acceso ca
                JOIN tarjeta_nfc t ON ca.id_tarjeta = t.id_tarjeta
                WHERE t.id_alumno = ? 
                ORDER BY ca.fecha_hora DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $idAlumno, $limite, $offset);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        $accesos = [];
        
        while ($fila = $resultado->fetch_assoc()) {
            $accesos[] = $fila;
        }
        
        return $accesos;
    }

    /**
     * Obtiene accesos por rango de fechas
     * @param string $fechaInicio Fecha inicial (YYYY-MM-DD)
     * @param string $fechaFin Fecha final (YYYY-MM-DD)
     * @param int|null $idAlumno ID del alumno (opcional)
     * @return array Lista de accesos
     */
    public function obtenerPorFechas($fechaInicio, $fechaFin, $idAlumno = null) {
        // Ajustar las fechas para incluir todo el día
        $fechaInicio = $fechaInicio . ' 00:00:00';
        $fechaFin = $fechaFin . ' 23:59:59';
        
        if ($idAlumno) {
            // Filtrar por alumno si se especifica
            $sql = "SELECT ca.*, t.codigo_nfc, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, 
                    a.id_alumno, a.carrera, a.estatus_pago 
                    FROM control_acceso ca
                    JOIN tarjeta_nfc t ON ca.id_tarjeta = t.id_tarjeta
                    JOIN alumno a ON t.id_alumno = a.id_alumno
                    WHERE ca.fecha_hora BETWEEN ? AND ? 
                    AND a.id_alumno = ?
                    ORDER BY ca.fecha_hora DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssi", $fechaInicio, $fechaFin, $idAlumno);
        } else {
            // Consulta sin filtro de alumno
            $sql = "SELECT ca.*, t.codigo_nfc, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, 
                    a.id_alumno, a.carrera, a.estatus_pago 
                    FROM control_acceso ca
                    JOIN tarjeta_nfc t ON ca.id_tarjeta = t.id_tarjeta
                    JOIN alumno a ON t.id_alumno = a.id_alumno
                    WHERE ca.fecha_hora BETWEEN ? AND ? 
                    ORDER BY ca.fecha_hora DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        $accesos = [];
        
        while ($fila = $resultado->fetch_assoc()) {
            $accesos[] = $fila;
        }
        
        return $accesos;
    }

    /**
     * Obtiene los últimos accesos registrados
     * @param int $limite Número de accesos a obtener
     * @param int $offset Desplazamiento para paginación
     * @return array Lista de accesos
     */
    public function obtenerUltimos($limite = 10, $offset = 0) {
        $sql = "SELECT ca.*, t.codigo_nfc, CONCAT(a.nombre, ' ', a.apellidos) as nombre_completo, 
                a.id_alumno, a.carrera, a.estatus_pago 
                FROM control_acceso ca
                JOIN tarjeta_nfc t ON ca.id_tarjeta = t.id_tarjeta
                JOIN alumno a ON t.id_alumno = a.id_alumno
                ORDER BY ca.fecha_hora DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $limite, $offset);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        $accesos = [];
        
        while ($fila = $resultado->fetch_assoc()) {
            $accesos[] = $fila;
        }
        
        return $accesos;
    }

    /**
     * Elimina un registro de acceso
     * @param int $id ID del acceso a eliminar
     * @return bool Resultado de la operación
     */
    public function eliminar($id) {
        $sql = "DELETE FROM control_acceso WHERE id_acceso = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
}
