<?php
// controladores/TarjetaController.php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../modelos/Tarjeta.php';
require_once __DIR__ . '/../modelos/Alumno.php';
require_once __DIR__ . '/../modelos/Asistencia.php';

class TarjetaController {
    private $tarjeta;
    private $alumno;
    private $asistencia;
    private $db;

    public function __construct() {
        $this->tarjeta = new Tarjeta();
        $this->alumno = new Alumno();
        $this->asistencia = new Asistencia();
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Registra una lectura de tarjeta NFC
     * @param string $codigoNFC Código único de la tarjeta NFC
     * @return array Resultado de la operación y datos del alumno
     */
    public function registrarLectura($codigoNFC) {
        $resultado = [
            'exito' => false,
            'mensaje' => '',
            'datos' => null,
            'tipo_registro' => ''
        ];

        // Verificar si la tarjeta existe
        $tarjeta = $this->tarjeta->buscarPorCodigo($codigoNFC);
        
        if (!$tarjeta) {
            $resultado['mensaje'] = 'La tarjeta no está registrada en el sistema.';
            return $resultado;
        }

        // Verificar si la tarjeta está activa
        if ($tarjeta['estado'] !== 'Activa') {
            $resultado['mensaje'] = 'La tarjeta está ' . strtolower($tarjeta['estado']) . '. No se puede registrar.';
            return $resultado;
        }

        // Verificar si la tarjeta está asociada a un alumno
        if (empty($tarjeta['id_alumno'])) {
            $resultado['mensaje'] = 'La tarjeta no está asociada a ningún alumno.';
            return $resultado;
        }

        // Obtener datos del alumno
        $alumno = $this->alumno->obtenerPorId($tarjeta['id_alumno']);
        
        if (!$alumno) {
            $resultado['mensaje'] = 'No se encontró información del alumno.';
            return $resultado;
        }

        // Verificar estado de pago del alumno
        if ($alumno['estatus_pago'] === 'Bloqueado') {
            $resultado['mensaje'] = 'El alumno está bloqueado por falta de pago.';
            return $resultado;
        }

        // Obtener fecha y hora actual
        $fechaActual = date('Y-m-d');
        $horaActual = date('H:i:s');

        // Verificar si ya tiene un registro de entrada para hoy
        $registroHoy = $this->asistencia->obtenerRegistroDia($tarjeta['id_tarjeta'], $fechaActual);

        if ($registroHoy) {
            // Ya hay registro hoy, verificar si es entrada o salida
            if ($registroHoy['hora_salida'] === null) {
                // Registrar salida
                if ($this->asistencia->registrarSalida($registroHoy['id_asistencia'], $horaActual)) {
                    $resultado['exito'] = true;
                    $resultado['mensaje'] = 'Salida registrada correctamente.';
                    $resultado['tipo_registro'] = 'Salida';
                    $resultado['datos'] = [
                        'alumno' => $alumno,
                        'hora' => $horaActual,
                        'fecha' => $fechaActual
                    ];
                } else {
                    $resultado['mensaje'] = 'Error al registrar la salida.';
                }
            } else {
                // Ya tiene entrada y salida registradas
                $resultado['mensaje'] = 'Ya se ha registrado la entrada y salida para el día de hoy.';
            }
        } else {
            // No hay registro hoy, registrar entrada
            $idAsistencia = $this->asistencia->registrarEntrada($tarjeta['id_tarjeta'], $fechaActual, $horaActual);
            
            if ($idAsistencia) {
                $resultado['exito'] = true;
                $resultado['mensaje'] = 'Entrada registrada correctamente.';
                $resultado['tipo_registro'] = 'Entrada';
                $resultado['datos'] = [
                    'alumno' => $alumno,
                    'hora' => $horaActual,
                    'fecha' => $fechaActual
                ];
            } else {
                $resultado['mensaje'] = 'Error al registrar la entrada.';
            }
        }

        return $resultado;
    }

    /**
     * Obtiene los últimos registros de asistencia
     * @param int $limite Número de registros a obtener
     * @param int $pagina Página actual para paginación
     * @return array Lista de registros de asistencia
     */
    public function obtenerUltimosRegistros($limite = 10, $pagina = 1) {
        $offset = ($pagina - 1) * $limite;
        return $this->asistencia->obtenerUltimosRegistros($limite, $offset);
    }

    /**
     * Obtiene el número total de páginas para paginación
     * @param int $limitePorPagina Registros por página
     * @return int Número total de páginas
     */
    public function obtenerTotalPaginas($limitePorPagina = 10) {
        $totalRegistros = $this->asistencia->contarRegistros();
        return ceil($totalRegistros / $limitePorPagina);
    }
}
