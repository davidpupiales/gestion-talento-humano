<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Si ya está logueado, redirigir al dashboard
if (SessionManager::verificarSesion()) {
    header('Location: dashboard.php');
    exit();
}

$error_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        $error_mensaje = 'Por favor complete todos los campos';
    } else {
        // Obtener usuarios mock
        $usuarios_mock = Database::getMockUsers();
        
        foreach ($usuarios_mock as $user) {
            if ($user['usuario'] === $usuario && password_verify($password, $user['password'])) {
                if ($user['activo']) {
                    SessionManager::iniciarSesion($user);
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error_mensaje = 'Usuario inactivo. Contacte al administrador.';
                    break;
                }
            }
        }
        
        if (empty($error_mensaje)) {
            $error_mensaje = 'Usuario o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HRMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo" style="font-size: 2rem; margin-bottom: 1rem;">
                    <i class="fas fa-users"></i> HRMS
                </div>
                <h2 class="login-title">Iniciar Sesión</h2>
                <p class="login-subtitle">Sistema de Gestión de Recursos Humanos</p>
            </div>
            
            <?php if (!empty($error_mensaje)): ?>
                <div style="background: var(--danger); color: white; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; text-align: center;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_mensaje); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
                    Iniciar Sesión
                </button>
            </form>
            
             Información de usuarios de prueba 
            <div style="margin-top: 2rem; padding: 1rem; background: var(--secondary-bg); border-radius: var(--radius); font-size: 0.875rem;">
                <h4 style="color: var(--accent-green); margin-bottom: 0.5rem;">Usuarios de Prueba:</h4>
                <div style="color: var(--text-secondary);">
                    <strong>Administrador:</strong> admin / admin123<br>
                    <strong>Gerente:</strong> gerente1 / gerente123<br>
                    <strong>Empleado:</strong> empleado1 / empleado123
                </div>
            </div>
        </div>
    </div>
</body>
</html>
