<?php
/**
 * functions/nomina.php
 *
 * Módulo con lógica pura para cálculos de nómina.
 * Este archivo NO contiene datos mock ni dependencias globales. Las
 * funciones reciben los datos necesarios por parámetro para facilitar
 * pruebas unitarias y reutilización.
 */

/**
 * Calcula impuesto sobre la renta (simplificado, paramétrico)
 * @param float $salario_bruto
 * @return float
 */
function calcular_impuesto_renta(float $salario_bruto): float {
    if ($salario_bruto <= 30000.0) return 0.0;
    if ($salario_bruto <= 60000.0) return ($salario_bruto - 30000.0) * 0.10;
    if ($salario_bruto <= 100000.0) return 3000.0 + (($salario_bruto - 60000.0) * 0.15);
    return 9000.0 + (($salario_bruto - 100000.0) * 0.20);
}


/**
 * Calcula la nómina de un empleado a partir de datos proporcionados.
 * No realiza lecturas de DB ni usa variables globales.
 *
 * $empleado debe contener al menos: ['id' => int, 'salario' => float]
 * $datos_adicionales puede incluir: 'horas_extras', 'bonificaciones', 'deducciones'
 *
 * @param array $empleado
 * @param string $periodo (YYYY-MM)
 * @param array $datos_adicionales
 * @return array|false
 */
function calcular_nomina(array $empleado, string $periodo, array $datos_adicionales = []) {
    if (!isset($empleado['salario'])) return false;

    $salario_base = (float)$empleado['salario'];
    $horas_extras = (float)($datos_adicionales['horas_extras'] ?? 0.0);
    // Convención: 220 horas/mes
    $valor_hora_base = $salario_base / 220.0;
    $valor_hora_extra = $valor_hora_base * 1.5;

    $bonificaciones = (float)($datos_adicionales['bonificaciones'] ?? 0.0);
    $deducciones = (float)($datos_adicionales['deducciones'] ?? 0.0);

    $total_horas_extras = $horas_extras * $valor_hora_extra;
    $salario_bruto = $salario_base + $total_horas_extras + $bonificaciones;

    $seguro_social = $salario_bruto * 0.03; // 3%
    $impuesto_renta = calcular_impuesto_renta($salario_bruto);

    $total_deducciones = $seguro_social + $impuesto_renta + $deducciones;
    $salario_neto = $salario_bruto - $total_deducciones;

    return [
        'empleado_id' => $empleado['id'] ?? null,
        'periodo' => $periodo,
        'salario_base' => round($salario_base, 2),
        'horas_extras' => $horas_extras,
        'valor_hora_extra' => round($valor_hora_extra, 2),
        'total_horas_extras' => round($total_horas_extras, 2),
        'bonificaciones' => round($bonificaciones, 2),
        'salario_bruto' => round($salario_bruto, 2),
        'seguro_social' => round($seguro_social, 2),
        'impuesto_renta' => round($impuesto_renta, 2),
        'deducciones' => round($deducciones, 2),
        'total_deducciones' => round($total_deducciones, 2),
        'salario_neto' => round($salario_neto, 2)
    ];
}


/**
 * Genera un comprobante de pago minimalista a partir de empleado y datos de nómina.
 * @param array $empleado
 * @param array $nomina
 * @return array
 */
function generar_comprobante_pago(array $empleado, array $nomina): array {
    return [
        'comprobante_id' => 'COMP-' . str_pad((string)($nomina['empleado_id'] ?? '0'), 6, '0', STR_PAD_LEFT),
        'empleado' => $empleado,
        'nomina' => $nomina,
        'fecha_generacion' => date('Y-m-d H:i:s'),
        'empresa' => [
            'nombre' => 'Mi Empresa S.A.',
            'ruc' => '12345678901',
            'direccion' => 'Calle Principal 123'
        ]
    ];
}


/**
 * Agrega utilidades de agregación sobre un conjunto de registros de nómina
 * @param array $registros (cada uno con 'salario_neto' y opcional 'estado')
 * @return array
 */
function obtener_estadisticas_nomina(array $registros): array {
    $total_registros = count($registros);
    $pagados = count(array_filter($registros, function($r){ return ($r['estado'] ?? '') === 'Pagado'; }));
    $total_monto = array_sum(array_column($registros, 'salario_neto'));

    return [
        'total_registros' => $total_registros,
        'pagados' => $pagados,
        'pendientes' => $total_registros - $pagados,
        'total_monto' => round($total_monto, 2),
        'promedio_salario' => $total_registros > 0 ? round($total_monto / $total_registros, 2) : 0
    ];
}

?>
