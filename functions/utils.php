<?php
/**
 * Funciones Utilitarias Generales
 * Sistema de Gestión de Recursos Humanos
 */

/**
 * Función para formatear fechas en español
 * @param string $fecha
 * @param string $formato
 * @return string
 */
function formatear_fecha($fecha, $formato = 'd/m/Y') {
    if (empty($fecha)) {
        return '';
    }
    
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
        5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
        9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
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
            return tiempo_transcurrido($fecha);
            
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
    return $moneda . ' ' . number_format($cantidad, 2, '.', ',');
}

/**
 * Función para limpiar y validar entrada de datos
 * @param string $data
 * @return string
 */
function limpiar_entrada($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Función para validar email
 * @param string $email
 * @return bool
 */
function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Función para validar teléfono
 * @param string $telefono
 * @return bool
 */
function validar_telefono($telefono) {
    // Patrón básico para teléfonos (permite varios formatos)
    $patron = '/^[\+]?[0-9\s\-$$$$]{7,15}$/';
    return preg_match($patron, $telefono);
}

/**
 * Función para generar contraseña aleatoria
 * @param int $longitud
 * @return string
 */
function generar_password($longitud = 8) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    
    for ($i = 0; $i < $longitud; $i++) {
        $password .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    return $password;
}

/**
 * Función para crear slug amigable para URLs
 * @param string $texto
 * @return string
 */
function crear_slug($texto) {
    // Convertir a minúsculas
    $texto = strtolower($texto);
    
    // Reemplazar caracteres especiales
    $texto = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
        ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
        $texto
    );
    
    // Reemplazar espacios y caracteres no alfanuméricos con guiones
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    
    // Eliminar guiones al inicio y final
    $texto = trim($texto, '-');
    
    return $texto;
}

/**
 * Función para truncar texto
 * @param string $texto
 * @param int $limite
 * @param string $sufijo
 * @return string
 */
function truncar_texto($texto, $limite = 100, $sufijo = '...') {
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    
    return substr($texto, 0, $limite) . $sufijo;
}

/**
 * Función para obtener iniciales de un nombre
 * @param string $nombre_completo
 * @return string
 */
function obtener_iniciales($nombre_completo) {
    $palabras = explode(' ', trim($nombre_completo));
    $iniciales = '';
    
    foreach ($palabras as $palabra) {
        if (!empty($palabra)) {
            $iniciales .= strtoupper(substr($palabra, 0, 1));
        }
    }
    
    return substr($iniciales, 0, 2); // Máximo 2 iniciales
}

/**
 * Función para calcular edad
 * @param string $fecha_nacimiento
 * @return int
 */
function calcular_edad($fecha_nacimiento) {
    if (empty($fecha_nacimiento)) {
        return 0;
    }
    
    $fecha_nac = new DateTime($fecha_nacimiento);
    $fecha_actual = new DateTime();
    $edad = $fecha_actual->diff($fecha_nac);
    
    return $edad->y;
}

/**
 * Función para calcular años de servicio
 * @param string $fecha_ingreso
 * @return array
 */
function calcular_años_servicio($fecha_ingreso) {
    if (empty($fecha_ingreso)) {
        return ['años' => 0, 'meses' => 0, 'dias' => 0];
    }
    
    $fecha_ing = new DateTime($fecha_ingreso);
    $fecha_actual = new DateTime();
    $diferencia = $fecha_actual->diff($fecha_ing);
    
    return [
        'años' => $diferencia->y,
        'meses' => $diferencia->m,
        'dias' => $diferencia->d,
        'total_dias' => $fecha_actual->diff($fecha_ing)->days
    ];
}

/**
 * Función para formatear tamaño de archivo
 * @param int $bytes
 * @return string
 */
function formatear_tamaño_archivo($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($unidades) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $unidades[$i];
}

/**
 * Función para generar código único
 * @param string $prefijo
 * @param int $longitud
 * @return string
 */
function generar_codigo_unico($prefijo = '', $longitud = 6) {
    $codigo = $prefijo;
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    return $codigo;
}

/**
 * Función para validar rango de fechas
 * @param string $fecha_inicio
 * @param string $fecha_fin
 * @return bool
 */
function validar_rango_fechas($fecha_inicio, $fecha_fin) {
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        return false;
    }
    
    return strtotime($fecha_inicio) <= strtotime($fecha_fin);
}

