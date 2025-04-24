<?php
// modelos/Alumno.php
require_once __DIR__ . '/../includes/Database.php';

class Alumno
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Obtener todos los alumnos
     */
    public function obtenerTodos()
    {
        $sql = "SELECT * FROM alumno ORDER BY apellidos, nombre";
        $resultado = $this->db->query($sql);

        $alumnos = [];
        if ($resultado->num_rows > 0) {
            while ($alumno = $resultado->fetch_assoc()) {
                $alumnos[] = $alumno;
            }
        }

        return $alumnos;
    }

    /**
     * Obtener un alumno por su ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM alumno WHERE id_alumno = ?";
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
     * Registrar un nuevo alumno
     */
    public function registrar($alumno)
    {
        $sql = "INSERT INTO alumno (nombre, apellidos, carrera, email, telefono, fecha_ingreso, estatus_pago) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "sssssss",
            $alumno['nombre'],
            $alumno['apellidos'],
            $alumno['carrera'],
            $alumno['email'],
            $alumno['telefono'],
            $alumno['fecha_ingreso'],
            $alumno['estatus_pago']
        );

        if ($stmt->execute()) {
            return $this->db->insert_id; // Retorna el ID del alumno insertado
        }

        return false;
    }

    /**
     * Actualizar un alumno existente
     */
    public function actualizar($id, $alumno)
    {
        $sql = "UPDATE alumno SET 
                nombre = ?, 
                apellidos = ?, 
                carrera = ?, 
                email = ?, 
                telefono = ?, 
                fecha_ingreso = ?, 
                estatus_pago = ? 
                WHERE id_alumno = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "sssssssi",
            $alumno['nombre'],
            $alumno['apellidos'],
            $alumno['carrera'],
            $alumno['email'],
            $alumno['telefono'],
            $alumno['fecha_ingreso'],
            $alumno['estatus_pago'],
            $id
        );

        return $stmt->execute();
    }

    /**
     * Eliminar un alumno
     */
    public function eliminar($id)
    {
        // Iniciar transacción para asegurar que todas las operaciones se completen o ninguna
        $this->db->begin_transaction();

        try {
            // 1. Primero verificamos si el alumno tiene tarjetas asociadas
            $sql = "SELECT id_tarjeta FROM tarjeta_nfc WHERE id_alumno = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();

            // Si tiene tarjetas, actualizamos a NULL la referencia del alumno
            if ($resultado->num_rows > 0) {
                $sql = "UPDATE tarjeta_nfc SET id_alumno = NULL, estado = 'Inactiva' WHERE id_alumno = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $id);
                if (!$stmt->execute()) {
                    throw new Exception("Error al actualizar las tarjetas del alumno");
                }
            }

            // 2. Si tiene pagos, podríamos decidir conservarlos o eliminarlos
            // En este caso, vamos a eliminarlos para que el alumno pueda ser borrado completamente
            $sql = "DELETE FROM pagos WHERE id_alumno = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar los pagos del alumno");
            }

            // 3. Finalmente, eliminamos al alumno
            $sql = "DELETE FROM alumno WHERE id_alumno = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar al alumno");
            }

            // Confirmar transacción
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Revertir cambios si hay error
            $this->db->rollback();
            error_log("Error al eliminar alumno: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar alumnos por nombre o apellido
     */
    public function buscar($termino)
    {
        $busqueda = "%$termino%";
        $sql = "SELECT * FROM alumno 
                WHERE nombre LIKE ? OR apellidos LIKE ? OR carrera LIKE ?
                ORDER BY apellidos, nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $busqueda, $busqueda, $busqueda);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $alumnos = [];
        if ($resultado->num_rows > 0) {
            while ($alumno = $resultado->fetch_assoc()) {
                $alumnos[] = $alumno;
            }
        }

        return $alumnos;
    }

    /**
     * Actualizar estatus de pago de un alumno
     */
    public function actualizarEstatusPago($id, $nuevoEstatus)
    {
        $sql = "UPDATE alumno SET estatus_pago = ? WHERE id_alumno = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $nuevoEstatus, $id);

        return $stmt->execute();
    }

    /**
     * Obtener estadísticas de alumnos para el dashboard
     */
    public function obtenerEstadisticas()
    {
        $estadisticas = [
            'total' => 0,
            'al_corriente' => 0,
            'pendientes' => 0,
            'bloqueados' => 0
        ];

        // Total de alumnos
        $sql = "SELECT COUNT(*) as total FROM alumno";
        $resultado = $this->db->query($sql);
        if ($resultado->num_rows > 0) {
            $estadisticas['total'] = $resultado->fetch_assoc()['total'];
        }

        // Alumnos al corriente
        $sql = "SELECT COUNT(*) as total FROM alumno WHERE estatus_pago = 'Al corriente'";
        $resultado = $this->db->query($sql);
        if ($resultado->num_rows > 0) {
            $estadisticas['al_corriente'] = $resultado->fetch_assoc()['total'];
        }

        // Alumnos con pagos pendientes
        $sql = "SELECT COUNT(*) as total FROM alumno WHERE estatus_pago = 'Pendiente'";
        $resultado = $this->db->query($sql);
        if ($resultado->num_rows > 0) {
            $estadisticas['pendientes'] = $resultado->fetch_assoc()['total'];
        }

        // Alumnos bloqueados
        $sql = "SELECT COUNT(*) as total FROM alumno WHERE estatus_pago = 'Bloqueado'";
        $resultado = $this->db->query($sql);
        if ($resultado->num_rows > 0) {
            $estadisticas['bloqueados'] = $resultado->fetch_assoc()['total'];
        }

        return $estadisticas;
    }
}
