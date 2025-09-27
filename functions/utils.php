<?php
/**
 * Funciones Utilitarias Generales y de Lógica de Negocio (RH)
 * Sistema de Gestión de Recursos Humanos
 */

// ===============================================
// CONSTANTES DE CONFIGURACIÓN DE NÓMINA
// ===============================================

/** Salario Mínimo Legal Vigente (Ejemplo: 2024 - 1.423.500 COP) */
const SMLV = 1423500; 
/** Porcentaje de la Mesada destinado a Cesantías (8.33% anual / 12) */
const PORCENTAJE_CESANTIAS = 0.0833;
/** Porcentaje de la Mesada destinado a Intereses de Cesantías (1% anual / 12) */
const PORCENTAJE_INTERES_CESANTIAS = 0.01;
/** Porcentaje de la Mesada destinado a Prima de Servicios (8.33% anual / 12) */
const PORCENTAJE_PRIMA = 0.0833;


// ===============================================
// 1. FUNCIONES DE FORMATO Y VALIDACIÓN
// ===============================================

/**
 * Función para formatear fechas en español
 * @param string $fecha
 * @param string $formato
 * @return string
 */
function formatear_fecha($fecha, $formato = 'd/m/Y') {
    if (empty($fecha) || $fecha === '0000-00-00') {
        return '';
    }
    
    // ... [Contenido de formatear_fecha sin cambios] ...
    
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'noviembre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    
    $dias_semana = [
        0 => 'domingo', 1 => 'lunes', 2 => 'martes', 3 => 'miércoles',
        4 => 'jueves', 5 => 'viernes', 6 => 'sábado'
    ];
    
    $timestamp = strtotime($fecha);
    
    switch ($formato) {
        case 'completa':
            $dia_semana = $dias_semana[date('w', $timestamp)];
            $dia = date('j', $timestamp);
            $mes = $meses[date('n', $timestamp)];
            $año = date('Y', $timestamp);
            return ucfirst($dia_semana) . ', ' . $dia . ' de ' . $mes . ' de ' . $año;
            
        case 'mes_año':
            $mes = $meses[date('n', $timestamp)];
            $año = date('Y', $timestamp);
            return ucfirst($mes) . ' ' . $año;
            
        case 'relativa':
            // Asume que tienes una función llamada tiempo_transcurrido()
            return 'Función relativa no implementada'; 
            
        default:
            return date($formato, $timestamp);
    }
}

/**
 * Función para formatear números como moneda
 * @param float $cantidad
 * @param string $moneda
 * @return string
 */
function formatear_moneda($cantidad, $moneda = '$') {
    // Usar locale/intl para mejor soporte de moneda si es posible
    return $moneda . ' ' . number_format((float)$cantidad, 2, '.', ',');
}

/**
 * Función para limpiar y validar entrada de datos (OPTIMIZADA)
 * @param mixed $data
 * @param string $tipo (string, int, float)
 * @return mixed
 */
function limpiar_entrada($data, $tipo = 'string') {
    if (is_array($data)) {
        return array_map('limpiar_entrada', $data);
    }

    $data = trim($data);
    $data = stripslashes($data);
    
    // Desinfección HTML para prevenir XSS
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); 

    // Validación de tipo de dato
    switch ($tipo) {
        case 'int':
            return (int)filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            // Permite comas como separador decimal si se usa number_format
            $data = str_replace(',', '.', $data); 
            return (float)filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        default:
            return $data;
    }
}

// ... [Otras funciones sin cambios: validar_email, validar_telefono, generar_password, crear_slug, truncar_texto] ...
// ... [obtener_iniciales, calcular_edad, calcular_años_servicio, formatear_tamaño_archivo, generar_codigo_unico] ...
// ... [validar_rango_fechas, calcular_dias_habiles, registrar_log, enviar_email, obtener_configuracion, validar_permisos_archivo, generar_breadcrumbs, obtener_color_estado, escapar_json] ...


// ===============================================
// 2. LÓGICA DE NÓMINA (FÓRMULAS DE EXCEL A PHP)
// ===============================================

/**
 * Genera el CÓDIGO de empleado siguiendo la lógica: SEDE-ID ANUAL.
 * Nota: 'ID' debe ser el último ID insertado o el ID de la fila actual.
 * @param string $prefijo (SEDE, que corresponde a A3 en tu fórmula)
 * @param int $id_registro
 * @return string
 */
