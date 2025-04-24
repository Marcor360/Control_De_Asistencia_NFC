<?php
// controladores/ReporteController.php
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../modelos/Reporte.php';
require_once __DIR__ . '/../modelos/Asistencia.php';
require_once __DIR__ . '/../modelos/Pago.php';
require_once __DIR__ . '/../modelos/ControlAcceso.php';

class ReporteController
{
    private $reporte;
    private $asistencia;
    private $pago;
    private $controlAcceso;
    private $db;

    public function __construct()
    {
        $this->reporte = new Reporte();
        $this->asistencia = new Asistencia();
        $this->pago = new Pago();
        $this->controlAcceso = new ControlAcceso();
        $this->db = Database::getInstancia()->getConexion();
    }

    /**
     * Genera un reporte según los parámetros especificados
     * @param int $idUsuario ID del usuario que genera el reporte
     * @param string $tipoReporte Tipo de reporte (Asistencia, Pagos, Accesos, General)
     * @param array $parametros Parámetros para el reporte (fechas, alumno, etc.)
     * @param bool $exportar Indica si se debe exportar el reporte
     * @return array Resultado de la operación y datos del reporte
     */
    public function generarReporte($idUsuario, $tipoReporte, $parametros, $exportar = false)
    {
        $resultado = [
            'exito' => false,
            'mensaje' => '',
            'datos' => [],
            'id_reporte' => 0
        ];

        // Validar fechas
        $fechaInicio = $parametros['fecha_inicio'] ?? null;
        $fechaFin = $parametros['fecha_fin'] ?? null;
        $idAlumno = $parametros['id_alumno'] ?? null;

        if (!$fechaInicio || !$fechaFin) {
            $resultado['mensaje'] = 'Las fechas de inicio y fin son obligatorias.';
            return $resultado;
        }

        // Validar que la fecha de inicio no sea posterior a la de fin
        if (strtotime($fechaInicio) > strtotime($fechaFin)) {
            $resultado['mensaje'] = 'La fecha de inicio no puede ser posterior a la fecha de fin.';
            return $resultado;
        }

        try {
            // Convertir los parámetros a formato JSON para almacenar en la BD
            $parametrosJSON = json_encode([
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'id_alumno' => $idAlumno
            ]);

            // Registrar el reporte en la base de datos
            $idReporte = $this->reporte->registrarReporte($idUsuario, $tipoReporte, $fechaInicio, $fechaFin, $parametrosJSON);

            if (!$idReporte) {
                $resultado['mensaje'] = 'Error al registrar el reporte en la base de datos.';
                return $resultado;
            }

            $resultado['id_reporte'] = $idReporte;

            // Generar datos según el tipo de reporte
            switch ($tipoReporte) {
                case 'Asistencia':
                    $datosReporte = $this->generarReporteAsistencia($fechaInicio, $fechaFin, $idAlumno);
                    break;

                case 'Pagos':
                    $datosReporte = $this->generarReportePagos($fechaInicio, $fechaFin, $idAlumno);
                    break;

                case 'Accesos':
                    $datosReporte = $this->generarReporteAccesos($fechaInicio, $fechaFin, $idAlumno);
                    break;

                case 'General':
                    $datosReporte = $this->generarReporteGeneral($fechaInicio, $fechaFin, $idAlumno);
                    break;

                default:
                    $resultado['mensaje'] = 'Tipo de reporte no válido.';
                    return $resultado;
            }

            // Si se solicitó exportar, generar el archivo
            if ($exportar) {
                $nombreArchivo = $this->exportarReporte($idReporte, $tipoReporte, $datosReporte);

                if ($nombreArchivo) {
                    // Actualizar el nombre del archivo en la BD
                    $this->reporte->actualizarArchivoGenerado($idReporte, $nombreArchivo);
                    $resultado['mensaje'] = "Reporte generado y exportado correctamente. Archivo: $nombreArchivo";
                } else {
                    $resultado['mensaje'] = 'Error al exportar el reporte a archivo.';
                    return $resultado;
                }
            } else {
                $resultado['mensaje'] = 'Reporte generado correctamente.';
            }

            $resultado['exito'] = true;
            $resultado['datos'] = $datosReporte;

            return $resultado;
        } catch (Exception $e) {
            $resultado['mensaje'] = 'Error al generar el reporte: ' . $e->getMessage();
            return $resultado;
        }
    }

    /**
     * Genera un reporte de asistencia
     * @param string $fechaInicio Fecha de inicio del periodo
     * @param string $fechaFin Fecha de fin del periodo
     * @param int|null $idAlumno ID del alumno (opcional)
     * @return array Datos del reporte de asistencia
     */
    private function generarReporteAsistencia($fechaInicio, $fechaFin, $idAlumno = null)
    {
        return $this->asistencia->obtenerPorFechas($fechaInicio, $fechaFin, $idAlumno);
    }

