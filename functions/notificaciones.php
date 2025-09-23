<?php
/**
 * Funciones para Sistema de Notificaciones
 * Sistema de Gestión de Recursos Humanos
 */

// Datos mock de notificaciones
$notificaciones_mock = [
    1 => [
        'id' => 1,
        'usuario_id' => 1,
        'tipo' => 'documento',
        'titulo' => 'Nuevo documento requiere firma',
        'mensaje' => 'El documento "Manual del Empleado 2024" requiere tu firma electrónica.',
        'icono' => 'fas fa-file-signature',
        'color' => 'warning',
        'enlace' => '/documentos.php?id=1',
        'fecha_creacion' => '2024-01-20 09:30:00',
        'leida' => false,
        'importante' => true
    ],
    2 => [
        'id' => 2,
        'usuario_id' => 1,
        'tipo' => 'permiso',
        'titulo' => 'Solicitud de permiso aprobada',
        'mensaje' => 'Tu solicitud de permiso para el 25 de enero ha sido aprobada.',
        'icono' => 'fas fa-check-circle',
        'color' => 'success',
        'enlace' => '/permisos.php?id=5',
        'fecha_creacion' => '2024-01-18 14:20:00',
        'leida' => true,
        'importante' => false
    ],
    3 => [
        'id' => 3,
        'usuario_id' => 1,
        'tipo' => 'capacitacion',
        'titulo' => 'Nueva capacitación disponible',
        'mensaje' => 'Se ha programado una capacitación sobre "Seguridad Informática" para el próximo mes.',
        'icono' => 'fas fa-graduation-cap',
        'color' => 'info',
        'enlace' => '/capacitaciones.php?id=3',
        'fecha_creacion' => '2024-01-15 11:45:00',
        'leida' => false,
        'importante' => false
    ],
    4 => [
        'id' => 4,
        'usuario_id' => 1,
        'tipo' => 'nomina',
        'titulo' => 'Comprobante de pago disponible',
        'mensaje' => 'Tu comprobante de pago de enero ya está disponible para descarga.',
        'icono' => 'fas fa-money-bill-wave',
        'color' => 'primary',
        'enlace' => '/nomina.php?comprobante=202401',
        'fecha_creacion' => '2024-01-31 16:00:00',
        'leida' => false,
        'importante' => false
    ],
    5 => [
        'id' => 5,
        'usuario_id' => 3,
        'tipo' => 'muro',
        'titulo' => 'Nueva publicación en el muro',
        'mensaje' => 'Se ha publicado información sobre la "Celebración del Día del Empleado".',
        'icono' => 'fas fa-bullhorn',
        'color' => 'secondary',
        'enlace' => '/muro.php#publicacion-3',
        'fecha_creacion' => '2024-01-25 10:20:00',
        'leida' => false,
        'importante' => true
    ]
];

/**
 * Función para obtener notificaciones de un usuario
 * @param int $usuario_id
 * @param array $filtros - Filtros opcionales
 * @param int $limite - Número máximo de notificaciones
 * @return array
 */
function obtener_notificaciones_usuario($usuario_id, $filtros = [], $limite = 20) {
    global $notificaciones_mock;
    
    // Filtrar por usuario
    $notificaciones = array_filter($notificaciones_mock, function($notif) use ($usuario_id) {
        return $notif['usuario_id'] == $usuario_id;
    });
    
    // Aplicar filtros adicionales
    if (!empty($filtros['tipo'])) {
        $notificaciones = array_filter($notificaciones, function($notif) use ($filtros) {
            return $notif['tipo'] === $filtros['tipo'];
        });
    }
    
    if (isset($filtros['leida'])) {
        $notificaciones = array_filter($notificaciones, function($notif) use ($filtros) {
            return $notif['leida'] === $filtros['leida'];
        });
    }
    
    if (isset($filtros['importante'])) {
        $notificaciones = array_filter($notificaciones, function($notif) use ($filtros) {
            return $notif['importante'] === $filtros['importante'];
        });
    }
    
    // Ordenar por fecha de creación descendente
    uasort($notificaciones, function($a, $b) {
        return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
    });
    
    // Aplicar límite
    return array_slice($notificaciones, 0, $limite, true);
}

/**
 * Función para crear nueva notificación
 * @param array $datos
 * @return bool|int
 */
