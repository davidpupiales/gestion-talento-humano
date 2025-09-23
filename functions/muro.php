<?php
/**
 * Funciones para el Muro/Pizarra Empresarial
 * Sistema de Gestión de Recursos Humanos
 */

// Datos mock de publicaciones del muro
$publicaciones_mock = [
    1 => [
        'id' => 1,
        'titulo' => 'Bienvenida al nuevo sistema HRMS',
        'contenido' => 'Estimados colaboradores, nos complace anunciar el lanzamiento de nuestro nuevo sistema de gestión de recursos humanos. Este sistema nos permitirá mejorar la comunicación y optimizar nuestros procesos internos.',
        'tipo' => 'Anuncio',
        'autor_id' => 2,
        'fecha_creacion' => '2024-01-15 09:00:00',
        'fecha_actualizacion' => '2024-01-15 09:00:00',
        'estado' => 'Activo',
        'prioridad' => 'Alta',
        'archivos_adjuntos' => [
            [
                'nombre' => 'guia_usuario_hrms.pdf',
                'tipo' => 'application/pdf',
                'tamaño' => '2.1 MB',
                'url' => '/uploads/guia_usuario_hrms.pdf'
            ]
        ],
        'enlaces' => [
            [
                'titulo' => 'Tutorial en video',
                'url' => 'https://youtube.com/watch?v=ejemplo',
                'descripcion' => 'Video explicativo del nuevo sistema'
            ]
        ],
        'likes' => 15,
        'comentarios_count' => 8,
        'visto_por' => [1, 3, 4]
    ],
    2 => [
        'id' => 2,
        'titulo' => 'Política de Trabajo Híbrido - Actualización',
        'contenido' => 'Se ha actualizado nuestra política de trabajo híbrido. A partir del 1 de febrero, todos los empleados podrán trabajar hasta 3 días desde casa por semana. Por favor, coordinen con sus supervisores directos.',
        'tipo' => 'Política',
        'autor_id' => 2,
        'fecha_creacion' => '2024-01-20 14:30:00',
        'fecha_actualizacion' => '2024-01-20 14:30:00',
        'estado' => 'Activo',
        'prioridad' => 'Media',
        'archivos_adjuntos' => [
            [
                'nombre' => 'politica_trabajo_hibrido_v2.pdf',
                'tipo' => 'application/pdf',
                'tamaño' => '1.5 MB',
                'url' => '/uploads/politica_trabajo_hibrido_v2.pdf'
            ]
        ],
        'enlaces' => [],
        'likes' => 23,
        'comentarios_count' => 12,
        'visto_por' => [1, 3]
    ],
    3 => [
        'id' => 3,
        'titulo' => 'Celebración del Día del Empleado',
        'contenido' => '¡Este viernes celebraremos el Día del Empleado! Habrá almuerzo especial en el comedor principal a las 12:30 PM. También tendremos actividades recreativas en el área de descanso. ¡Los esperamos!',
        'tipo' => 'Evento',
        'autor_id' => 2,
        'fecha_creacion' => '2024-01-25 10:15:00',
        'fecha_actualizacion' => '2024-01-25 10:15:00',
        'estado' => 'Activo',
        'prioridad' => 'Media',
        'archivos_adjuntos' => [],
        'enlaces' => [],
        'likes' => 31,
        'comentarios_count' => 18,
        'visto_por' => [1, 3, 4]
    ]
];

// Datos mock de comentarios
$comentarios_mock = [
    1 => [
        'id' => 1,
        'publicacion_id' => 1,
        'autor_id' => 1,
        'contenido' => 'Excelente iniciativa. El sistema se ve muy profesional y fácil de usar.',
        'fecha_creacion' => '2024-01-15 10:30:00',
        'estado' => 'Activo',
        'likes' => 3
    ],
    2 => [
        'id' => 2,
        'publicacion_id' => 1,
        'autor_id' => 3,
        'contenido' => '¿Habrá capacitación para aprender a usar todas las funciones?',
        'fecha_creacion' => '2024-01-15 11:45:00',
        'estado' => 'Activo',
        'likes' => 1
    ],
    3 => [
        'id' => 3,
        'publicacion_id' => 2,
        'autor_id' => 1,
        'contenido' => 'Perfecto, esto nos dará más flexibilidad para organizar nuestro tiempo.',
        'fecha_creacion' => '2024-01-20 15:20:00',
        'estado' => 'Activo',
        'likes' => 5
    ]
];

/**
 * Función para obtener todas las publicaciones del muro
 * @param array $filtros - Filtros opcionales
 * @param int $limite - Número máximo de publicaciones
 * @param int $offset - Desplazamiento para paginación
 * @return array
 */
