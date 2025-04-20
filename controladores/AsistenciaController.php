<?php
// controladores/AsistenciaController.php
require_once __DIR__ . '/../modelos/Asistencia.php';
require_once __DIR__ . '/../modelos/Alumno.php';
require_once __DIR__ . '/../modelos/Tarjeta.php';

class AsistenciaController
{
    private $asistencia;
    private $alumno;
    private $tarjeta;

    public function __construct()
    {
        $this->asistencia = new Asistencia();
        $this->alumno = new Alumno();
        $this->tarjeta = new Tarjeta();
    }

    /**
     * Registrar entrada de un alumno
     */
    public function registrarEntrada($idTarjeta)
    {
        // Obtener información de la tarjeta
        $tarjetaInfo = $this->tarjeta->obtenerPorId($idTarjeta);
        if (!$tarjetaInfo) {
            return [
                'exito' => false,
                'mensaje' => 'La tarjeta no existe en el sistema'
            ];
        }

        // Verificar que la tarjeta esté activa
        if ($tarjetaInfo['estado'] !== 'Activa') {
            return [
                'exito' => false,
                'mensaje' => 'La tarjeta no está activa: ' . $tarjetaInfo['estado']
            ];
        }

        // Verificar que la tarjeta esté asignada a un alumno
        if (empty($tarjetaInfo['id_alumno'])) {
            return [
                'exito' => false,
                'mensaje' => 'La tarjeta no está asignada a ningún alumno'
            ];
        }

        // Verificar el estado de pagos del alumno
        $alumnoInfo = $this->alumno->obtenerPorId($tarjetaInfo['id_alumno']);
        if (!$alumnoInfo) {
            return [
                'exito' => false,
                'mensaje' => 'No se encontró información del alumno'
            ];
        }

        // Verificar si el alumno está bloqueado por pagos
        if ($alumnoInfo['estatus_pago'] === 'Bloqueado') {
            // Registrar el acceso no permitido
            $this->registrarAccesoBloqueado($idTarjeta, 'Entrada', 'Alumno bloqueado por pagos pendientes');
            
            return [
                'exito' => false,
                'mensaje' => 'Acceso denegado: El alumno tiene pagos pendientes'
            ];
        }

        // Verificar si ya se registró entrada hoy
        $registroHoy = $this->asistencia->obtenerRegistroPorFecha($idTarjeta, date('Y-m-d'));
        if ($registroHoy && $registroHoy['hora_entrada']) {
            return [
                'exito' => false,
                'mensaje' => 'Ya se registró entrada para este alumno hoy a las ' . date('H:i', strtotime($registroHoy['hora_entrada']))
            ];
        }

        // Determinar tipo de asistencia (Presente o Retardo) según la hora de entrada
        $horaActual = date('H:i:s');
        $horaLimite = '09:00:00'; // Hora límite para considerar "Presente" (configurable)
        $tipoAsistencia = ($horaActual <= $horaLimite) ? 'Presente' : 'Retardo';

        // Registrar la entrada
        $resultado = $this->asistencia->registrarEntrada($idTarjeta, $tipoAsistencia);
        
        if ($resultado) {
            // Registrar en control de acceso
            $this->registrarAcceso($idTarjeta, 'Entrada');
            
            return [
                'exito' => true,
                'mensaje' => 'Entrada registrada correctamente a las ' . date('H:i')
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al registrar la entrada'
            ];
        }
    }

    /**
     * Registrar salida de un alumno
     */
    public function registrarSalida($idTarjeta)
    {
        // Obtener información de la tarjeta
        $tarjetaInfo = $this->tarjeta->obtenerPorId($idTarjeta);
        if (!$tarjetaInfo) {
            return [
                'exito' => false,
                'mensaje' => 'La tarjeta no existe en el sistema'
            ];
        }

        // Verificar que la tarjeta esté activa
        if ($tarjetaInfo['estado'] !== 'Activa') {
            return [
                'exito' => false,
                'mensaje' => 'La tarjeta no está activa: ' . $tarjetaInfo['estado']
            ];
        }

        // Verificar si ya se registró entrada hoy
        $registroHoy = $this->asistencia->obtenerRegistroPorFecha($idTarjeta, date('Y-m-d'));
        if (!$registroHoy) {
            return [
                'exito' => false,
                'mensaje' => 'No hay registro de entrada para este alumno hoy'
            ];
        }

        // Verificar que no se haya registrado ya la salida
        if ($registroHoy['hora_salida']) {
            return [
                'exito' => false,
                'mensaje' => 'Ya se registró salida para este alumno hoy a las ' . date('H:i', strtotime($registroHoy['hora_salida']))
            ];
        }

        // Registrar la salida
        $resultado = $this->asistencia->registrarSalida($registroHoy['id_asistencia']);
        
        if ($resultado) {
            // Registrar en control de acceso
            $this->registrarAcceso($idTarjeta, 'Salida');
            
            return [
                'exito' => true,
                'mensaje' => 'Salida registrada correctamente a las ' . date('H:i')
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al registrar la salida'
            ];
        }
    }

    /**
     * Registrar acceso en la tabla control_acceso
     */
    private function registrarAcceso($idTarjeta, $tipoAcceso, $permitido = true, $motivoRechazo = null)
    {
        $acceso = [
            'id_tarjeta' => $idTarjeta,
            'fecha_hora' => date('Y-m-d H:i:s'),
            'tipo_acceso' => $tipoAcceso,
            'permitido' => $permitido ? 1 : 0,
            'motivo_rechazo' => $motivoRechazo,
            'dispositivo' => 'Web',
            'ubicacion' => 'Sistema principal'
        ];

        return $this->asistencia->registrarAcceso($acceso);
    }

    /**
     * Registrar acceso bloqueado
     */
    private function registrarAccesoBloqueado($idTarjeta, $tipoAcceso, $motivo)
    {
        return $this->registrarAcceso($idTarjeta, $tipoAcceso, false, $motivo);
    }

    /**
     * Obtener registros recientes de asistencia
     */
    public function obtenerRegistrosRecientes($limite = 10)
    {
        return $this->asistencia->obtenerRegistrosRecientes($limite);
    }

    /**
     * Obtener información de un alumno para mostrar tras el registro
     */
    public function obtenerInfoAlumno($idAlumno)
    {
        $alumnoInfo = $this->alumno->obtenerPorId($idAlumno);
        if (!$alumnoInfo) {
            return null;
        }

        // Determinar el tipo de registro (entrada o salida)
        $registroHoy = $this->asistencia->obtenerRegistroPorAlumnoFecha($idAlumno, date('Y-m-d'));
        $tipoRegistro = 'entrada';
        
        if ($registroHoy && $registroHoy['hora_salida']) {
            $tipoRegistro = 'salida';
        }

        $alumnoInfo['tipo_registro'] = $tipoRegistro;
        
        return $alumnoInfo;
    }

    /**
     * Obtener asistencias por fecha
     */
    public function obtenerAsistenciasPorFecha($fecha)
    {
        return $this->asistencia->obtenerAsistenciasPorFecha($fecha);
    }

    /**
     * Obtener asistencias por alumno
     */
    public function obtenerAsistenciasPorAlumno($idAlumno, $limite = 30)
    {
        return $this->asistencia->obtenerAsistenciasPorAlumno($idAlumno, $limite);
    }

    /**
     * Obtener estadísticas de asistencia
     */
    public function obtenerEstadisticas()
    {
        return $this->asistencia->obtenerEstadisticas();
    }
}