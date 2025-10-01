<?php
/**
 * Configuración de Sesiones
 * Sistema HRMS
 */

// Configuración de seguridad de sesión (DEBE ir antes de session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS
ini_set('session.gc_maxlifetime', 1800); // 30 minutos

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Tiempo de expiración de sesión (30 minutos)
define('SESSION_TIMEOUT', 1800);

class SessionManager {
    
    public static function iniciarSesion($usuario) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['username'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['ultimo_acceso'] = time();
        
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
    }
    
    public static function verificarSesion() {
        if (!isset($_SESSION['usuario_id'])) {
            return false;
        }
        
        // Verificar timeout de sesión
        if (isset($_SESSION['ultimo_acceso']) && 
            (time() - $_SESSION['ultimo_acceso']) > SESSION_TIMEOUT) {
            self::cerrarSesion();
            return false;
        }
        
        // Actualizar último acceso
        $_SESSION['ultimo_acceso'] = time();
        return true;
    }
    
    public static function cerrarSesion() {
        session_unset();
        session_destroy();
        header('Location: /login.php');
        exit();
    }
    
    public static function obtenerUsuario() {
        if (self::verificarSesion()) {
            return [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email'],
                'rol' => $_SESSION['usuario_rol']
            ];
        }
        return null;
    }
    
    public static function tienePermiso($rol_requerido) {
        $usuario = self::obtenerUsuario();
        if (!$usuario) return false;
        
        $jerarquia_roles = [
            'empleado' => 1,
            'gerente' => 2,
            'administrador' => 3
        ];
        
        $rol_usuario = $jerarquia_roles[$usuario['rol']] ?? 0;
        $rol_necesario = $jerarquia_roles[$rol_requerido] ?? 0;
        
        return $rol_usuario >= $rol_necesario;
    }
}
?>