function obtener_publicaciones_muro($filtros = [], $limite = 10, $offset = 0) {
    global $publicaciones_mock;
    
    $publicaciones = $publicaciones_mock;
    
    // Aplicar filtros
    if (!empty($filtros['tipo'])) {
        $publicaciones = array_filter($publicaciones, function($pub) use ($filtros) {
            return $pub['tipo'] === $filtros['tipo'];
        });
    }
    
    if (!empty($filtros['autor_id'])) {
        $publicaciones = array_filter($publicaciones, function($pub) use ($filtros) {
            return $pub['autor_id'] == $filtros['autor_id'];
        });
    }
    
    if (!empty($filtros['prioridad'])) {
        $publicaciones = array_filter($publicaciones, function($pub) use ($filtros) {
            return $pub['prioridad'] === $filtros['prioridad'];
        });
    }
    
    // Ordenar por fecha de creación descendente
    uasort($publicaciones, function($a, $b) {
        return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
    });
    
    // Aplicar paginación
    $publicaciones = array_slice($publicaciones, $offset, $limite, true);
    
    // Agregar información del autor a cada publicación
    foreach ($publicaciones as &$publicacion) {
        $autor = obtener_empleado_por_id($publicacion['autor_id']);
        $publicacion['autor'] = $autor;
    }
    
    return array_values($publicaciones);
}

/**
 * Función para obtener una publicación por ID
 * @param int $id
 * @return array|null
 */
function obtener_publicacion_por_id($id) {
    global $publicaciones_mock;
    
    if (!isset($publicaciones_mock[$id])) {
        return null;
    }
    
    $publicacion = $publicaciones_mock[$id];
    $autor = obtener_empleado_por_id($publicacion['autor_id']);
    $publicacion['autor'] = $autor;
    
    return $publicacion;
}

/**
 * Función para crear nueva publicación
 * @param array $datos
 * @return bool|int
 */
function crear_publicacion_muro($datos) {
    global $publicaciones_mock;
    
    // Validar datos requeridos
    if (empty($datos['titulo']) || empty($datos['contenido']) || empty($datos['autor_id'])) {
        return false;
    }
    
    // Generar nuevo ID
    $nuevo_id = max(array_keys($publicaciones_mock)) + 1;
    
    $publicaciones_mock[$nuevo_id] = [
        'id' => $nuevo_id,
        'titulo' => $datos['titulo'],
        'contenido' => $datos['contenido'],
        'tipo' => $datos['tipo'] ?? 'Anuncio',
        'autor_id' => $datos['autor_id'],
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'fecha_actualizacion' => date('Y-m-d H:i:s'),
        'estado' => 'Activo',
        'prioridad' => $datos['prioridad'] ?? 'Media',
        'archivos_adjuntos' => $datos['archivos_adjuntos'] ?? [],
        'enlaces' => $datos['enlaces'] ?? [],
        'likes' => 0,
        'comentarios_count' => 0,
        'visto_por' => []
    ];
    
    return $nuevo_id;
}

/**
 * Función para actualizar publicación
 * @param int $id
 * @param array $datos
 * @return bool
 */
function actualizar_publicacion_muro($id, $datos) {
    global $publicaciones_mock;
    
    if (!isset($publicaciones_mock[$id])) {
        return false;
    }
    
    // Campos actualizables
    $campos_actualizables = ['titulo', 'contenido', 'tipo', 'prioridad', 'archivos_adjuntos', 'enlaces'];
    
    foreach ($campos_actualizables as $campo) {
        if (isset($datos[$campo])) {
            $publicaciones_mock[$id][$campo] = $datos[$campo];
        }
    }
    
    $publicaciones_mock[$id]['fecha_actualizacion'] = date('Y-m-d H:i:s');
    
    return true;
}

/**
 * Función para eliminar publicación
 * @param int $id
 * @return bool
 */
function eliminar_publicacion_muro($id) {
    global $publicaciones_mock;
    
    if (!isset($publicaciones_mock[$id])) {
        return false;
    }
    
    $publicaciones_mock[$id]['estado'] = 'Eliminado';
    return true;
}

/**
 * Función para obtener comentarios de una publicación
 * @param int $publicacion_id
 * @return array
 */
function obtener_comentarios_publicacion($publicacion_id) {
    global $comentarios_mock;
    
    $comentarios = array_filter($comentarios_mock, function($com) use ($publicacion_id) {
        return $com['publicacion_id'] == $publicacion_id && $com['estado'] === 'Activo';
    });
    
    // Ordenar por fecha de creación ascendente
    uasort($comentarios, function($a, $b) {
        return strtotime($a['fecha_creacion']) - strtotime($b['fecha_creacion']);
    });
    
    // Agregar información del autor a cada comentario
    foreach ($comentarios as &$comentario) {
        $autor = obtener_empleado_por_id($comentario['autor_id']);
        $comentario['autor'] = $autor;
    }
    
    return array_values($comentarios);
}