function crear_notificacion($datos) {
    global $notificaciones_mock;
    
    // Validar datos requeridos
    $campos_requeridos = ['usuario_id', 'tipo', 'titulo', 'mensaje'];
    foreach ($campos_requeridos as $campo) {
        if (empty($datos[$campo])) {
            return false;
        }
    }
    
    // Generar nuevo ID
    $nuevo_id = max(array_keys($notificaciones_mock)) + 1;
    
    // Configurar icono y color por defecto según el tipo
    $config_tipos = [
        'documento' => ['icono' => 'fas fa-file-alt', 'color' => 'warning'],
        'permiso' => ['icono' => 'fas fa-calendar-check', 'color' => 'info'],
        'capacitacion' => ['icono' => 'fas fa-graduation-cap', 'color' => 'primary'],
        'nomina' => ['icono' => 'fas fa-money-bill-wave', 'color' => 'success'],
        'muro' => ['icono' => 'fas fa-bullhorn', 'color' => 'secondary'],
        'sistema' => ['icono' => 'fas fa-cog', 'color' => 'dark'],
        'recordatorio' => ['icono' => 'fas fa-bell', 'color' => 'warning']
    ];
    
    $config = $config_tipos[$datos['tipo']] ?? $config_tipos['sistema'];
    
    $notificaciones_mock[$nuevo_id] = [
        'id' => $nuevo_id,
        'usuario_id' => $datos['usuario_id'],
        'tipo' => $datos['tipo'],
        'titulo' => $datos['titulo'],
        'mensaje' => $datos['mensaje'],
        'icono' => $datos['icono'] ?? $config['icono'],
        'color' => $datos['color'] ?? $config['color'],
        'enlace' => $datos['enlace'] ?? null,
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'leida' => false,
        'importante' => $datos['importante'] ?? false
    ];
    
    return $nuevo_id;
}

/**
 * Función para marcar notificación como leída
 * @param int $notificacion_id
 * @param int $usuario_id - Para verificar permisos
 * @return bool
 */
function marcar_notificacion_leida($notificacion_id, $usuario_id) {
    global $notificaciones_mock;
    
    if (!isset($notificaciones_mock[$notificacion_id])) {
        return false;
    }
    
    // Verificar que la notificación pertenece al usuario
    if ($notificaciones_mock[$notificacion_id]['usuario_id'] != $usuario_id) {
        return false;
    }
    
    $notificaciones_mock[$notificacion_id]['leida'] = true;
    return true;
}

/**
 * Función para marcar todas las notificaciones como leídas
 * @param int $usuario_id
 * @return int - Número de notificaciones marcadas
 */
function marcar_todas_notificaciones_leidas($usuario_id) {
    global $notificaciones_mock;
    
    $contador = 0;
    foreach ($notificaciones_mock as $id => $notificacion) {
        if ($notificacion['usuario_id'] == $usuario_id && !$notificacion['leida']) {
            $notificaciones_mock[$id]['leida'] = true;
            $contador++;
        }
    }
    
    return $contador;
}

/**
 * Función para eliminar notificación
 * @param int $notificacion_id
 * @param int $usuario_id - Para verificar permisos
 * @return bool
 */
function eliminar_notificacion($notificacion_id, $usuario_id) {
    global $notificaciones_mock;
    
    if (!isset($notificaciones_mock[$notificacion_id])) {
        return false;
    }
    
    // Verificar que la notificación pertenece al usuario
    if ($notificaciones_mock[$notificacion_id]['usuario_id'] != $usuario_id) {
        return false;
    }
    
    unset($notificaciones_mock[$notificacion_id]);
    return true;
}

/**
 * Función para contar notificaciones no leídas
 * @param int $usuario_id
 * @return int
 */
function contar_notificaciones_no_leidas($usuario_id) {
    global $notificaciones_mock;
    
    return count(array_filter($notificaciones_mock, function($notif) use ($usuario_id) {
        return $notif['usuario_id'] == $usuario_id && !$notif['leida'];
    }));
}

/**
 * Función para obtener estadísticas de notificaciones
 * @param int $usuario_id
 * @return array
 */
function obtener_estadisticas_notificaciones($usuario_id) {
    global $notificaciones_mock;
    
    $notificaciones_usuario = array_filter($notificaciones_mock, function($notif) use ($usuario_id) {
        return $notif['usuario_id'] == $usuario_id;
    });
    
    $total = count($notificaciones_usuario);
    $no_leidas = count(array_filter($notificaciones_usuario, function($notif) {
        return !$notif['leida'];
    }));
    $importantes = count(array_filter($notificaciones_usuario, function($notif) {
        return $notif['importante'];
    }));
    
    // Contar por tipo
    $por_tipo = [];
    foreach ($notificaciones_usuario as $notificacion) {
        $tipo = $notificacion['tipo'];
        $por_tipo[$tipo] = ($por_tipo[$tipo] ?? 0) + 1;
    }
    
    // Notificaciones recientes (últimos 7 días)
    $fecha_limite = date('Y-m-d H:i:s', strtotime('-7 days'));
    $recientes = count(array_filter($notificaciones_usuario, function($notif) use ($fecha_limite) {
        return $notif['fecha_creacion'] >= $fecha_limite;
    }));
    
    return [
        'total' => $total,
        'no_leidas' => $no_leidas,
        'leidas' => $total - $no_leidas,
        'importantes' => $importantes,
        'recientes' => $recientes,
        'por_tipo' => $por_tipo
    ];
}

