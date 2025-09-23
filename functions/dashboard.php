<?php
/**
 * Funciones para el Dashboard Principal
 * Sistema de Gestión de Recursos Humanos
 */

/**
 * Función para obtener métricas principales del dashboard
 * @return array
 */
function obtener_metricas_dashboard() {
    // Obtener estadísticas de empleados
    $stats_empleados = obtener_estadisticas_empleados();
    
    // Obtener estadísticas de documentos
    $stats_documentos = generar_reporte_documentos();
    
    // Obtener estadísticas del muro
    $stats_muro = obtener_estadisticas_muro();
    
    // Obtener estadísticas de nómina del mes actual
    $periodo_actual = date('Y-m');
    $stats_nomina = obtener_estadisticas_nomina($periodo_actual);
    
    return [
        'empleados_activos' => [
            'valor' => $stats_empleados['activos'],
            'total' => $stats_empleados['total'],
            'cambio' => '+' . $stats_empleados['nuevos_mes'],
            'tipo_cambio' => 'positive',
            'descripcion' => 'Empleados activos en el sistema'
        ],
        'documentos_pendientes' => [
            'valor' => $stats_documentos['firmas_pendientes'],
            'total' => $stats_documentos['total_firmas'],
            'cambio' => $stats_documentos['porcentaje_firmas'] . '%',
            'tipo_cambio' => $stats_documentos['porcentaje_firmas'] > 80 ? 'positive' : 'warning',
            'descripcion' => 'Documentos pendientes de firma'
        ],
        'publicaciones_mes' => [
            'valor' => $stats_muro['publicaciones_recientes'],
            'total' => $stats_muro['total_publicaciones'],
            'cambio' => '+' . $stats_muro['total_comentarios'],
            'tipo_cambio' => 'positive',
            'descripcion' => 'Publicaciones este mes'
        ],
        'nomina_procesada' => [
            'valor' => $stats_nomina['pagados'],
            'total' => $stats_nomina['total_registros'],
            'cambio' => formatear_moneda($stats_nomina['total_monto']),
            'tipo_cambio' => 'neutral',
            'descripcion' => 'Nómina procesada este mes'
        ]
    ];
}

/**
 * Función para obtener actividad reciente
 * @param int $limite
 * @return array
 */
function obtener_actividad_reciente($limite = 10) {
    // Simular actividad reciente del sistema
    $actividades = [
        [
            'tipo' => 'empleado',
            'icono' => 'fas fa-user-plus',
            'color' => 'success',
            'titulo' => 'Nuevo empleado registrado',
            'descripcion' => 'Carlos Rodríguez se unió al departamento de Ventas',
            'tiempo' => '2024-01-25 14:30:00',
            'usuario' => 'María González'
        ],
        [
            'tipo' => 'documento',
            'icono' => 'fas fa-file-signature',
            'color' => 'warning',
            'titulo' => 'Documento firmado',
            'descripcion' => 'Manual del Empleado 2024 firmado por Juan Pérez',
            'tiempo' => '2024-01-25 11:15:00',
            'usuario' => 'Juan Pérez'
        ],
        [
            'tipo' => 'nomina',
            'icono' => 'fas fa-money-bill-wave',
            'color' => 'primary',
            'titulo' => 'Nómina procesada',
            'descripcion' => 'Nómina de enero procesada para 15 empleados',
            'tiempo' => '2024-01-25 09:00:00',
            'usuario' => 'Sistema'
        ],
        [
            'tipo' => 'muro',
            'icono' => 'fas fa-bullhorn',
            'color' => 'info',
            'titulo' => 'Nueva publicación',
            'descripcion' => 'Anuncio sobre celebración del Día del Empleado',
            'tiempo' => '2024-01-24 16:45:00',
            'usuario' => 'María González'
        ],
        [
            'tipo' => 'permiso',
            'icono' => 'fas fa-calendar-check',
            'color' => 'success',
            'titulo' => 'Permiso aprobado',
            'descripcion' => 'Solicitud de vacaciones aprobada para Ana Martínez',
            'tiempo' => '2024-01-24 13:20:00',
            'usuario' => 'María González'
        ]
    ];
    
    // Formatear tiempo transcurrido
    foreach ($actividades as &$actividad) {
        $actividad['tiempo_transcurrido'] = tiempo_transcurrido($actividad['tiempo']);
    }
    
    return array_slice($actividades, 0, $limite);
}

