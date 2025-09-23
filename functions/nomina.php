<?php
/**
 * Funciones para Gestión de Nómina
 * Sistema de Gestión de Recursos Humanos
 */

// Datos mock de nómina para desarrollo
$nomina_mock = [
    1 => [
        'id' => 1,
        'empleado_id' => 1,
        'periodo' => '2024-01',
        'salario_base' => 75000.00,
        'horas_extras' => 10,
        'valor_hora_extra' => 25.00,
        'bonificaciones' => 500.00,
        'deducciones' => 1200.00,
        'seguro_social' => 2250.00,
        'impuesto_renta' => 8500.00,
        'salario_neto' => 63550.00,
        'estado' => 'Pagado',
        'fecha_pago' => '2024-01-31',
        'metodo_pago' => 'Transferencia',
        'observaciones' => 'Pago regular mensual'
    ],
    2 => [
        'id' => 2,
        'empleado_id' => 2,
        'periodo' => '2024-01',
        'salario_base' => 95000.00,
        'horas_extras' => 5,
        'valor_hora_extra' => 35.00,
        'bonificaciones' => 1000.00,
        'deducciones' => 800.00,
        'seguro_social' => 2850.00,
        'impuesto_renta' => 12500.00,
        'salario_neto' => 80825.00,
        'estado' => 'Pagado',
        'fecha_pago' => '2024-01-31',
        'metodo_pago' => 'Transferencia',
        'observaciones' => 'Incluye bono de gestión'
    ]
];

/**
 * Función para obtener registros de nómina
 * @param array $filtros - Filtros opcionales
 * @return array
 */
function obtener_nomina($filtros = []) {
    global $nomina_mock;
    
    $registros = $nomina_mock;
    
    // Aplicar filtros
    if (!empty($filtros['empleado_id'])) {
        $registros = array_filter($registros, function($reg) use ($filtros) {
            return $reg['empleado_id'] == $filtros['empleado_id'];
        });
    }
    
    if (!empty($filtros['periodo'])) {
        $registros = array_filter($registros, function($reg) use ($filtros) {
            return $reg['periodo'] === $filtros['periodo'];
        });
    }
    
    if (!empty($filtros['estado'])) {
        $registros = array_filter($registros, function($reg) use ($filtros) {
            return $reg['estado'] === $filtros['estado'];
        });
    }
    
    return array_values($registros);
}

/**
 * Función para calcular nómina de un empleado
 * @param int $empleado_id
 * @param string $periodo - Formato YYYY-MM
 * @param array $datos_adicionales
 * @return array|false
 */
function calcular_nomina($empleado_id, $periodo, $datos_adicionales = []) {
    // Obtener datos del empleado
    $empleado = obtener_empleado_por_id($empleado_id);
    if (!$empleado) {
        return false;
    }
    
    $salario_base = $empleado['salario'];
    $horas_extras = $datos_adicionales['horas_extras'] ?? 0;
    $valor_hora_extra = ($salario_base / 160) * 1.5; // 160 horas mensuales, 50% extra
    $bonificaciones = $datos_adicionales['bonificaciones'] ?? 0;
    $deducciones = $datos_adicionales['deducciones'] ?? 0;
    
    // Cálculos de impuestos y deducciones
    $total_horas_extras = $horas_extras * $valor_hora_extra;
    $salario_bruto = $salario_base + $total_horas_extras + $bonificaciones;
    
    // Seguro social (3% del salario bruto)
    $seguro_social = $salario_bruto * 0.03;
    
    // Impuesto sobre la renta (progresivo simplificado)
    $impuesto_renta = calcular_impuesto_renta($salario_bruto);
    
    // Salario neto
    $total_deducciones = $seguro_social + $impuesto_renta + $deducciones;
    $salario_neto = $salario_bruto - $total_deducciones;
    
    return [
        'empleado_id' => $empleado_id,
        'periodo' => $periodo,
        'salario_base' => $salario_base,
        'horas_extras' => $horas_extras,
        'valor_hora_extra' => round($valor_hora_extra, 2),
        'total_horas_extras' => round($total_horas_extras, 2),
        'bonificaciones' => $bonificaciones,
        'salario_bruto' => round($salario_bruto, 2),
        'seguro_social' => round($seguro_social, 2),
        'impuesto_renta' => round($impuesto_renta, 2),
        'deducciones' => $deducciones,
        'total_deducciones' => round($total_deducciones, 2),
        'salario_neto' => round($salario_neto, 2)
    ];
}

/**
 * Función para calcular impuesto sobre la renta
 * @param float $salario_bruto
 * @return float
 */