/**
 * Función para crear notificaciones masivas
 * @param array $usuarios_ids - IDs de usuarios destinatarios
 * @param array $datos_notificacion - Datos de la notificación
 * @return int - Número de notificaciones creadas
 */
function crear_notificaciones_masivas($usuarios_ids, $datos_notificacion) {
    $contador = 0;
    
    foreach ($usuarios_ids as $usuario_id) {
        $datos_notificacion['usuario_id'] = $usuario_id;
        if (crear_notificacion($datos_notificacion)) {
            $contador++;
        }
    }
    
    return $contador;
}

/**
 * Función para obtener tipos de notificaciones
 * @return array
 */
function obtener_tipos_notificaciones() {
    return [
        'documento' => [
            'nombre' => 'Documentos',
            'icono' => 'fas fa-file-alt',
            'color' => 'warning',
            'descripcion' => 'Notificaciones sobre documentos y firmas'
        ],
        'permiso' => [
            'nombre' => 'Permisos',
            'icono' => 'fas fa-calendar-check',
            'color' => 'info',
            'descripcion' => 'Solicitudes y aprobaciones de permisos'
        ],
        'capacitacion' => [
            'nombre' => 'Capacitaciones',
            'icono' => 'fas fa-graduation-cap',
            'color' => 'primary',
            'descripcion' => 'Cursos y entrenamientos disponibles'
        ],
        'nomina' => [
            'nombre' => 'Nómina',
            'icono' => 'fas fa-money-bill-wave',
            'color' => 'success',
            'descripcion' => 'Información sobre pagos y comprobantes'
        ],
        'muro' => [
            'nombre' => 'Muro Empresarial',
            'icono' => 'fas fa-bullhorn',
            'color' => 'secondary',
            'descripcion' => 'Publicaciones y anuncios importantes'
        ],
        'sistema' => [
            'nombre' => 'Sistema',
            'icono' => 'fas fa-cog',
            'color' => 'dark',
            'descripcion' => 'Notificaciones del sistema'
        ],
        'recordatorio' => [
            'nombre' => 'Recordatorios',
            'icono' => 'fas fa-bell',
            'color' => 'warning',
            'descripcion' => 'Recordatorios y fechas importantes'
        ]
    ];
}

/**
 * Función para limpiar notificaciones antiguas
 * @param int $dias - Días de antigüedad para eliminar
 * @return int - Número de notificaciones eliminadas
 */
function limpiar_notificaciones_antiguas($dias = 30) {
    global $notificaciones_mock;
    
    $fecha_limite = date('Y-m-d H:i:s', strtotime("-$dias days"));
    $eliminadas = 0;
    
    foreach ($notificaciones_mock as $id => $notificacion) {
        if ($notificacion['fecha_creacion'] < $fecha_limite && $notificacion['leida']) {
            unset($notificaciones_mock[$id]);
            $eliminadas++;
        }
    }
    
    return $eliminadas;
}

/**
 * Función para obtener notificaciones recientes para el dashboard
 * @param int $usuario_id
 * @param int $limite
 * @return array
 */
function obtener_notificaciones_dashboard($usuario_id, $limite = 5) {
    $notificaciones = obtener_notificaciones_usuario($usuario_id, [], $limite);
    
    // Formatear para el dashboard
    $notificaciones_formateadas = [];
    foreach ($notificaciones as $notificacion) {
        $tiempo_transcurrido = tiempo_transcurrido($notificacion['fecha_creacion']);
        
        $notificaciones_formateadas[] = [
            'id' => $notificacion['id'],
            'titulo' => $notificacion['titulo'],
            'mensaje' => $notificacion['mensaje'],
            'icono' => $notificacion['icono'],
            'color' => $notificacion['color'],
            'enlace' => $notificacion['enlace'],
            'tiempo' => $tiempo_transcurrido,
            'leida' => $notificacion['leida'],
            'importante' => $notificacion['importante']
        ];
    }
    
    return $notificaciones_formateadas;
}

/**
 * Función auxiliar para calcular tiempo transcurrido
 * @param string $fecha
 * @return string
 */
function tiempo_transcurrido($fecha) {
    $tiempo = time() - strtotime($fecha);
    
    if ($tiempo < 60) {
        return 'Hace menos de 1 minuto';
    } elseif ($tiempo < 3600) {
        $minutos = floor($tiempo / 60);
        return "Hace $minutos minuto" . ($minutos > 1 ? 's' : '');
    } elseif ($tiempo < 86400) {
        $horas = floor($tiempo / 3600);
        return "Hace $horas hora" . ($horas > 1 ? 's' : '');
    } elseif ($tiempo < 2592000) {
        $dias = floor($tiempo / 86400);
        return "Hace $dias día" . ($dias > 1 ? 's' : '');
    } else {
        return date('d/m/Y', strtotime($fecha));
    }
}
?>