function generar_codigo_empleado($prefijo, $id_registro) {
    if (empty($prefijo) || $id_registro < 1) {
        return '';
    }
    // Lógica: A3 & "-" & FILA()-2 & " " & AÑO(HOY())
    $anio_actual = date('Y');
    return limpiar_entrada($prefijo) . '-' . $id_registro . ' ' . $anio_actual;
}


/**
 * Calcula los DÍAS TRABAJADOS entre FECHA_INICIO y FECHA_FIN.
 * Lógica de Excel: =SI(Y(Z3 <> "";AA3 <>"");(-Z3+AA3)+1;"0")
 * @param string $fecha_inicio
 * @param string $fecha_fin
 * @return int
 */
function calcular_dias_trabajados($fecha_inicio, $fecha_fin) {
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        return 0;
    }
    
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    
    if ($inicio > $fin) {
        return 0;
    }
    
    $diferencia = $inicio->diff($fin);
    // +1 para incluir el día final (diferencia entre fechas + 1)
    return $diferencia->days + 1;
}

/**
 * Calcula el AUXILIO DE TRANSPORTE.
 * Lógica de Excel: =SI(Y(A3="LAB";AJ3<(2*Listas!$N$2);AJ3<>"");200000;0)
 * (A3=CONTRATO, AJ3=MESADA, N2=SMLV)
 * @param string $tipo_contrato
 * @param float $mesada
 * @return float
 */
function calcular_aux_transporte($tipo_contrato, $mesada) {
    // Aplicar lógica: Contrato LAB AND Mesada < (2 * SMLV) AND Mesada no nula/cero
    if (limpiar_entrada($tipo_contrato) === 'LAB' && (float)$mesada < (2 * SMLV) && (float)$mesada > 0) {
        return 200000.00; // Valor del auxilio de transporte (debe ser parametrizable)
    }
    return 0.00;
}

/**
 * Calcula el PRESUPUESTO ANUAL (PRES ANUAL).
 * Lógica de Excel: =SI(A3="";"";AK3*(AH3/30)) (AK3=MESADA, AH3=DIAS_TRABAJADOS)
 * @param float $mesada
 * @param int $dias_trabajados
 * @return float
 */
function calcular_pres_anual($mesada, $dias_trabajados) {
    if ((float)$mesada > 0 && (int)$dias_trabajados > 0) {
        return (float)$mesada * ((int)$dias_trabajados / 30);
    }
    return 0.00;
}

/**
 * Calcula el PRESUPUESTO MENSUAL (PRES MENSUAL).
 * Lógica de Excel: =SUMA(AJ3;AN3;...;(AX3/12);(AY3/12);(AZ3/12);(BA3/12))
 * (Suma de Mesada, Auxilios Fijos, Aportes Mensuales + Provisión Anual / 12)
 *
 * NOTA: Esta función requiere todos los campos de aportes y pagos. 
 * Se recibe un array $datos para hacerlo más genérico.
 * @param array $datos
 * @return float
 */
function calcular_pres_mensual($datos) {
    // Mapeo simplificado basado en la estructura lógica:
    $mesada = (float)($datos['mesada'] ?? 0);
    $extras_legales = (float)($datos['extras_legales'] ?? 0);
    $aux_transporte = calcular_aux_transporte($datos['tipo_contrato'] ?? '', $mesada);
    
    // Aportes Mensuales (Suma de AN3 a AW3 en la fórmula)
    $aportes_mensuales = (float)($datos['ap_salud_mes'] ?? 0)
                       + (float)($datos['ap_pension_mes'] ?? 0)
                       + (float)($datos['ap_arl_mes'] ?? 0)
                       + (float)($datos['ap_caja_mes'] ?? 0)
                       + (float)($datos['ap_sena_mes'] ?? 0)
                       + (float)($datos['ap_icbf_mes'] ?? 0);
                       
    // Provisión Anual / 12 (Suma de AX3 a BA3 / 12)
    $provision_anual = (float)($datos['ap_cesantia_anual'] ?? 0)
                     + (float)($datos['ap_interes_cesantias_anual'] ?? 0)
                     + (float)($datos['ap_prima_anual'] ?? 0);
                     
    $pro_anual_mensual = $provision_anual / 12;
    
    return $mesada + $extras_legales + $aux_transporte + $aportes_mensuales + $pro_anual_mensual;
}