    /**
     * Genera un reporte de pagos
     * @param string $fechaInicio Fecha de inicio del periodo
     * @param string $fechaFin Fecha de fin del periodo
     * @param int|null $idAlumno ID del alumno (opcional)
     * @return array Datos del reporte de pagos
     */
    private function generarReportePagos($fechaInicio, $fechaFin, $idAlumno = null)
    {
        return $this->pago->obtenerPorFechas($fechaInicio, $fechaFin, $idAlumno);
    }

    /**
     * Genera un reporte de accesos
     * @param string $fechaInicio Fecha de inicio del periodo
     * @param string $fechaFin Fecha de fin del periodo
     * @param int|null $idAlumno ID del alumno (opcional)
     * @return array Datos del reporte de accesos
     */
    private function generarReporteAccesos($fechaInicio, $fechaFin, $idAlumno = null)
    {
        return $this->controlAcceso->obtenerPorFechas($fechaInicio, $fechaFin, $idAlumno);
    }

    /**
     * Genera un reporte general que incluye asistencia, pagos y accesos
     * @param string $fechaInicio Fecha de inicio del periodo
     * @param string $fechaFin Fecha de fin del periodo
     * @param int|null $idAlumno ID del alumno (opcional)
     * @return array Datos del reporte general
     */
    private function generarReporteGeneral($fechaInicio, $fechaFin, $idAlumno = null)
    {
        // Obtener datos de asistencia
        $datosAsistencia = $this->asistencia->obtenerPorFechas($fechaInicio, $fechaFin, $idAlumno);

        // Conteo de asistencias
        $totalAsistencias = count($datosAsistencia);
        $presentes = 0;
        $retardos = 0;
        $ausentes = 0;

        foreach ($datosAsistencia as $asistencia) {
            if ($asistencia['tipo_asistencia'] === 'Presente') {
                $presentes++;
            } elseif ($asistencia['tipo_asistencia'] === 'Retardo') {
                $retardos++;
            } elseif ($asistencia['tipo_asistencia'] === 'Ausente') {
                $ausentes++;
            }
        }

        // Obtener datos de pagos
        $datosPagos = $this->pago->obtenerPorFechas($fechaInicio, $fechaFin, $idAlumno);

        // Conteo de pagos
        $totalPagos = count($datosPagos);
        $pagados = 0;
        $pendientes = 0;
        $montoTotal = 0;

        foreach ($datosPagos as $pago) {
            if ($pago['estado_pago'] === 'Pagado') {
                $pagados++;
            } elseif ($pago['estado_pago'] === 'Pendiente') {
                $pendientes++;
            }

            $montoTotal += $pago['monto'];
        }

        // Obtener datos de accesos
        $datosAccesos = $this->controlAcceso->obtenerPorFechas($fechaInicio, $fechaFin, $idAlumno);

        // Conteo de accesos
        $totalAccesos = count($datosAccesos);
        $entradas = 0;
        $salidas = 0;
        $denegados = 0;

        foreach ($datosAccesos as $acceso) {
            if ($acceso['tipo_acceso'] === 'Entrada') {
                $entradas++;
            } elseif ($acceso['tipo_acceso'] === 'Salida') {
                $salidas++;
            }

            if (!$acceso['permitido']) {
                $denegados++;
            }
        }

        // Generar datos para gráfica por meses
        $meses = $this->obtenerMesesEnRango($fechaInicio, $fechaFin);
        $datosGrafica = [
            'meses' => $meses,
            'asistencias' => [],
            'pagos' => [],
            'accesos' => []
        ];

        // Conteo por meses para gráfica
        foreach ($meses as $mes) {
            // Formato YYYY-MM
            $anioMes = substr($mes, -4) . '-' . $this->obtenerNumeroMes(substr($mes, 0, 3));

            // Contar asistencias por mes
            $conteoAsistencias = 0;
            foreach ($datosAsistencia as $asistencia) {
                if (substr($asistencia['fecha'], 0, 7) === $anioMes) {
                    $conteoAsistencias++;
                }
            }
            $datosGrafica['asistencias'][] = $conteoAsistencias;

            // Contar pagos por mes
            $conteoPagos = 0;
            foreach ($datosPagos as $pago) {
                if (substr($pago['fecha_pago'], 0, 7) === $anioMes) {
                    $conteoPagos++;
                }
            }
            $datosGrafica['pagos'][] = $conteoPagos;

            // Contar accesos por mes
            $conteoAccesos = 0;
            foreach ($datosAccesos as $acceso) {
                if (substr($acceso['fecha_hora'], 0, 7) === $anioMes) {
                    $conteoAccesos++;
                }
            }
            $datosGrafica['accesos'][] = $conteoAccesos;
        }

        // Armar estructura de datos para el reporte general
        return [
            'asistencia' => [
                'total' => $totalAsistencias,
                'presentes' => $presentes,
                'retardos' => $retardos,
                'ausentes' => $ausentes
            ],
            'pagos' => [
                'total' => $totalPagos,
                'pagados' => $pagados,
                'pendientes' => $pendientes,
                'monto_total' => $montoTotal
            ],
            'accesos' => [
                'total' => $totalAccesos,
                'entradas' => $entradas,
                'salidas' => $salidas,
                'denegados' => $denegados
            ],
            'grafica' => $datosGrafica
        ];
    }