/**
 * Función para obtener días hábiles entre dos fechas
 * @param string $fecha_inicio
 * @param string $fecha_fin
 * @return int
 */
function calcular_dias_habiles($fecha_inicio, $fecha_fin) {
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $dias_habiles = 0;
    
    while ($inicio <= $fin) {
        $dia_semana = $inicio->format('N'); // 1 = lunes, 7 = domingo
        if ($dia_semana < 6) { // Lunes a viernes
            $dias_habiles++;
        }
        $inicio->add(new DateInterval('P1D'));
    }
    
    return $dias_habiles;
}

/**
 * Función para registrar log de actividades
 * @param string $accion
 * @param string $detalle
 * @param int $usuario_id
 */
function registrar_log($accion, $detalle = '', $usuario_id = null) {
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $log_entry = "[$fecha] Usuario: $usuario_id | IP: $ip | Acción: $accion | Detalle: $detalle | User-Agent: $user_agent" . PHP_EOL;
    
    // En producción, guardar en archivo o base de datos
    error_log($log_entry, 3, 'logs/actividad.log');
}

/**
 * Función para enviar notificación por email (mock)
 * @param string $destinatario
 * @param string $asunto
 * @param string $mensaje
 * @return bool
 */
function enviar_email($destinatario, $asunto, $mensaje) {
    // En producción, implementar envío real de email
    // Por ahora solo registrar en log
    registrar_log('Email enviado', "Para: $destinatario | Asunto: $asunto");
    return true;
}

/**
 * Función para obtener configuración del sistema
 * @param string $clave
 * @param mixed $valor_defecto
 * @return mixed
 */
function obtener_configuracion($clave, $valor_defecto = null) {
    // Configuraciones mock del sistema
    $configuraciones = [
        'nombre_empresa' => 'Mi Empresa S.A.',
        'timezone' => 'America/Mexico_City',
        'moneda' => '$',
        'idioma' => 'es',
        'formato_fecha' => 'd/m/Y',
        'dias_vacaciones_anuales' => 15,
        'horas_trabajo_diarias' => 8,
        'dias_trabajo_semanales' => 5,
        'porcentaje_seguro_social' => 3.0,
        'email_notificaciones' => true,
        'backup_automatico' => true,
        'session_timeout' => 1800, // 30 minutos
        'max_intentos_login' => 3,
        'longitud_minima_password' => 8
    ];
    
    return $configuraciones[$clave] ?? $valor_defecto;
}

/**
 * Función para validar permisos de archivo
 * @param string $ruta_archivo
 * @return bool
 */
function validar_permisos_archivo($ruta_archivo) {
    $extensiones_permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
    $tamaño_maximo = 10 * 1024 * 1024; // 10 MB
    
    if (!file_exists($ruta_archivo)) {
        return false;
    }
    
    $extension = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));
    $tamaño = filesize($ruta_archivo);
    
    return in_array($extension, $extensiones_permitidas) && $tamaño <= $tamaño_maximo;
}

/**
 * Función para generar breadcrumbs
 * @param array $rutas
 * @return string
 */
function generar_breadcrumbs($rutas) {
    $breadcrumbs = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    $total = count($rutas);
    $contador = 1;
    
    foreach ($rutas as $nombre => $enlace) {
        if ($contador === $total) {
            // Último elemento (activo)
            $breadcrumbs .= '<li class="breadcrumb-item active" aria-current="page">' . $nombre . '</li>';
        } else {
            // Elementos con enlace
            $breadcrumbs .= '<li class="breadcrumb-item"><a href="' . $enlace . '">' . $nombre . '</a></li>';
        }
        $contador++;
    }
    
    $breadcrumbs .= '</ol></nav>';
    return $breadcrumbs;
}

/**
 * Función para obtener color por estado
 * @param string $estado
 * @return string
 */
function obtener_color_estado($estado) {
    $colores = [
        'Activo' => 'success',
        'Inactivo' => 'secondary',
        'Pendiente' => 'warning',
        'Aprobado' => 'success',
        'Rechazado' => 'danger',
        'En Proceso' => 'info',
        'Completado' => 'success',
        'Cancelado' => 'danger',
        'Pagado' => 'success',
        'Por Pagar' => 'warning',
        'Vencido' => 'danger'
    ];
    
    return $colores[$estado] ?? 'secondary';
}

/**
 * Función para escapar datos para JSON
 * @param mixed $data
 * @return string
 */
function escapar_json($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
}
?>