/**
 * Función para obtener gráfico de empleados por departamento
 * @return array
 */
function obtener_grafico_departamentos() {
    $stats_empleados = obtener_estadisticas_empleados();
    $por_departamento = $stats_empleados['por_departamento'];
    
    $colores = [
        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
        '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'
    ];
    
    $datos = [];
    $contador = 0;
    
    foreach ($por_departamento as $departamento => $cantidad) {
        $datos[] = [
            'label' => $departamento,
            'value' => $cantidad,
            'color' => $colores[$contador % count($colores)]
        ];
        $contador++;
    }
    
    return $datos;
}

/**
 * Función para obtener próximos eventos/recordatorios
 * @param int $limite
 * @return array
 */
function obtener_proximos_eventos($limite = 5) {
    // Eventos mock para el dashboard
    $eventos = [
        [
            'id' => 1,
            'titulo' => 'Celebración Día del Empleado',
            'descripcion' => 'Almuerzo especial y actividades recreativas',
            'fecha' => '2024-01-26 12:30:00',
            'tipo' => 'evento',
            'icono' => 'fas fa-calendar-star',
            'color' => 'success'
        ],
        [
            'id' => 2,
            'titulo' => 'Capacitación Seguridad Informática',
            'descripcion' => 'Curso obligatorio para todo el personal',
            'fecha' => '2024-02-01 09:00:00',
            'tipo' => 'capacitacion',
            'icono' => 'fas fa-graduation-cap',
            'color' => 'primary'
        ],
        [
            'id' => 3,
            'titulo' => 'Revisión Anual de Desempeño',
            'descripcion' => 'Evaluaciones de desempeño 2024',
            'fecha' => '2024-02-15 08:00:00',
            'tipo' => 'evaluacion',
            'icono' => 'fas fa-chart-line',
            'color' => 'warning'
        ],
        [
            'id' => 4,
            'titulo' => 'Reunión Gerencial Mensual',
            'descripción' => 'Revisión de métricas y objetivos',
            'fecha' => '2024-02-05 14:00:00',
            'tipo' => 'reunion',
            'icono' => 'fas fa-users',
            'color' => 'info'
        ]
    ];
    
    // Ordenar por fecha
    usort($eventos, function($a, $b) {
        return strtotime($a['fecha']) - strtotime($b['fecha']);
    });
    
    // Formatear fechas
    foreach ($eventos as &$evento) {
        $evento['fecha_formateada'] = formatear_fecha($evento['fecha'], 'd/m/Y H:i');
        $evento['dias_restantes'] = ceil((strtotime($evento['fecha']) - time()) / 86400);
    }
    
    return array_slice($eventos, 0, $limite);
}

/**
 * Función para obtener resumen de tareas pendientes
 * @param int $usuario_id
 * @return array
 */