/**
 * Obtiene la TASA ARL (en decimal) basada en el NIVEL DE RIESGO.
 * Lógica de Excel: =SI(A3=""; ""; BUSCARV(NIVEL_RIESGO, TABLA_TARIFAS, ...))
 * @param string $nivel_riesgo (I, II, III, IV, V)
 * @return float
 */
function obtener_tasa_arl($nivel_riesgo) {
    // Tasa ARL (Ejemplo de tasas en Colombia, parametrizable en base de datos)
    $tasas = [
        'I' => 0.00522,   // 0.522%
        'II' => 0.01044,  // 1.044%
        'III' => 0.02436, // 2.436%
        'IV' => 0.04350,  // 4.350%
        'V' => 0.06960,   // 6.960%
    ];
    
    $nivel = strtoupper(trim(limpiar_entrada($nivel_riesgo)));
    
    return $tasas[$nivel] ?? 0.00000;
}


// ===============================================
// 3. FUNCIONES PARA LISTAS DESPLEGABLES (OPTIONS)
// ===============================================

/** Obtiene las opciones para el campo CARGO */
function getCargoOptions() {
    return [
        'FISIOTERAPEUTA', 'TERAPEUTA OCUPACIONAL', 'FONOAUDIOLOGO/A', 'AUXILIAR DE ENFERMERIA', 'PSICOLOGO/A',
        'TERAPEUTA RESPIRATORIO', 'CUIDADOR/A', 'TRABAJADOR/A SOCIAL', 'GERENTE GENERAL', 'AUXILIAR DE SERVICIOS GENERALES',
        'AUXILIAR DE ENFERMERÍA Y TOMA DE MUESTRAS', 'JEFE DE ENFERMERIA', 'TECNICO ADMINISTRATIVO', 'AUXILIAR CONTABLE',
        'DIRECTOR TECNICO DE SERVICIO FARMACEUTICO', 'TECNICO EN SERVICIO FARMACEUTICO', 'AUXILIAR DE TALENTO HUMANO',
        'AUXILIAR DE ODONTOLOGIA', 'MÉDICO EXPERTO VIH', 'REVISORA FISCAL', 'PSIQUIATRA', 'FISIATRA', 'REUMATOLOGO/A',
        'QUIMICO FARMACEUTICA - ASESORA EXTERNA', 'INFECTOLOGIA - MEDICO INTERNISTA', 'INGENIERO/A BIOMÉDICO/A',
        'INGENIERO/A AMBIENTAL', 'SIBDIRECTOR ASISTENCIAL', 'MEDICO GENERAL', 'CONTADORA', 'INFECTOLOGO/A PEDIATRA',
        'MEDICO DEL DOLOR Y CUIDADOS PALIATIVOS', 'APOYO TRABAJO SOCIAL', 'TECNICO ADMINISTRATIVO Y GESTOR DE RECURSOS FÍSICOS',
        'SUPERVISOR DE PROGRAMA', 'SIBDIRECTOR ADMINISTRATIVO', 'COORDINADOR DE FACTURCIÓN, CUENTAS MEDICAS Y SISTEMAS',
        'PROFESIONAL ADMINISTRATIVO', 'OPTÓMETRA', 'COORDINADORA DE TALENTO HUMANO', 'NUTRICIONISTA', 'ODONTOLOGO/A',
        'AUXILIAR DE PROCEDIMIENTOS DE MEDICAMENTOS', 'BACTERIOLOGA', 'AUXILIAR DE LABORATORIO', 'SUPERVISOR DE LABORATORIO',
        'PROFESIONAL SST', 'QUIMICO FARMACEUTICA', 'COORDINADORA', 'COORDINADOR', 'INGENIERO DE SISTEMAS',
        'INGENIERO/A INDUSTRIAL', 'MEDICO INTERNISTA', 'MEDICO OCUPACIONAL', 'BACTERIOLOGO', 'APRENDIZ SENA',
        'PRACTICANTE UNIVERSITARIO'
    ];
}

