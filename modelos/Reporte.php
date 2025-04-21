<?php
// modelos/Reporte.php
require_once __DIR__ . '/../includes/Database.php';

class Reporte {
    private $db;

    public function __construct() {
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Registra un nuevo reporte en la base de datos
     * @param int $idUsuario ID del usuario que genera el reporte
     * @param string $tipoReporte Tipo de reporte (Asistencia, Pagos, Accesos, General)
     * @param string $fechaInicio Fecha de inicio del periodo
     * @param string $fechaFin Fecha de fin del periodo
     * @param string $parametros Parámetros del reporte en formato JSON
     * @return int|bool ID del reporte creado o false si falla
     */
    public function registrarReporte($idUsuario, $tipoReporte, $fechaInicio, $fechaFin, $parametros) {
        $fechaGeneracion = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO reporte (id_usuario, fecha_generacion, tipo_reporte, periodo_inicio, periodo_fin, parametros) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isssss", 
            $idUsuario, 
            $fechaGeneracion, 
            $tipoReporte,
            $fechaInicio,
            $fechaFin,
            $parametros
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }

    /**
     * Actualiza el nombre del archivo generado para un reporte
     * @param int $idReporte ID del reporte
     * @param string $nombreArchivo Nombre del archivo generado
     * @return bool Resultado de la operación
     */
    public function actualizarArchivoGenerado($idReporte, $nombreArchivo) {
        $sql = "UPDATE reporte SET archivo_generado = ? WHERE id_reporte = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $nombreArchivo, $idReporte);
        
        return $stmt->execute();
    }

    /**
     * Obtiene un reporte por su ID
     * @param int $idReporte ID del reporte
     * @return array|false Datos del reporte o false si no existe
     */
    public function obtenerPorId($idReporte) {
        $sql = "SELECT r.*, u.nombre, u.apellidos 
                FROM reporte r 
                JOIN usuario u ON r.id_usuario = u.id_usuario 
                WHERE r.id_reporte = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idReporte);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        
        return false;
    }

    /**
     * Obtiene los reportes generados por un usuario
     * @param int $idUsuario ID del usuario
     * @param int $limite Número de reportes a obtener
     * @param int $offset Desplazamiento para paginación
     * @return array Lista de reportes
     */
    public function obtenerPorUsuario($idUsuario, $limite = 10, $offset = 0) {
        $sql = "SELECT * FROM reporte WHERE id_usuario = ? ORDER BY fecha_generacion DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $idUsuario, $limite, $offset);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        $reportes = [];
        
        while ($fila = $resultado->fetch_assoc()) {
            $reportes[] = $fila;
        }
        
        return $reportes;
    }

    /**
     * Obtiene los reportes de un tipo específico
     * @param string $tipoReporte Tipo de reporte (Asistencia, Pagos, Accesos, General)
     * @param int $limite Número de reportes a obtener
     * @param int $offset Desplazamiento para paginación
     * @return array Lista de reportes
     */
    public function obtenerPorTipo($tipoReporte, $limite = 10, $offset = 0) {
        $sql = "SELECT r.*, u.nombre, u.apellidos 
                FROM reporte r 
                JOIN usuario u ON r.id_usuario = u.id_usuario 
                WHERE r.tipo_reporte = ? 
                ORDER BY r.fecha_generacion DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sii", $tipoReporte, $limite, $offset);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        $reportes = [];
        
        while ($fila = $resultado->fetch_assoc()) {
            $reportes[] = $fila;
        }
        
        return $reportes;
    }

    /**
     * Elimina un reporte
     * @param int $idReporte ID del reporte a eliminar
     * @return bool Resultado de la operación
     */
    public function eliminar($idReporte) {
        // Primero, obtener el nombre del archivo para eliminarlo si existe
        $reporte = $this->obtenerPorId($idReporte);
        
        if ($reporte && !empty($reporte['archivo_generado'])) {
            $rutaArchivo = __DIR__ . '/../reportes/' . $reporte['archivo_generado'];
            
            // Eliminar el archivo si existe
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
        }
        
        // Eliminar el registro de la base de datos
        $sql = "DELETE FROM reporte WHERE id_reporte = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idReporte);
        
        return $stmt->execute();
    }

    /**
     * Elimina los detalles asociados a un reporte
     * @param int $idReporte ID del reporte
     * @return bool Resultado de la operación
     */
    public function eliminarDetalles($idReporte) {
        $sql = "DELETE FROM detalle_reporte WHERE id_reporte = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idReporte);
        
        return $stmt->execute();
    }

    /**
     * Agrega un detalle a un reporte
     * @param int $idReporte ID del reporte
     * @param string $tipoReferencia Tipo de referencia (Asistencia, Acceso, Pago)
     * @param int $idReferencia ID de la referencia
     * @return int|bool ID del detalle creado o false si falla
     */
    public function agregarDetalle($idReporte, $tipoReferencia, $idReferencia) {
        $sql = "INSERT INTO detalle_reporte (id_reporte, tipo_referencia, id_referencia) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isi", $idReporte, $tipoReferencia, $idReferencia);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }
}
