<?php
/**
 * Página de Inicio - Redirección al Login o Dashboard
 * Sistema HRMS
 */

require_once 'config/session.php';

// Si el usuario ya está logueado, redirigir al dashboard
if (SessionManager::verificarSesion()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
?>
