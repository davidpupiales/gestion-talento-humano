<?php
/**
 * Funciones para Gestión de Documentos
 * Sistema de Gestión de Recursos Humanos
 */

// Datos mock de documentos para desarrollo
$documentos_mock = [
    1 => [
        'id' => 1,
        'titulo' => 'Manual del Empleado 2024',
        'descripcion' => 'Manual actualizado con políticas y procedimientos de la empresa',
        'tipo' => 'Manual',
        'categoria' => 'Políticas',
        'archivo' => 'manual_empleado_2024.pdf',
        'tamaño' => '2.5 MB',
        'fecha_creacion' => '2024-01-15 10:30:00',
        'creado_por' => 2,
        'requiere_firma' => true,
        'estado' => 'Activo',
        'version' => '1.0',
        'firmas_requeridas' => 'Todos los empleados',
        'fecha_vencimiento' => '2024-12-31'
    ],
    2 => [
        'id' => 2,
        'titulo' => 'Política de Trabajo Remoto',
        'descripcion' => 'Nuevas directrices para el trabajo desde casa',
        'tipo' => 'Política',
        'categoria' => 'Operaciones',
        'archivo' => 'politica_trabajo_remoto.pdf',
        'tamaño' => '1.2 MB',
        'fecha_creacion' => '2024-02-01 14:20:00',
        'creado_por' => 2,
        'requiere_firma' => true,
        'estado' => 'Activo',
        'version' => '2.1',
        'firmas_requeridas' => 'Empleados de Desarrollo',
        'fecha_vencimiento' => null
    ],
    3 => [
        'id' => 3,
        'titulo' => 'Formato de Solicitud de Vacaciones',
        'descripcion' => 'Formulario para solicitar días de vacaciones',
        'tipo' => 'Formulario',
        'categoria' => 'RRHH',
        'archivo' => 'solicitud_vacaciones.pdf',
        'tamaño' => '0.8 MB',
        'fecha_creacion' => '2024-01-10 09:15:00',
        'creado_por' => 2,
        'requiere_firma' => false,
        'estado' => 'Activo',
        'version' => '1.2',
        'firmas_requeridas' => null,
        'fecha_vencimiento' => null
    ]
];

// Datos mock de firmas de documentos
$firmas_documentos_mock = [
    1 => [
        'id' => 1,
        'documento_id' => 1,
        'empleado_id' => 1,
        'fecha_firma' => '2024-01-20 16:45:00',
        'ip_address' => '192.168.1.100',
        'estado' => 'Firmado',
        'observaciones' => 'Documento leído y aceptado'
    ],
    2 => [
        'id' => 2,
        'documento_id' => 1,
        'empleado_id' => 3,
        'fecha_firma' => null,
        'ip_address' => null,
        'estado' => 'Pendiente',
        'observaciones' => null
    ],
    3 => [
        'id' => 3,
        'documento_id' => 2,
        'empleado_id' => 1,
        'fecha_firma' => '2024-02-05 11:30:00',
        'ip_address' => '192.168.1.101',
        'estado' => 'Firmado',
        'observaciones' => 'Política revisada y aceptada'
    ]
];

/**
 * Función para obtener todos los documentos
 * @param array $filtros - Filtros opcionales
 * @return array
 */
function obtener_documentos($filtros = []) {
    global $documentos_mock;
    
    $documentos = $documentos_mock;
    
    // Aplicar filtros
    if (!empty($filtros['tipo'])) {
        $documentos = array_filter($documentos, function($doc) use ($filtros) {
            return $doc['tipo'] === $filtros['tipo'];
        });
    }
    
    if (!empty($filtros['categoria'])) {
        $documentos = array_filter($documentos, function($doc) use ($filtros) {
            return $doc['categoria'] === $filtros['categoria'];
        });
    }
    
    if (!empty($filtros['estado'])) {
        $documentos = array_filter($documentos, function($doc) use ($filtros) {
            return $doc['estado'] === $filtros['estado'];
        });
    }
    
    if (!empty($filtros['requiere_firma'])) {
        $documentos = array_filter($documentos, function($doc) use ($filtros) {
            return $doc['requiere_firma'] === ($filtros['requiere_firma'] === 'true');
        });
    }
    
    return array_values($documentos);
}

/**
 * Función para obtener un documento por ID
 * @param int $id
 * @return array|null
 */
function obtener_documento_por_id($id) {
    global $documentos_mock;
    return $documentos_mock[$id] ?? null;
}

/**
 * Función para crear nuevo documento
 * @param array $datos
 * @return bool|int
 */
function crear_documento($datos) {
    global $documentos_mock;
    
    // Validar datos requeridos
    $campos_requeridos = ['titulo', 'tipo', 'categoria'];
    foreach ($campos_requeridos as $campo) {
        if (empty($datos[$campo])) {
            return false;
        }
    }
    
    // Generar nuevo ID
    $nuevo_id = max(array_keys($documentos_mock)) + 1;
    
    $documentos_mock[$nuevo_id] = [
        'id' => $nuevo_id,
        'titulo' => $datos['titulo'],
        'descripcion' => $datos['descripcion'] ?? '',
        'tipo' => $datos['tipo'],
        'categoria' => $datos['categoria'],
        'archivo' => $datos['archivo'] ?? '',
        'tamaño' => $datos['tamaño'] ?? '0 MB',
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'creado_por' => $datos['creado_por'] ?? 1,
        'requiere_firma' => $datos['requiere_firma'] ?? false,
        'estado' => 'Activo',
        'version' => $datos['version'] ?? '1.0',
        'firmas_requeridas' => $datos['firmas_requeridas'] ?? null,
        'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null
    ];
    
    return $nuevo_id;
}