/** Obtiene las opciones para el campo MUNICIPIO */
function getMunicipioOptions() {
    return [
        'PASTO', 'IPIALES', 'ALBÁN', 'ALDANA', 'ANCUYA', 'ARBOLEDA', 'BARBACOAS', 'BELÉN', 'BUESACO', 'CHACHAGÜÍ',
        'COLÓN', 'CONSACÁ', 'CONTADERO', 'CÓRDOBA', 'CUASPUD', 'CUMBAL', 'CUMBITARA', 'EL CHARCO', 'EL PEÑOL',
        'EL ROSARIO', 'EL TABLÓN DE GÓMEZ', 'EL TAMBO', 'FRANCISCO PIZARRO', 'FUNES', 'GUACHUCAL', 'GUAITARILLA',
        'GUALMATÁN', 'ILES', 'IMUÉS', 'LA CRUZ', 'LA FLORIDA', 'LA LLANADA', 'LA TOLA', 'LA UNIÓN', 'LEIVA',
        'LINARES', 'LOS ANDES', 'MAGÜÍ PAYÁN', 'MALLAMA', 'MOSQUERA', 'NARIÑO', 'OLAYA HERRERA', 'OSPINA',
        'POLICARPA', 'POTOSÍ', 'PROVIDENCIA', 'PUERRES', 'PUPIALES', 'RICAURTE', 'ROBERTO PAYÁN', 'SAMANIEGO',
        'SANDONÁ', 'SAN BERNARDO', 'SAN LORENZO', 'SAN PABLO', 'SAN PEDRO DE CARTAGO', 'SANTA BÁRBARA',
        'SANTACRUZ', 'SAPUYES', 'TAMINANGO', 'TANGUA', 'TUMACO', 'TUQUERRES', 'YACUANQUER', 'ALTO PUTUMAYO'
    ];
}

/** Obtiene las opciones para el campo CONTRATO */
function getContratoOptions() {
    return ['OPS', 'LAB'];
}

/** Obtiene las opciones para el campo DEPARTAMENTO */
function getDepartamentoOptions() {
    return ['NARIÑO', 'CAUCA', 'VALLE'];
}

/** Obtiene las opciones para el campo TIPO DE SERVICIO */
function getServicioOptions() {
    return ['ATENCIÓN A PACIENTE', 'HORA DE SERVICIO', 'NO APLICA'];
}

/** Obtiene las opciones para el campo NIVEL */
function getNivelOptions() {
    return ['OPERATIVO ESPECIALIZADO', 'SOPORTE', 'COORDINADOR/MANDO MEDIO', 'OPERATIVO TÉCNICO/ADMINISTRATIVO', 'DIRECTIVO'];
}

/** Obtiene las opciones para el campo CALIDAD */
function getCalidadOptions() {
    return ['ASISTENCIAL (EN SALUD)', 'ADMINISTRATIVO (EN SALUD)'];
}

/** Obtiene las opciones para el campo ÁREA */
function getAreaOptions() {
    return [
        'PROGRAMA ARTRITIS', 'PROGRAMA B24X', 'HOME CARE', 'OPTOMETRÍA', 'PALIATIVOS', 'PROGRAMA EPOC',
        'PROGRAMA NEFROPROTECCIÓN', 'SERVICIO FARMACÉUTICO ARTRITIS', 'SERVICIO FARMACÉUTICO B24X', 'ATENCIÓN AL USUARIO',
        'CALIDAD', 'CONTABILIDAD', 'FACTURACIÓN', 'INFRAESTRUCTURA', 'TALENTO HUMANO', 'TECNOLOGÍA Y LOGÍSTICA',
        'GOBIERNO', 'SUBDIRECCIONES', 'AUXILIAR CAC', 'SERVICIOS GENERALES', 'LABORATORIO', 'ASISTENCIAL',
        'ADMINISTRATIVO', 'POST-CONSULTA'
    ];
}

/** Obtiene las opciones para el campo NIVEL DE RIESGO */
function getNivelRiesgoOptions() {
    return ['I', 'II', 'III', 'IV', 'V'];
}

/** Obtiene las opciones para el campo PROGRAMA */
function getProgramaOptions() {
    return [
        'PROGRAMA ARTRITIS', 'PROGRAMA B24X', 'HOME CARE', 'OPTOMETRÍA', 'PALIATIVOS', 'PROGRAMA EPOC',
        'PROGRAMA NEFROPROTECCIÓN', 'LABORATORIO', 'ADMINISTRATIVO'
    ];
}
// ... [Otras funciones de lista pueden agregarse aquí si son requeridas en otros módulos] ...