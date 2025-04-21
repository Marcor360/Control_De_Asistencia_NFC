<?php
// modelos/Tarjeta.php
require_once __DIR__ . '/../includes/Database.php';

class Tarjeta {
    private $db;

    public function __construct() {
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Busca una tarjeta por su código NFC
     * @param string $codigo Código único de la tarjeta NFC
     * @return array|false Datos de la tarjeta o false si no existe
     */
    public function buscarPorCodigo($codigo) {
        $sql = "SELECT * FROM tarjeta_nfc WHERE codigo_nfc = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
        
        return false;
    }

    /**
     * Obtiene todas las tarjetas registradas
     * @return array Lista de tarjetas
     */
    public function obtenerTodas() {
        $sql = "SELECT t.*, CONCAT(a.nombre, ' ', a.apellidos) as nombre_alumno 
                FROM tarjeta_nfc t 
                LEFT JOIN alumno a ON t.id_alumno = a.id_alumno
                ORDER BY t.id_tarjeta DESC";
        
        $resultado = $this->db->query($sql);
        $tarjetas = [];
        
        while ($fila = $resultado->fetch_assoc()) {
            $tarjetas[] = $fila;
        }
        
        return $tarjetas;
    }

    /**
     * Registra una nueva tarjeta en el sistema
     * @param string $codigo Código único de la tarjeta NFC
     * @param string $estado Estado inicial de la tarjeta
     * @param int|null $idAlumno ID del alumno asociado (opcional)
     * @return int|bool ID de la tarjeta creada o false si falla
     */
    public function registrar($codigo, $estado = 'Activa', $idAlumno = null) {
        $fechaEmision = date('Y-m-d');
        
        $sql = "INSERT INTO tarjeta_nfc (codigo_nfc, estado, fecha_emision, id_alumno) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssi", $codigo, $estado, $fechaEmision, $idAlumno);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        return false;
    }

    /**
     * Actualiza el estado de una tarjeta
     * @param int $idTarjeta ID de la tarjeta
     * @param string $nuevoEstado Nuevo estado de la tarjeta
     * @return bool Resultado de la operación
     */
    public function actualizarEstado($idTarjeta, $nuevoEstado) {
        $sql = "UPDATE tarjeta_nfc SET estado = ? WHERE id_tarjeta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $nuevoEstado, $idTarjeta);
        
        return $stmt->execute();
    }

    /**
     * Asigna una tarjeta a un alumno
     * @param int $idTarjeta ID de la tarjeta
     * @param int $idAlumno ID del alumno
     * @return bool Resultado de la operación
     */
    public function asignarAlumno($idTarjeta, $idAlumno) {
        $sql = "UPDATE tarjeta_nfc SET id_alumno = ? WHERE id_tarjeta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $idAlumno, $idTarjeta);
        
        return $stmt->execute();
    }

    /**
     * Elimina una tarjeta del sistema
     * @param int $idTarjeta ID de la tarjeta a eliminar
     * @return bool Resultado de la operación
     */
    public function eliminar($idTarjeta) {
        $sql = "DELETE FROM tarjeta_nfc WHERE id_tarjeta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idTarjeta);
        
        return $stmt->execute();
    }
}
