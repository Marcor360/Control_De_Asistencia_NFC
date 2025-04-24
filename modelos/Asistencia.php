<?php
// modelos/Asistencia.php
require_once __DIR__ . '/../includes/Database.php';

class Asistencia
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Registrar entrada de un alumno
     */
    public function registrarEntrada($idTarjeta, $tipoAsistencia = 'Presente')
    {
        // Obtener la fecha actual
        $fechaActual = date('Y-m-d');

        // Verificar si ya existe un registro para hoy
        $sql = "SELECT id_asistencia FROM asistencia WHERE id_tarjeta = ? AND fecha = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $idTarjeta, $fechaActual);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // Ya existe un registro para hoy, actualizar hora de entrada
            $asistencia = $resultado->fetch_assoc();
            $idAsistencia = $asistencia['id_asistencia'];

            $sql = "UPDATE asistencia SET hora_entrada = NOW(), tipo_asistencia = ? WHERE id_asistencia = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("si", $tipoAsistencia, $idAsistencia);
            return $stmt->execute();
        } else {
            // No existe, crear un nuevo registro
            $sql = "INSERT INTO asistencia (id_tarjeta, fecha, hora_entrada, tipo_asistencia, acceso_permitido) 
                    VALUES (?, ?, NOW(), ?, 1)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iss", $idTarjeta, $fechaActual, $tipoAsistencia);
            return $stmt->execute();
        }
    }

    /**
     * Registrar salida de un alumno
     */
    public function registrarSalida($idAsistencia)
    {
        $sql = "UPDATE asistencia SET hora_salida = NOW() WHERE id_asistencia = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $idAsistencia);
        return $stmt->execute();
    }

    /**
     * Registrar un nuevo acceso
     */
    public function registrarAcceso($acceso)
    {
        $sql = "INSERT INTO control_acceso (id_tarjeta, fecha_hora, tipo_acceso, permitido, motivo_rechazo, dispositivo, ubicacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "issisbs",
            $acceso['id_tarjeta'],
            $acceso['fecha_hora'],
            $acceso['tipo_acceso'],
            $acceso['permitido'],
            $acceso['motivo_rechazo'],
            $acceso['dispositivo'],
            $acceso['ubicacion']
        );

        return $stmt->execute();
    }

    /**
     * Obtener registro de asistencia por fecha y tarjeta
     */
    public function obtenerRegistroPorFecha($idTarjeta, $fecha)
    {
        $sql = "SELECT * FROM asistencia WHERE id_tarjeta = ? AND fecha = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $idTarjeta, $fecha);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }

        return null;
    }

    /**
     * Obtener registro de asistencia por fecha y alumno
     */
    public function obtenerRegistroPorAlumnoFecha($idAlumno, $fecha)
    {
        $sql = "SELECT a.* FROM asistencia a
                JOIN tarjeta_nfc t ON a.id_tarjeta = t.id_tarjeta
                WHERE t.id_alumno = ? AND a.fecha = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $idAlumno, $fecha);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }

        return null;
    }

    /**
     * Obtener registros recientes de asistencia
     */
    public function obtenerRegistrosRecientes($limite = 10)
    {
        $sql = "SELECT a.*, CONCAT(al.nombre, ' ', al.apellidos) as nombre_completo
                FROM asistencia a
                JOIN tarjeta_nfc t ON a.id_tarjeta = t.id_tarjeta
                JOIN alumno al ON t.id_alumno = al.id_alumno
                ORDER BY a.fecha DESC, a.hora_entrada DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $registros = [];
        if ($resultado->num_rows > 0) {
            while ($registro = $resultado->fetch_assoc()) {
                $registros[] = $registro;
            }
        }

        return $registros;
    }

    /**
     * Obtener asistencias por fecha
     */
    public function obtenerAsistenciasPorFecha($fecha)
    {
        $sql = "SELECT a.*, CONCAT(al.nombre, ' ', al.apellidos) as nombre_completo
                FROM asistencia a
                JOIN tarjeta_nfc t ON a.id_tarjeta = t.id_tarjeta
                JOIN alumno al ON t.id_alumno = al.id_alumno
                WHERE a.fecha = ?
                ORDER BY a.hora_entrada";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $registros = [];
        if ($resultado->num_rows > 0) {
            while ($registro = $resultado->fetch_assoc()) {
                $registros[] = $registro;
            }
        }

        return $registros;
    }

    /**
     * Obtener asistencias por alumno
     */
    public function obtenerAsistenciasPorAlumno($idAlumno, $limite = 30)
    {
        $sql = "SELECT a.*, CONCAT(al.nombre, ' ', al.apellidos) as nombre_completo
                FROM asistencia a
                JOIN tarjeta_nfc t ON a.id_tarjeta = t.id_tarjeta
                JOIN alumno al ON t.id_alumno = al.id_alumno
                WHERE al.id_alumno = ?
                ORDER BY a.fecha DESC, a.hora_entrada DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $idAlumno, $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $registros = [];
        if ($resultado->num_rows > 0) {
            while ($registro = $resultado->fetch_assoc()) {
                $registros[] = $registro;
            }
        }

        return $registros;
    }

    /**
     * Obtener estadísticas de asistencia
     */
    public function obtenerEstadisticas()
    {
        $estadisticas = [
            'hoy' => [
                'total' => 0,
                'presentes' => 0,
                'retardos' => 0,
                'ausentes' => 0
            ],
            'semana' => [
                'total' => 0,
                'presentes' => 0,
                'retardos' => 0,
                'ausentes' => 0
            ],
            'mes' => [
                'total' => 0,
                'presentes' => 0,
                'retardos' => 0,
                'ausentes' => 0
            ]
        ];

        // Fechas para consultas
        $fechaHoy = date('Y-m-d');
        $inicioSemana = date('Y-m-d', strtotime('monday this week'));
        $finSemana = date('Y-m-d', strtotime('sunday this week'));
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');

        // Consultar total de alumnos (para calcular ausencias)
        $sqlTotalAlumnos = "SELECT COUNT(*) as total FROM alumno";
        $resultado = $this->db->query($sqlTotalAlumnos);
        $totalAlumnos = $resultado->fetch_assoc()['total'];

        // Estadísticas de hoy
        $sqlHoy = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN tipo_asistencia = 'Presente' THEN 1 ELSE 0 END) as presentes,
                    SUM(CASE WHEN tipo_asistencia = 'Retardo' THEN 1 ELSE 0 END) as retardos
                   FROM asistencia WHERE fecha = ?";

        $stmt = $this->db->prepare($sqlHoy);
        $stmt->bind_param("s", $fechaHoy);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $datosHoy = $resultado->fetch_assoc();
            $estadisticas['hoy']['total'] = $datosHoy['total'];
            $estadisticas['hoy']['presentes'] = $datosHoy['presentes'];
            $estadisticas['hoy']['retardos'] = $datosHoy['retardos'];
            $estadisticas['hoy']['ausentes'] = $totalAlumnos - $datosHoy['total'];
        } else {
            $estadisticas['hoy']['ausentes'] = $totalAlumnos;
        }

        // Estadísticas de la semana
        $sqlSemana = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN tipo_asistencia = 'Presente' THEN 1 ELSE 0 END) as presentes,
                        SUM(CASE WHEN tipo_asistencia = 'Retardo' THEN 1 ELSE 0 END) as retardos
                     FROM asistencia WHERE fecha BETWEEN ? AND ?";

        $stmt = $this->db->prepare($sqlSemana);
        $stmt->bind_param("ss", $inicioSemana, $finSemana);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $datosSemana = $resultado->fetch_assoc();
            $estadisticas['semana']['total'] = $datosSemana['total'];
            $estadisticas['semana']['presentes'] = $datosSemana['presentes'];
            $estadisticas['semana']['retardos'] = $datosSemana['retardos'];
            // Ausentes es aproximado (total_alumnos * dias_laborables - asistencias_registradas)
            $diasLaborablesSemana = 5; // Lunes a viernes
            $estadisticas['semana']['ausentes'] = ($totalAlumnos * $diasLaborablesSemana) - $datosSemana['total'];
        }

        // Estadísticas del mes
        $sqlMes = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN tipo_asistencia = 'Presente' THEN 1 ELSE 0 END) as presentes,
                    SUM(CASE WHEN tipo_asistencia = 'Retardo' THEN 1 ELSE 0 END) as retardos
                   FROM asistencia WHERE fecha BETWEEN ? AND ?";

        $stmt = $this->db->prepare($sqlMes);
        $stmt->bind_param("ss", $inicioMes, $finMes);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $datosMes = $resultado->fetch_assoc();
            $estadisticas['mes']['total'] = $datosMes['total'];
            $estadisticas['mes']['presentes'] = $datosMes['presentes'];
            $estadisticas['mes']['retardos'] = $datosMes['retardos'];

            // Calcular días laborables en el mes (aproximadamente 20)
            $diasLaborablesMes = 20;
            $estadisticas['mes']['ausentes'] = ($totalAlumnos * $diasLaborablesMes) - $datosMes['total'];
        }

        return $estadisticas;
    }

    /**
     * Obtener porcentaje de asistencia por alumno
     */
    public function obtenerPorcentajeAsistencia($idAlumno, $periodo = 'mes')
    {
        // Determinar fechas según periodo
        $fechaInicio = '';
        $fechaFin = date('Y-m-d');

        switch ($periodo) {
            case 'semana':
                $fechaInicio = date('Y-m-d', strtotime('monday this week'));
                break;
            case 'mes':
                $fechaInicio = date('Y-m-01');
                break;
            case 'trimestre':
                $fechaInicio = date('Y-m-d', strtotime('-3 months'));
                break;
            default:
                $fechaInicio = date('Y-m-01');
        }

        // Calcular días laborables en el periodo
        $diasLaborables = $this->calcularDiasLaborables($fechaInicio, $fechaFin);

        // Obtener asistencias del alumno en el periodo
        $sql = "SELECT COUNT(*) as total_asistencias
                FROM asistencia a
                JOIN tarjeta_nfc t ON a.id_tarjeta = t.id_tarjeta
                WHERE t.id_alumno = ? AND a.fecha BETWEEN ? AND ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iss", $idAlumno, $fechaInicio, $fechaFin);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $totalAsistencias = 0;
        if ($resultado->num_rows > 0) {
            $totalAsistencias = $resultado->fetch_assoc()['total_asistencias'];
        }

        // Calcular porcentaje
        $porcentaje = ($diasLaborables > 0) ? ($totalAsistencias / $diasLaborables) * 100 : 0;

        return round($porcentaje, 2);
    }

    /**
     * Calcular días laborables entre dos fechas (excluyendo fines de semana)
     */
    private function calcularDiasLaborables($fechaInicio, $fechaFin)
    {
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $fin->modify('+1 day'); // Incluir el día final

        $dias = 0;
        $intervalo = new DateInterval('P1D');
        $periodo = new DatePeriod($inicio, $intervalo, $fin);

        foreach ($periodo as $fecha) {
            $diaSemana = $fecha->format('N');
            if ($diaSemana < 6) { // 1 (lunes) a 5 (viernes)
                $dias++;
            }
        }

        return $dias;
    }
    /**
     * Obtener asistencias por rango de fechas
     * @param string $fechaInicio Fecha inicial (YYYY-MM-DD)
     * @param string $fechaFin Fecha final (YYYY-MM-DD)
     * @param int|null $idAlumno ID del alumno (opcional)
     * @return array Lista de asistencias
     */
    public function obtenerPorFechas($fechaInicio, $fechaFin, $idAlumno = null)
    {
        if ($idAlumno) {
            // Si se especifica un alumno, filtrar por él
            $sql = "SELECT a.*, t.codigo_nfc, CONCAT(al.nombre, ' ', al.apellidos) as nombre_alumno, 
                al.id_alumno, al.carrera
                FROM asistencia a
                JOIN tarjeta_nfc t ON a.id_tarjeta = t.id_tarjeta
                JOIN alumno al ON t.id_alumno = al.id_alumno
                WHERE a.fecha BETWEEN ? AND ? 
                AND al.id_alumno = ?
                ORDER BY a.fecha DESC, a.hora_entrada DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssi", $fechaInicio, $fechaFin, $idAlumno);
        } else {
            // Consulta sin filtro de alumno
            $sql = "SELECT a.*, t.codigo_nfc, CONCAT(al.nombre, ' ', al.apellidos) as nombre_alumno, 
                al.id_alumno, al.carrera
                FROM asistencia a
                JOIN tarjeta_nfc t ON a.id_tarjeta = t.id_tarjeta
                JOIN alumno al ON t.id_alumno = al.id_alumno
                WHERE a.fecha BETWEEN ? AND ? 
                ORDER BY a.fecha DESC, a.hora_entrada DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        $asistencias = [];

        while ($fila = $resultado->fetch_assoc()) {
            $asistencias[] = $fila;
        }

        return $asistencias;
    }

    /**
     * Obtener registro de asistencia de un día específico
     * @param int $idTarjeta ID de la tarjeta
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return array|null Datos del registro o null si no existe
     */
    public function obtenerRegistroDia($idTarjeta, $fecha)
    {
        $sql = "SELECT * FROM asistencia WHERE id_tarjeta = ? AND fecha = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("is", $idTarjeta, $fecha);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }

        return null;
    }

    /**
     * Obtener los últimos registros de asistencia
     * @param int $limite Número de registros a obtener
     * @param int $offset Offset para paginación
     * @return array Lista de registros
     */
    public function obtenerUltimosRegistros($limite = 10, $offset = 0)
    {
        $sql = "SELECT a.*, CONCAT(al.nombre, ' ', al.apellidos) as nombre_alumno, 
            t.codigo_nfc, al.carrera
            FROM asistencia a
            JOIN tarjeta_nfc t ON a.id_tarjeta = t.id_tarjeta
            JOIN alumno al ON t.id_alumno = al.id_alumno
            ORDER BY a.fecha DESC, a.hora_entrada DESC
            LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $limite, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $registros = [];
        while ($fila = $resultado->fetch_assoc()) {
            $registros[] = $fila;
        }

        return $registros;
    }

    /**
     * Contar total de registros de asistencia
     * @return int Total de registros
     */
    public function contarRegistros()
    {
        $sql = "SELECT COUNT(*) as total FROM asistencia";
        $resultado = $this->db->query($sql);

        if ($resultado && $fila = $resultado->fetch_assoc()) {
            return $fila['total'];
        }

        return 0;
    }
}
