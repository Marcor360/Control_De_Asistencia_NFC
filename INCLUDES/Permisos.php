<?php
class Permisos
{
    // Define los permisos por rol
    private static $permisosPorRol = [
        'Administrador' => [
            'dashboard' => ['ver', 'editar'],
            'alumnos' => ['ver', 'editar', 'eliminar'],
            'asistencia' => ['ver', 'editar', 'registrar'],
            'pagos' => ['ver', 'editar', 'registrar'],
            'reportes' => ['ver', 'generar', 'exportar'],
            'administrar_usuarios' => ['ver', 'editar', 'crear', 'eliminar'] // Nuevo módulo
        ],
        'Profesor' => [
            'dashboard' => ['ver'],
            'alumnos' => ['ver'],
            'asistencia' => ['ver', 'registrar'],
            'pagos' => ['ver'],
            'reportes' => ['ver', 'generar']
        ],
        'Coordinador' => [
            'dashboard' => ['ver'],
            'alumnos' => ['ver', 'editar'],
            'asistencia' => ['ver', 'registrar'],
            'pagos' => ['ver', 'registrar'],
            'reportes' => ['ver', 'generar', 'exportar']
        ],
        'Alumno' => [
            'asistencia' => ['ver'],
            'reportes' => ['ver']
        ]
    ];

    /**
     * Verifica si un rol tiene un permiso específico
     */
    public static function tienePermiso($rol, $modulo, $accion)
    {
        if (!isset(self::$permisosPorRol[$rol])) {
            return false;
        }
        if (!isset(self::$permisosPorRol[$rol][$modulo])) {
            return false;
        }
        return in_array($accion, self::$permisosPorRol[$rol][$modulo]);
    }

    /**
     * Verifica si un usuario tiene permiso para acceder a un módulo
     */
    public static function puedeAccederModulo($rol, $modulo)
    {
        if (!isset(self::$permisosPorRol[$rol])) {
            return false;
        }
        return isset(self::$permisosPorRol[$rol][$modulo]);
    }
}