    /**
     * Exporta un reporte a archivo (Excel, CSV, etc.)
     * @param int $idReporte ID del reporte
     * @param string $tipoReporte Tipo de reporte
     * @param array $datos Datos del reporte
     * @return string|false Nombre del archivo generado o false si falla
     */
    private function exportarReporte($idReporte, $tipoReporte, $datos)
    {
        // En una implementación real, aquí se generaría el archivo Excel o CSV

        // Para esta demostración, simulamos la creación de un archivo
        $fechaHora = date('Ymd_His');
        $nombreArchivo = "reporte_{$tipoReporte}_{$idReporte}_{$fechaHora}.csv";

        // Ruta del archivo en el servidor
        $rutaArchivo = __DIR__ . '/../reportes/' . $nombreArchivo;

        // Verificar si existe el directorio de reportes, sino crearlo
        if (!is_dir(__DIR__ . '/../reportes')) {
            mkdir(__DIR__ . '/../reportes', 0755, true);
        }

        // Crear archivo CSV (simplificado)
        $archivo = fopen($rutaArchivo, 'w');

        if (!$archivo) {
            return false;
        }

        // Escribir datos según el tipo de reporte
        switch ($tipoReporte) {
            case 'Asistencia':
                // Encabezados
                fputcsv($archivo, ['Alumno', 'Fecha', 'Hora Entrada', 'Hora Salida', 'Tipo']);

                // Datos
                foreach ($datos as $registro) {
                    fputcsv($archivo, [
                        $registro['nombre_alumno'],
                        $registro['fecha'],
                        $registro['hora_entrada'],
                        $registro['hora_salida'],
                        $registro['tipo_asistencia']
                    ]);
                }
                break;

            case 'Pagos':
                // Encabezados
                fputcsv($archivo, ['Alumno', 'Periodo', 'Monto', 'Fecha Pago', 'Estado']);

                // Datos
                foreach ($datos as $registro) {
                    fputcsv($archivo, [
                        $registro['nombre_completo'],
                        $registro['periodo'],
                        $registro['monto'],
                        $registro['fecha_pago'],
                        $registro['estado_pago']
                    ]);
                }
                break;

            case 'Accesos':
                // Encabezados
                fputcsv($archivo, ['Alumno', 'Fecha', 'Hora', 'Tipo', 'Permitido', 'Motivo Rechazo']);

                // Datos
                foreach ($datos as $registro) {
                    fputcsv($archivo, [
                        $registro['nombre_completo'],
                        substr($registro['fecha_hora'], 0, 10),
                        substr($registro['fecha_hora'], 11, 8),
                        $registro['tipo_acceso'],
                        $registro['permitido'] ? 'Sí' : 'No',
                        $registro['motivo_rechazo']
                    ]);
                }
                break;

            case 'General':
                // Encabezados - Sección Asistencia
                fputcsv($archivo, ['REPORTE GENERAL']);
                fputcsv($archivo, []);
                fputcsv($archivo, ['ASISTENCIA']);
                fputcsv($archivo, ['Total Registros', 'Presentes', 'Retardos', 'Ausentes']);
                fputcsv($archivo, [
                    $datos['asistencia']['total'],
                    $datos['asistencia']['presentes'],
                    $datos['asistencia']['retardos'],
                    $datos['asistencia']['ausentes']
                ]);

                // Encabezados - Sección Pagos
                fputcsv($archivo, []);
                fputcsv($archivo, ['PAGOS']);
                fputcsv($archivo, ['Total Pagos', 'Pagados', 'Pendientes', 'Monto Total']);
                fputcsv($archivo, [
                    $datos['pagos']['total'],
                    $datos['pagos']['pagados'],
                    $datos['pagos']['pendientes'],
                    $datos['pagos']['monto_total']
                ]);

                // Encabezados - Sección Accesos
                fputcsv($archivo, []);
                fputcsv($archivo, ['ACCESOS']);
                fputcsv($archivo, ['Total Accesos', 'Entradas', 'Salidas', 'Denegados']);
                fputcsv($archivo, [
                    $datos['accesos']['total'],
                    $datos['accesos']['entradas'],
                    $datos['accesos']['salidas'],
                    $datos['accesos']['denegados']
                ]);
                break;
        }

        fclose($archivo);

        return $nombreArchivo;
    }

