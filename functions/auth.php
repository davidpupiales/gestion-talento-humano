<?php
/**
 * Funciones de Autenticación y Seguridad
 * Sistema de Gestión de Recursos Humanos
 */

// Datos de usuarios mock para desarrollo
$usuarios_mock = [
    'admin' => [
        'id' => 1,
        'usuario' => 'admin',
        'password' => 'admin123', // En producción usar password_hash()
        'nombre' => 'Administrador',
        'apellido' => 'Sistema',
        'email' => 'admin@empresa.com',
        'rol' => 'Administrador',
        'departamento' => 'Sistemas',
        'avatar' => 'A',
        'activo' => true,
        'ultimo_acceso' => date('Y-m-d H:i:s')
    ],
    'gerente1' => [
        'id' => 2,
        'usuario' => 'gerente1',
        'password' => 'gerente123',
        'nombre' => 'María',
        'apellido' => 'González',
        'email' => 'maria.gonzalez@empresa.com',
        'rol' => 'Gerente',
        'departamento' => 'Recursos Humanos',
        'avatar' => 'MG',
        'activo' => true,
        'ultimo_acceso' => date('Y-m-d H:i:s', strtotime('-2 hours'))
    ],
    'empleado1' => [
        'id' => 3,
        'usuario' => 'empleado1',
        'password' => 'empleado123',
        'nombre' => 'Carlos',
        'apellido' => 'Rodríguez',
        'email' => 'carlos.rodriguez@empresa.com',
        'rol' => 'Empleado',
        'departamento' => 'Ventas',
        'avatar' => 'CR',
        'activo' => true,
        'ultimo_acceso' => date('Y-m-d H:i:s', strtotime('-1 hour'))
    ]
];

/**
 * Función para autenticar usuario
 * @param string $usuario - Nombre de usuario
 * @param string $password - Contraseña
 * @return array|false - Datos del usuario o false si falla
 */
function autenticar_usuario($usuario, $password) {
    global $usuarios_mock;
    
    // Validar que los campos no estén vacíos
    if (empty($usuario) || empty($password)) {
        return false;
    }
    
    // Buscar usuario en datos mock
    if (isset($usuarios_mock[$usuario])) {
        $datos_usuario = $usuarios_mock[$usuario];
        
        // Verificar contraseña (en producción usar password_verify())
        if ($datos_usuario['password'] === $password && $datos_usuario['activo']) {
            // Actualizar último acceso
            $datos_usuario['ultimo_acceso'] = date('Y-m-d H:i:s');
            return $datos_usuario;
        }
    }
    
    return false;
}

/**
 * Función para verificar si el usuario está logueado
 * @return bool
 */
function usuario_logueado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Función para obtener datos del usuario actual
 * @return array|null
 */
function obtener_usuario_actual() {
    if (!usuario_logueado()) {
        return null;
    }
    
    global $usuarios_mock;
    $usuario_id = $_SESSION['usuario_id'];
    
    // Buscar usuario por ID
    foreach ($usuarios_mock as $usuario_data) {
        if ($usuario_data['id'] == $usuario_id) {
            return $usuario_data;
        }
    }
    
    return null;
}

/**
 * Función para verificar permisos por rol
 * @param string $rol_requerido - Rol mínimo requerido
 * @return bool
 */
function verificar_permisos($rol_requerido) {
    $usuario = obtener_usuario_actual();
    if (!$usuario) {
        return false;
    }
    
    $jerarquia_roles = [
        'Empleado' => 1,
        'Gerente' => 2,
        'Administrador' => 3
    ];
    
    $nivel_usuario = $jerarquia_roles[$usuario['rol']] ?? 0;
    $nivel_requerido = $jerarquia_roles[$rol_requerido] ?? 0;
    
    return $nivel_usuario >= $nivel_requerido;
}

/**
 * Función para cerrar sesión
 */
function cerrar_sesion() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

/**
 * Función para generar token CSRF
 * @return string
 */
function generar_token_csrf() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Función para verificar token CSRF
 * @param string $token
 * @return bool
 */
function verificar_token_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Función para registrar actividad del usuario
 * @param string $accion - Descripción de la acción
 */
function registrar_actividad($accion) {
    $usuario = obtener_usuario_actual();
    if ($usuario) {
        // En producción, guardar en base de datos
        error_log("Usuario {$usuario['nombre']} {$usuario['apellido']} realizó: $accion");
    }
}

/**
 * Función para validar sesión activa
 */
function validar_sesion() {
    if (!usuario_logueado()) {
        header('Location: login.php');
        exit();
    }
    
    // Verificar timeout de sesión (30 minutos)
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempo_inactivo = time() - $_SESSION['ultimo_acceso'];
        if ($tiempo_inactivo > 1800) { // 30 minutos
            cerrar_sesion();
        }
    }
    
    $_SESSION['ultimo_acceso'] = time();
}

/**
 * Función para obtener avatar del usuario
 * @param array $usuario - Datos del usuario
 * @return string - Iniciales para avatar
 */
function obtener_avatar($usuario) {
    if (isset($usuario['avatar'])) {
        return $usuario['avatar'];
    }
    
    $iniciales = '';
    if (isset($usuario['nombre'])) {
        $iniciales .= strtoupper(substr($usuario['nombre'], 0, 1));
    }
    if (isset($usuario['apellido'])) {
        $iniciales .= strtoupper(substr($usuario['apellido'], 0, 1));
    }
    
    return $iniciales ?: 'U';
}
?>
