<?php
/**
 * Funciones para Gestión de Empleados
 * Sistema de Gestión de Recursos Humanos
 */

// Datos mock de empleados para desarrollo
$empleados_mock = [
    1 => [
        'id' => 1,
        'codigo_empleado' => 'EMP001',
        'nombre' => 'Juan Carlos',
        'apellido' => 'Pérez López',
        'email' => 'juan.perez@empresa.com',
        'telefono' => '+1234567890',
        'departamento' => 'Desarrollo',
        'cargo' => 'Desarrollador Senior',
        'fecha_ingreso' => '2022-01-15',
        'salario' => 75000.00,
        'estado' => 'Activo',
        'jefe_directo' => 2,
        'direccion' => 'Calle Principal 123, Ciudad',
        'fecha_nacimiento' => '1990-05-20',
        'cedula' => '12345678',
        'estado_civil' => 'Soltero',
        'nivel_educacion' => 'Universitario',
        'avatar' => 'JP'
    ],
    2 => [
        'id' => 2,
        'codigo_empleado' => 'EMP002',
        'nombre' => 'María Elena',
        'apellido' => 'González Ruiz',
        'email' => 'maria.gonzalez@empresa.com',
        'telefono' => '+1234567891',
        'departamento' => 'Recursos Humanos',
        'cargo' => 'Gerente de RRHH',
        'fecha_ingreso' => '2020-03-10',
        'salario' => 95000.00,
        'estado' => 'Activo',
        'jefe_directo' => null,
        'direccion' => 'Avenida Central 456, Ciudad',
        'fecha_nacimiento' => '1985-08-15',
        'cedula' => '87654321',
        'estado_civil' => 'Casada',
        'nivel_educacion' => 'Postgrado',
        'avatar' => 'MG'
    ],
    3 => [
        'id' => 3,
        'codigo_empleado' => 'EMP003',
        'nombre' => 'Carlos Alberto',
        'apellido' => 'Rodríguez Silva',
        'email' => 'carlos.rodriguez@empresa.com',
        'telefono' => '+1234567892',
        'departamento' => 'Ventas',
        'cargo' => 'Ejecutivo de Ventas',
        'fecha_ingreso' => '2023-06-01',
        'salario' => 55000.00,
        'estado' => 'Activo',
        'jefe_directo' => 4,
        'direccion' => 'Calle Secundaria 789, Ciudad',
        'fecha_nacimiento' => '1992-12-03',
        'cedula' => '11223344',
        'estado_civil' => 'Soltero',
        'nivel_educacion' => 'Universitario',
        'avatar' => 'CR'
    ],
    4 => [
        'id' => 4,
        'codigo_empleado' => 'EMP004',
        'nombre' => 'Ana Patricia',
        'apellido' => 'Martínez Torres',
        'email' => 'ana.martinez@empresa.com',
        'telefono' => '+1234567893',
        'departamento' => 'Ventas',
        'cargo' => 'Gerente de Ventas',
        'fecha_ingreso' => '2019-09-20',
        'salario' => 85000.00,
        'estado' => 'Activo',
        'jefe_directo' => 2,
        'direccion' => 'Plaza Mayor 321, Ciudad',
        'fecha_nacimiento' => '1988-04-10',
        'cedula' => '55667788',
        'estado_civil' => 'Casada',
        'nivel_educacion' => 'Universitario',
        'avatar' => 'AM'
    ]
];

/**
 * Función para obtener todos los empleados
 * @param array $filtros - Filtros opcionales
 * @return array
 */
function obtener_empleados($filtros = []) {
    global $empleados_mock;
    
    $empleados = $empleados_mock;
    
    // Aplicar filtros si existen
    if (!empty($filtros['departamento'])) {
        $empleados = array_filter($empleados, function($emp) use ($filtros) {
            return $emp['departamento'] === $filtros['departamento'];
        });
    }
    
    if (!empty($filtros['estado'])) {
        $empleados = array_filter($empleados, function($emp) use ($filtros) {
            return $emp['estado'] === $filtros['estado'];
        });
    }
    
    if (!empty($filtros['busqueda'])) {
        $busqueda = strtolower($filtros['busqueda']);
        $empleados = array_filter($empleados, function($emp) use ($busqueda) {
            return strpos(strtolower($emp['nombre'] . ' ' . $emp['apellido']), $busqueda) !== false ||
                   strpos(strtolower($emp['email']), $busqueda) !== false ||
                   strpos(strtolower($emp['codigo_empleado']), $busqueda) !== false;
        });
    }
    
    return array_values($empleados);
}

/**
 * Función para obtener un empleado por ID
 * @param int $id
 * @return array|null
 */
function obtener_empleado_por_id($id) {
    global $empleados_mock;
    return $empleados_mock[$id] ?? null;
}

/**
 * Función para crear nuevo empleado
 * @param array $datos
 * @return bool|int - ID del empleado creado o false
 */