    /**
     * Obtiene un reporte por su ID
     * @param int $idReporte ID del reporte
     * @return array|false Datos del reporte o false si no existe
     */
    public function obtenerReportePorId($idReporte)
    {
        return $this->reporte->obtenerPorId($idReporte);
    }

    /**
     * Obtiene la lista de meses en un rango de fechas
     * @param string $fechaInicio Fecha de inicio en formato Y-m-d
     * @param string $fechaFin Fecha de fin en formato Y-m-d
     * @return array Lista de meses en formato "MMM YYYY"
     */
    private function obtenerMesesEnRango($fechaInicio, $fechaFin)
    {
        $meses = [];
        $mesActual = new DateTime($fechaInicio);
        $mesFin = new DateTime($fechaFin);

        // Establecer el día 1 para cada fecha
        $mesActual->modify('first day of this month');
        $mesFin->modify('first day of this month');

        // Iterar por cada mes en el rango
        while ($mesActual <= $mesFin) {
            $meses[] = $mesActual->format('M Y'); // Formato "MMM YYYY"
            $mesActual->modify('+1 month');
        }

        return $meses;
    }

    /**
     * Obtiene el número de mes a partir de su abreviatura en inglés
     * @param string $abreviatura Abreviatura del mes (Jan, Feb, etc.)
     * @return string Número de mes con dos dígitos (01, 02, etc.)
     */
    private function obtenerNumeroMes($abreviatura)
    {
        $meses = [
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'May' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Aug' => '08',
            'Sep' => '09',
            'Oct' => '10',
            'Nov' => '11',
            'Dec' => '12'
        ];

        return $meses[$abreviatura] ?? '01';
    }

    // Añadir este método a la clase ReporteController

    /**
     * Genera un reporte solo para visualización (sin guardar en BD)
     * @param string $tipoReporte Tipo de reporte (Asistencia, Pagos, Accesos, General)
     * @param array $parametros Parámetros para el reporte (fechas, alumno, etc.)
     * @return array Resultado de la operación y datos del reporte
     */
    public function generarReporteSoloVista($tipoReporte, $parametros)
    {
        $resultado = [
            'exito' => false,
            'mensaje' => '',
            'datos' => []
        ];

        // Validar fechas
        $fechaInicio = $parametros['fecha_inicio'] ?? null;
        $fechaFin = $parametros['fecha_fin'] ?? null;
        $idAlumno = $parametros['id_alumno'] ?? null;

        if (!$fechaInicio || !$fechaFin) {
            $resultado['mensaje'] = 'Las fechas de inicio y fin son obligatorias.';
            return $resultado;
        }

        // Validar que la fecha de inicio no sea posterior a la de fin
        if (strtotime($fechaInicio) > strtotime($fechaFin)) {
            $resultado['mensaje'] = 'La fecha de inicio no puede ser posterior a la fecha de fin.';
            return $resultado;
        }

        try {
            // Generar datos según el tipo de reporte
            switch ($tipoReporte) {
                case 'Asistencia':
                    $datosReporte = $this->generarReporteAsistencia($fechaInicio, $fechaFin, $idAlumno);
                    break;

                case 'General':
                    $datosReporte = $this->generarReporteGeneral($fechaInicio, $fechaFin, $idAlumno);
                    // Añadir las últimas asistencias al reporte general para alumnos
                    if ($idAlumno) {
                        $datosReporte['ultimas_asistencias'] = $this->asistencia->obtenerAsistenciasPorAlumno($idAlumno, 10);
                    }
                    break;

                default:
                    $resultado['mensaje'] = 'Tipo de reporte no válido.';
                    return $resultado;
            }

            $resultado['exito'] = true;
            $resultado['mensaje'] = 'Reporte generado correctamente.';
            $resultado['datos'] = $datosReporte;

            return $resultado;
        } catch (Exception $e) {
            $resultado['mensaje'] = 'Error al generar el reporte: ' . $e->getMessage();
            return $resultado;
        }
    }
}