function obtener_tareas_pendientes($usuario_id) {
    $tareas = [];
    
    // Documentos pendientes de firma
    $documentos_pendientes = obtener_documentos_pendientes_firma($usuario_id);
    if (!empty($documentos_pendientes)) {
        $tareas[] = [
            'tipo' => 'documentos',
            'titulo' => 'Documentos por firmar',
            'cantidad' => count($documentos_pendientes),
            'descripcion' => count($documentos_pendientes) . ' documento(s) requieren tu firma',
            'enlace' => '/documentos.php?pendientes=1',
            'icono' => 'fas fa-file-signature',
            'color' => 'warning',
            'prioridad' => 'alta'
        ];
    }
    
    // Notificaciones no leídas
    $notificaciones_no_leidas = contar_notificaciones_no_leidas($usuario_id);
    if ($notificaciones_no_leidas > 0) {
        $tareas[] = [
            'tipo' => 'notificaciones',
            'titulo' => 'Notificaciones nuevas',
            'cantidad' => $notificaciones_no_leidas,
            'descripcion' => $notificaciones_no_leidas . ' notificación(es) sin leer',
            'enlace' => '#',
            'icono' => 'fas fa-bell',
            'color' => 'info',
            'prioridad' => 'media'
        ];
    }
    
    // Verificar si es gerente o admin para tareas adicionales
    $usuario = obtener_usuario_actual();
    if ($usuario && in_array($usuario['rol'], ['Gerente', 'Administrador'])) {
        // Solicitudes de permisos pendientes (mock)
        $tareas[] = [
            'tipo' => 'permisos',
            'titulo' => 'Solicitudes de permisos',
            'cantidad' => 3,
            'descripcion' => '3 solicitud(es) de permisos por revisar',
            'enlace' => '/permisos.php?pendientes=1',
            'icono' => 'fas fa-calendar-check',
            'color' => 'primary',
            'prioridad' => 'alta'
        ];
    }
    
    return $tareas;
}

/**
 * Función para obtener datos del widget de nómina
 * @return array
 */
function obtener_widget_nomina() {
    $periodo_actual = date('Y-m');
    $stats_nomina = obtener_estadisticas_nomina($periodo_actual);
    
    return [
        'periodo' => formatear_fecha($periodo_actual . '-01', 'mes_año'),
        'empleados_procesados' => $stats_nomina['pagados'],
        'total_empleados' => $stats_nomina['total_registros'],
        'monto_total' => $stats_nomina['total_monto'],
        'promedio_salario' => $stats_nomina['promedio_salario'],
        'porcentaje_completado' => $stats_nomina['total_registros'] > 0 ? 
            round(($stats_nomina['pagados'] / $stats_nomina['total_registros']) * 100, 1) : 0
    ];
}

/**
 * Función para obtener alertas del sistema
 * @return array
 */
function obtener_alertas_sistema() {
    $alertas = [];
    
    // Verificar documentos próximos a vencer
    $documentos = obtener_documentos();
    foreach ($documentos as $documento) {
        if ($documento['fecha_vencimiento'] && 
            strtotime($documento['fecha_vencimiento']) <= strtotime('+30 days')) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Documento próximo a vencer',
                'mensaje' => "El documento '{$documento['titulo']}' vence el " . 
                           formatear_fecha($documento['fecha_vencimiento']),
                'icono' => 'fas fa-exclamation-triangle'
            ];
        }
    }
    
    // Verificar empleados con cumpleaños próximos
    $empleados = obtener_empleados();
    $hoy = date('m-d');
    foreach ($empleados as $empleado) {
        if ($empleado['fecha_nacimiento']) {
            $cumple = date('m-d', strtotime($empleado['fecha_nacimiento']));
            $dias_diferencia = (strtotime(date('Y') . '-' . $cumple) - strtotime(date('Y-m-d'))) / 86400;
            
            if ($dias_diferencia >= 0 && $dias_diferencia <= 7) {
                $alertas[] = [
                    'tipo' => 'info',
                    'titulo' => 'Cumpleaños próximo',
                    'mensaje' => "{$empleado['nombre']} {$empleado['apellido']} cumple años " . 
                               ($dias_diferencia == 0 ? 'hoy' : "en {$dias_diferencia} día(s)"),
                    'icono' => 'fas fa-birthday-cake'
                ];
            }
        }
    }
    
    return array_slice($alertas, 0, 5); // Máximo 5 alertas
}
?>