/**
 * Función para obtener documentos pendientes de firma por empleado
 * @param int $empleado_id
 * @return array
 */
function obtener_documentos_pendientes_firma($empleado_id) {
    global $documentos_mock, $firmas_documentos_mock;
    
    $documentos_pendientes = [];
    
    foreach ($documentos_mock as $documento) {
        if (!$documento['requiere_firma']) {
            continue;
        }
        
        // Verificar si el empleado ya firmó este documento
        $ya_firmado = false;
        foreach ($firmas_documentos_mock as $firma) {
            if ($firma['documento_id'] == $documento['id'] && 
                $firma['empleado_id'] == $empleado_id && 
                $firma['estado'] === 'Firmado') {
                $ya_firmado = true;
                break;
            }
        }
        
        if (!$ya_firmado) {
            $documentos_pendientes[] = $documento;
        }
    }
    
    return $documentos_pendientes;
}

/**
 * Función para firmar documento
 * @param int $documento_id
 * @param int $empleado_id
 * @param string $observaciones
 * @return bool
 */
function firmar_documento($documento_id, $empleado_id, $observaciones = '') {
    global $firmas_documentos_mock;
    
    // Verificar que el documento existe y requiere firma
    $documento = obtener_documento_por_id($documento_id);
    if (!$documento || !$documento['requiere_firma']) {
        return false;
    }
    
    // Verificar si ya existe una firma pendiente
    $firma_existente = null;
    foreach ($firmas_documentos_mock as $id => $firma) {
        if ($firma['documento_id'] == $documento_id && $firma['empleado_id'] == $empleado_id) {
            $firma_existente = $id;
            break;
        }
    }
    
    if ($firma_existente) {
        // Actualizar firma existente
        $firmas_documentos_mock[$firma_existente]['fecha_firma'] = date('Y-m-d H:i:s');
        $firmas_documentos_mock[$firma_existente]['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $firmas_documentos_mock[$firma_existente]['estado'] = 'Firmado';
        $firmas_documentos_mock[$firma_existente]['observaciones'] = $observaciones;
    } else {
        // Crear nueva firma
        $nuevo_id = max(array_keys($firmas_documentos_mock)) + 1;
        $firmas_documentos_mock[$nuevo_id] = [
            'id' => $nuevo_id,
            'documento_id' => $documento_id,
            'empleado_id' => $empleado_id,
            'fecha_firma' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'estado' => 'Firmado',
            'observaciones' => $observaciones
        ];
    }
    
    return true;
}

/**
 * Función para obtener estadísticas de firmas de un documento
 * @param int $documento_id
 * @return array
 */
function obtener_estadisticas_firmas($documento_id) {
    global $firmas_documentos_mock;
    
    $firmas = array_filter($firmas_documentos_mock, function($firma) use ($documento_id) {
        return $firma['documento_id'] == $documento_id;
    });
    
    $total_firmas = count($firmas);
    $firmados = count(array_filter($firmas, function($firma) {
        return $firma['estado'] === 'Firmado';
    }));
    $pendientes = $total_firmas - $firmados;
    
    return [
        'total_firmas' => $total_firmas,
        'firmados' => $firmados,
        'pendientes' => $pendientes,
        'porcentaje_completado' => $total_firmas > 0 ? round(($firmados / $total_firmas) * 100, 1) : 0
    ];
}

/**
 * Función para obtener tipos de documentos
 * @return array
 */
function obtener_tipos_documentos() {
    return [
        'Manual',
        'Política',
        'Procedimiento',
        'Formulario',
        'Contrato',
        'Comunicado',
        'Reglamento',
        'Instructivo'
    ];
}

/**
 * Función para obtener categorías de documentos
 * @return array
 */
function obtener_categorias_documentos() {
    return [
        'RRHH',
        'Políticas',
        'Operaciones',
        'Seguridad',
        'Calidad',
        'Finanzas',
        'Legal',
        'Tecnología'
    ];
}

/**
 * Función para generar reporte de documentos
 * @return array
 */
function generar_reporte_documentos() {
    global $documentos_mock, $firmas_documentos_mock;
    
    $total_documentos = count($documentos_mock);
    $documentos_con_firma = count(array_filter($documentos_mock, function($doc) {
        return $doc['requiere_firma'];
    }));
    
    $total_firmas = count($firmas_documentos_mock);
    $firmas_completadas = count(array_filter($firmas_documentos_mock, function($firma) {
        return $firma['estado'] === 'Firmado';
    }));
    
    // Documentos por tipo
    $por_tipo = [];
    foreach ($documentos_mock as $documento) {
        $tipo = $documento['tipo'];
        $por_tipo[$tipo] = ($por_tipo[$tipo] ?? 0) + 1;
    }
    
    return [
        'total_documentos' => $total_documentos,
        'documentos_con_firma' => $documentos_con_firma,
        'total_firmas' => $total_firmas,
        'firmas_completadas' => $firmas_completadas,
        'firmas_pendientes' => $total_firmas - $firmas_completadas,
        'porcentaje_firmas' => $total_firmas > 0 ? round(($firmas_completadas / $total_firmas) * 100, 1) : 0,
        'por_tipo' => $por_tipo
    ];
}
?>