/**
 * Función para agregar comentario a publicación
 * @param int $publicacion_id
 * @param int $autor_id
 * @param string $contenido
 * @return bool|int
 */
function agregar_comentario_publicacion($publicacion_id, $autor_id, $contenido) {
    global $comentarios_mock, $publicaciones_mock;
    
    // Validar que la publicación existe
    if (!isset($publicaciones_mock[$publicacion_id])) {
        return false;
    }
    
    if (empty($contenido)) {
        return false;
    }
    
    // Generar nuevo ID
    $nuevo_id = max(array_keys($comentarios_mock)) + 1;
    
    $comentarios_mock[$nuevo_id] = [
        'id' => $nuevo_id,
        'publicacion_id' => $publicacion_id,
        'autor_id' => $autor_id,
        'contenido' => $contenido,
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'estado' => 'Activo',
        'likes' => 0
    ];
    
    // Actualizar contador de comentarios en la publicación
    $publicaciones_mock[$publicacion_id]['comentarios_count']++;
    
    return $nuevo_id;
}

/**
 * Función para dar like a una publicación
 * @param int $publicacion_id
 * @param int $usuario_id
 * @return bool
 */
function dar_like_publicacion($publicacion_id, $usuario_id) {
    global $publicaciones_mock;
    
    if (!isset($publicaciones_mock[$publicacion_id])) {
        return false;
    }
    
    // En una implementación real, se verificaría si el usuario ya dio like
    $publicaciones_mock[$publicacion_id]['likes']++;
    
    return true;
}

/**
 * Función para marcar publicación como vista
 * @param int $publicacion_id
 * @param int $usuario_id
 * @return bool
 */
function marcar_publicacion_vista($publicacion_id, $usuario_id) {
    global $publicaciones_mock;
    
    if (!isset($publicaciones_mock[$publicacion_id])) {
        return false;
    }
    
    if (!in_array($usuario_id, $publicaciones_mock[$publicacion_id]['visto_por'])) {
        $publicaciones_mock[$publicacion_id]['visto_por'][] = $usuario_id;
    }
    
    return true;
}

/**
 * Función para obtener tipos de publicaciones
 * @return array
 */
function obtener_tipos_publicaciones() {
    return [
        'Anuncio',
        'Política',
        'Evento',
        'Comunicado',
        'Noticia',
        'Recordatorio',
        'Celebración',
        'Capacitación'
    ];
}

/**
 * Función para obtener estadísticas del muro
 * @return array
 */
function obtener_estadisticas_muro() {
    global $publicaciones_mock, $comentarios_mock;
    
    $total_publicaciones = count(array_filter($publicaciones_mock, function($pub) {
        return $pub['estado'] === 'Activo';
    }));
    
    $total_comentarios = count(array_filter($comentarios_mock, function($com) {
        return $com['estado'] === 'Activo';
    }));
    
    $total_likes = array_sum(array_column($publicaciones_mock, 'likes'));
    
    // Publicaciones por tipo
    $por_tipo = [];
    foreach ($publicaciones_mock as $publicacion) {
        if ($publicacion['estado'] === 'Activo') {
            $tipo = $publicacion['tipo'];
            $por_tipo[$tipo] = ($por_tipo[$tipo] ?? 0) + 1;
        }
    }
    
    // Publicaciones recientes (últimos 7 días)
    $fecha_limite = date('Y-m-d H:i:s', strtotime('-7 days'));
    $publicaciones_recientes = count(array_filter($publicaciones_mock, function($pub) use ($fecha_limite) {
        return $pub['estado'] === 'Activo' && $pub['fecha_creacion'] >= $fecha_limite;
    }));
    
    return [
        'total_publicaciones' => $total_publicaciones,
        'total_comentarios' => $total_comentarios,
        'total_likes' => $total_likes,
        'publicaciones_recientes' => $publicaciones_recientes,
        'por_tipo' => $por_tipo,
        'promedio_comentarios' => $total_publicaciones > 0 ? round($total_comentarios / $total_publicaciones, 1) : 0,
        'promedio_likes' => $total_publicaciones > 0 ? round($total_likes / $total_publicaciones, 1) : 0
    ];
}

/**
 * Función para buscar en publicaciones
 * @param string $termino
 * @return array
 */
function buscar_publicaciones($termino) {
    global $publicaciones_mock;
    
    $termino = strtolower($termino);
    
    $resultados = array_filter($publicaciones_mock, function($pub) use ($termino) {
        return $pub['estado'] === 'Activo' && (
            strpos(strtolower($pub['titulo']), $termino) !== false ||
            strpos(strtolower($pub['contenido']), $termino) !== false
        );
    });
    
    // Agregar información del autor
    foreach ($resultados as &$publicacion) {
        $autor = obtener_empleado_por_id($publicacion['autor_id']);
        $publicacion['autor'] = $autor;
    }
    
    return array_values($resultados);
}
?>