function crear_empleado($datos) {
    global $empleados_mock;
    
    // Validar datos requeridos
    $campos_requeridos = ['nombre', 'apellido', 'email', 'departamento', 'cargo'];
    foreach ($campos_requeridos as $campo) {
        if (empty($datos[$campo])) {
            return false;
        }
    }
    
    // Generar nuevo ID
    $nuevo_id = max(array_keys($empleados_mock)) + 1;
    
    // Generar código de empleado
    $codigo_empleado = 'EMP' . str_pad($nuevo_id, 3, '0', STR_PAD_LEFT);
    
    // Crear empleado
    $empleados_mock[$nuevo_id] = [
        'id' => $nuevo_id,
        'codigo_empleado' => $codigo_empleado,
        'nombre' => $datos['nombre'],
        'apellido' => $datos['apellido'],
        'email' => $datos['email'],
        'telefono' => $datos['telefono'] ?? '',
        'departamento' => $datos['departamento'],
        'cargo' => $datos['cargo'],
        'fecha_ingreso' => $datos['fecha_ingreso'] ?? date('Y-m-d'),
        'salario' => floatval($datos['salario'] ?? 0),
        'estado' => 'Activo',
        'jefe_directo' => $datos['jefe_directo'] ?? null,
        'direccion' => $datos['direccion'] ?? '',
        'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? '',
        'cedula' => $datos['cedula'] ?? '',
        'estado_civil' => $datos['estado_civil'] ?? '',
        'nivel_educacion' => $datos['nivel_educacion'] ?? '',
        'avatar' => strtoupper(substr($datos['nombre'], 0, 1) . substr($datos['apellido'], 0, 1))
    ];
    
    return $nuevo_id;
}

/**
 * Función para actualizar empleado
 * @param int $id
 * @param array $datos
 * @return bool
 */
function actualizar_empleado($id, $datos) {
    global $empleados_mock;
    
    if (!isset($empleados_mock[$id])) {
        return false;
    }
    
    // Actualizar campos permitidos
    $campos_actualizables = [
        'nombre', 'apellido', 'email', 'telefono', 'departamento', 
        'cargo', 'salario', 'estado', 'jefe_directo', 'direccion',
        'fecha_nacimiento', 'cedula', 'estado_civil', 'nivel_educacion'
    ];
    
    foreach ($campos_actualizables as $campo) {
        if (isset($datos[$campo])) {
            $empleados_mock[$id][$campo] = $datos[$campo];
        }
    }
    
    // Actualizar avatar si cambió el nombre
    if (isset($datos['nombre']) || isset($datos['apellido'])) {
        $empleados_mock[$id]['avatar'] = strtoupper(
            substr($empleados_mock[$id]['nombre'], 0, 1) . 
            substr($empleados_mock[$id]['apellido'], 0, 1)
        );
    }
    
    return true;
}

/**
 * Función para eliminar empleado (cambiar estado a Inactivo)
 * @param int $id
 * @return bool
 */
function eliminar_empleado($id) {
    global $empleados_mock;
    
    if (!isset($empleados_mock[$id])) {
        return false;
    }
    
    $empleados_mock[$id]['estado'] = 'Inactivo';
    return true;
}

/**
 * Función para obtener estadísticas de empleados
 * @return array
 */
function obtener_estadisticas_empleados() {
    global $empleados_mock;
    
    $total = count($empleados_mock);
    $activos = count(array_filter($empleados_mock, function($emp) {
        return $emp['estado'] === 'Activo';
    }));
    
    // Contar por departamentos
    $por_departamento = [];
    foreach ($empleados_mock as $empleado) {
        $dept = $empleado['departamento'];
        $por_departamento[$dept] = ($por_departamento[$dept] ?? 0) + 1;
    }
    
    return [
        'total' => $total,
        'activos' => $activos,
        'inactivos' => $total - $activos,
        'por_departamento' => $por_departamento,
        'nuevos_mes' => count(array_filter($empleados_mock, function($emp) {
            return date('Y-m', strtotime($emp['fecha_ingreso'])) === date('Y-m');
        }))
    ];
}

/**
 * Función para obtener lista de departamentos
 * @return array
 */
function obtener_departamentos() {
    return [
        'Recursos Humanos',
        'Desarrollo',
        'Ventas',
        'Marketing',
        'Finanzas',
        'Operaciones',
        'Soporte Técnico',
        'Administración'
    ];
}

/**
 * Función para obtener lista de cargos por departamento
 * @param string $departamento
 * @return array
 */
function obtener_cargos_por_departamento($departamento) {
    $cargos = [
        'Recursos Humanos' => [
            'Gerente de RRHH',
            'Especialista en RRHH',
            'Reclutador',
            'Analista de Compensaciones'
        ],
        'Desarrollo' => [
            'Desarrollador Senior',
            'Desarrollador Junior',
            'Líder Técnico',
            'Arquitecto de Software',
            'DevOps Engineer'
        ],
        'Ventas' => [
            'Gerente de Ventas',
            'Ejecutivo de Ventas',
            'Representante de Ventas',
            'Coordinador de Ventas'
        ],
        'Marketing' => [
            'Gerente de Marketing',
            'Especialista en Marketing Digital',
            'Community Manager',
            'Diseñador Gráfico'
        ],
        'Finanzas' => [
            'Gerente Financiero',
            'Contador',
            'Analista Financiero',
            'Tesorero'
        ]
    ];
    
    return $cargos[$departamento] ?? [];
}

/**
 * Función para validar email único
 * @param string $email
 * @param int $excluir_id - ID a excluir de la validación
 * @return bool
 */
function validar_email_unico($email, $excluir_id = null) {
    global $empleados_mock;
    
    foreach ($empleados_mock as $empleado) {
        if ($empleado['email'] === $email && $empleado['id'] !== $excluir_id) {
            return false;
        }
    }
    
    return true;
}
?>
