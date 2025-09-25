<?php
/**
 * header.php
 * Este archivo contiene la estructura principal de la página,
 * incluyendo el DOCTYPE, <head>, y la estructura del layout (sidebar y header).
 *
 * No contiene las etiquetas de cierre </body> y </html>,
 * que se encuentran en el archivo footer.php.
 *
 */

// Incluimos los archivos de configuración y clases necesarias.
require_once 'config/session.php';
require_once 'config/database.php';

// Verificamos si el usuario está logueado. Si no, lo redirigimos a la página de login.
if (!SessionManager::verificarSesion()) {
    header('Location: login.php');
    exit();
}

// Obtenemos los datos del usuario de la sesión para mostrarlos en el header.
$usuario_actual = SessionManager::obtenerUsuario();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS - Sistema de Gestión de Recursos Humanos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/header_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <nav class="sidebar">
            <div class="sidebar-logo">
                <div class="logo">
                    <i class="fas fa-users-cog"></i> HRMS
                </div>
                <div class="logo-subtitle">Recursos Humanos</div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-section">
                    <div class="nav-section-title">Principal</div>
                    <ul>
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                                <i class="fas fa-chart-line nav-icon"></i>
                                Dashboard
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-section">
                    <div class="nav-section-title">Gestión</div>
                    <ul>
                        <?php if (SessionManager::tienePermiso('gerente')): ?>
                        <li class="nav-item">
                            <a href="empleados.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'empleados.php' ? 'active' : ''; ?>">
                                <i class="fas fa-users nav-icon"></i>
                                Empleados
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a href="nomina.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'nomina.php' ? 'active' : ''; ?>">
                                <i class="fas fa-money-bill-wave nav-icon"></i>
                                Nómina
                            </a>
                        </li>
                        
                        <?php if (SessionManager::tienePermiso('administrador')): ?>
                        <li class="nav-item">
                            <a href="reclutamiento.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reclutamiento.php' ? 'active' : ''; ?>">
                                <i class="fas fa-user-plus nav-icon"></i>
                                Reclutamiento
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a href="capacitacion.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'capacitacion.php' ? 'active' : ''; ?>">
                                <i class="fas fa-graduation-cap nav-icon"></i>
                                Capacitación
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-section">
                    <div class="nav-section-title">Servicios</div>
                    <ul>
                        <li class="nav-item">
                            <a href="permisos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'permisos.php' ? 'active' : ''; ?>">
                                <i class="fas fa-calendar-alt nav-icon"></i>
                                Permisos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="documentos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'documentos.php' ? 'active' : ''; ?>">
                                <i class="fas fa-file-alt nav-icon"></i>
                                Documentos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="muro.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'muro.php' ? 'active' : ''; ?>">
                                <i class="fas fa-comments nav-icon"></i>
                                Muro Empresarial
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (SessionManager::tienePermiso('gerente')): ?>
                <li class="nav-section">
                    <div class="nav-section-title">Análisis</div>
                    <ul>
                        <li class="nav-item">
                            <a href="reportes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'active' : ''; ?>">
                                <i class="fas fa-chart-bar nav-icon"></i>
                                Reportes
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                <li class="nav-section">
                    <div class="nav-section-title">Usuario</div>
                    <ul>
                        <li class="nav-item">
                            <a href="perfil.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>">
                                <i class="fas fa-user nav-icon"></i>
                                Mi Perfil
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        
        <main class="content">
            <header class="header">
                <div class="header-title">
                    <i class="fas fa-<?php
                        $iconos = [
                            'dashboard.php' => 'chart-line',
                            'empleados.php' => 'users',
                            'nomina.php' => 'money-bill-wave',
                            'reclutamiento.php' => 'user-plus',
                            'capacitacion.php' => 'graduation-cap',
                            'permisos.php' => 'calendar-alt',
                            'documentos.php' => 'file-alt',
                            'muro.php' => 'comments',
                            'reportes.php' => 'chart-bar',
                            'perfil.php' => 'user'
                        ];
                        echo $iconos[basename($_SERVER['PHP_SELF'])] ?? 'home';
                    ?>"></i>
                    <?php
                    $titulos = [
                        'dashboard.php' => 'Dashboard',
                        'empleados.php' => 'Gestión de Empleados',
                        'nomina.php' => 'Gestión de Nómina',
                        'reclutamiento.php' => 'Reclutamiento',
                        'capacitacion.php' => 'Capacitación',
                        'permisos.php' => 'Permisos y Ausencias',
                        'documentos.php' => 'Gestión de Documentos',
                        'muro.php' => 'Muro Empresarial',
                        'reportes.php' => 'Reportes',
                        'perfil.php' => 'Mi Perfil'
                    ];
                    echo $titulos[basename($_SERVER['PHP_SELF'])] ?? 'HRMS';
                    ?>
                </div>
                
                <div class="header-actions">
                    <div class="notifications-panel">
                        <button class="notification-trigger">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        
                        <div class="notifications-dropdown">
                            <div class="notification-item unread">
                                <div class="notification-content">
                                    <div class="notification-icon" style="background: var(--accent-blue);"></div>
                                    <div>
                                        <div class="notification-title">Nuevo documento para firmar</div>
                                        <div class="notification-meta">Política de Seguridad - Hace 2 horas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-item unread">
                                <div class="notification-content">
                                    <div class="notification-icon" style="background: var(--accent-green);"></div>
                                    <div>
                                        <div class="notification-title">Permiso aprobado</div>
                                        <div class="notification-meta">Vacaciones del 20-25 Feb - Hace 4 horas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-item">
                                <div class="notification-content">
                                    <div class="notification-icon" style="background: var(--accent-orange);"></div>
                                    <div>
                                        <div class="notification-title">Capacitación programada</div>
                                        <div class="notification-meta">Seguridad Laboral - Mañana 9:00 AM</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="user-menu">
                        <span class="user-name"><?php echo htmlspecialchars($usuario_actual['nombre']); ?></span>
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($usuario_actual['nombre'], 0, 1)); ?>
                        </div>
                        <span class="user-role">
                            <?php echo ucfirst($usuario_actual['rol']); ?>
                        </span>
                    </div>
                    
                    <a href="logout.php" class="btn btn-secondary" title="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </header>

            <div class="page-container">