function calcular_impuesto_renta($salario_bruto) {
    // Tabla de impuestos simplificada
    if ($salario_bruto <= 30000) {
        return 0; // Exento
    } elseif ($salario_bruto <= 60000) {
        return ($salario_bruto - 30000) * 0.10; // 10%
    } elseif ($salario_bruto <= 100000) {
        return 3000 + (($salario_bruto - 60000) * 0.15); // 15%
    } else {
        return 9000 + (($salario_bruto - 100000) * 0.20); // 20%
    }
}

/**
 * Función para procesar nómina masiva
 * @param string $periodo
 * @param array $empleados_ids
 * @return array
 */
function procesar_nomina_masiva($periodo, $empleados_ids = []) {
    $resultados = [];
    
    // Si no se especifican empleados, procesar todos los activos
    if (empty($empleados_ids)) {
        $empleados = obtener_empleados(['estado' => 'Activo']);
        $empleados_ids = array_column($empleados, 'id');
    }
    
    foreach ($empleados_ids as $empleado_id) {
        $calculo = calcular_nomina($empleado_id, $periodo);
        if ($calculo) {
            $resultados[] = $calculo;
        }
    }
    
    return $resultados;
}

/**
 * Función para generar reporte de nómina
 * @param string $periodo
 * @return array
 */
function generar_reporte_nomina($periodo) {
    $nomina = obtener_nomina(['periodo' => $periodo]);
    
    $total_empleados = count($nomina);
    $total_salarios_brutos = 0;
    $total_deducciones = 0;
    $total_salarios_netos = 0;
    
    foreach ($nomina as $registro) {
        $salario_bruto = $registro['salario_base'] + 
                        ($registro['horas_extras'] * $registro['valor_hora_extra']) + 
                        $registro['bonificaciones'];
        $total_salarios_brutos += $salario_bruto;
        $total_deducciones += $registro['seguro_social'] + $registro['impuesto_renta'] + $registro['deducciones'];
        $total_salarios_netos += $registro['salario_neto'];
    }
    
    return [
        'periodo' => $periodo,
        'total_empleados' => $total_empleados,
        'total_salarios_brutos' => round($total_salarios_brutos, 2),
        'total_deducciones' => round($total_deducciones, 2),
        'total_salarios_netos' => round($total_salarios_netos, 2),
        'promedio_salario' => $total_empleados > 0 ? round($total_salarios_netos / $total_empleados, 2) : 0
    ];
}

/**
 * Función para obtener historial de pagos de un empleado
 * @param int $empleado_id
 * @param int $limite
 * @return array
 */
function obtener_historial_pagos($empleado_id, $limite = 12) {
    global $nomina_mock;
    
    $historial = array_filter($nomina_mock, function($reg) use ($empleado_id) {
        return $reg['empleado_id'] == $empleado_id;
    });
    
    // Ordenar por periodo descendente
    usort($historial, function($a, $b) {
        return strcmp($b['periodo'], $a['periodo']);
    });
    
    return array_slice($historial, 0, $limite);
}

/**
 * Función para generar comprobante de pago
 * @param int $nomina_id
 * @return array|false
 */
function generar_comprobante_pago($nomina_id) {
    global $nomina_mock;
    
    if (!isset($nomina_mock[$nomina_id])) {
        return false;
    }
    
    $registro = $nomina_mock[$nomina_id];
    $empleado = obtener_empleado_por_id($registro['empleado_id']);
    
    if (!$empleado) {
        return false;
    }
    
    return [
        'comprobante_id' => 'COMP-' . str_pad($nomina_id, 6, '0', STR_PAD_LEFT),
        'empleado' => $empleado,
        'nomina' => $registro,
        'fecha_generacion' => date('Y-m-d H:i:s'),
        'empresa' => [
            'nombre' => 'Mi Empresa S.A.',
            'ruc' => '12345678901',
            'direccion' => 'Calle Principal 123, Ciudad'
        ]
    ];
}

/**
 * Función para obtener estadísticas de nómina
 * @param string $periodo
 * @return array
 */
function obtener_estadisticas_nomina($periodo = null) {
    global $nomina_mock;
    
    $registros = $nomina_mock;
    
    if ($periodo) {
        $registros = array_filter($registros, function($reg) use ($periodo) {
            return $reg['periodo'] === $periodo;
        });
    }
    
    $total_registros = count($registros);
    $pagados = count(array_filter($registros, function($reg) {
        return $reg['estado'] === 'Pagado';
    }));
    $pendientes = $total_registros - $pagados;
    
    $total_monto = array_sum(array_column($registros, 'salario_neto'));
    
    return [
        'total_registros' => $total_registros,
        'pagados' => $pagados,
        'pendientes' => $pendientes,
        'total_monto' => round($total_monto, 2),
        'promedio_salario' => $total_registros > 0 ? round($total_monto / $total_registros, 2) : 0
    ];
}
?>
