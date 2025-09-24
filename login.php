<?php
// Incluimos los archivos de configuración y clases necesarias.
require_once 'config/database.php';
require_once 'config/session.php';

// Redireccionamos al dashboard si el usuario ya ha iniciado sesión.
if (SessionManager::verificarSesion()) {
    header('Location: dashboard.php');
    exit();
}

$error_mensaje = '';

// Verificamos si la solicitud HTTP es de tipo POST (formulario enviado).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenemos las entradas del usuario.
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validamos que los campos no estén vacíos.
    if (empty($usuario) || empty($password)) {
        $error_mensaje = 'Por favor complete todos los campos';
    } else {
        // Obtenemos la instancia de la base de datos.
        $db_manager = Database::getInstance();
        
        // Obtenemos el usuario por su nombre de usuario.
        $user = $db_manager->getUserByUsername($usuario);

        // Verificamos si se encontró un usuario y si la contraseña es correcta.
        // La función password_verify es segura para comparar contraseñas hasheadas.
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['activo']) {
                // Inicio de sesión exitoso, guardamos los datos en la sesión.
                SessionManager::iniciarSesion($user);
                header('Location: dashboard.php');
                exit();
            } else {
                $error_mensaje = 'Usuario inactivo. Contacte al administrador.';
            }
        } else {
            // Usuario no encontrado o contraseña incorrecta.
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
    <div class="login-wrapper">
        <div class="info-section">
            <div class="logo">
                <img src="assets/img/logoCS.png" alt="Logo Conexión Salud Fundación" class="logo-image">
            </div>
            <h1 class="welcome-title">Bienvenido</h1>
            <p class="welcome-subtitle">
                Su plataforma integral de gestión de talento humano.
                Inicie sesión para acceder a todas las funcionalidades.
            </p>
        </div>
        <div class="form-section">
            <div class="login-card">
                <h2 class="form-title">Iniciar Sesión</h2>
                <p class="form-description">
                    Por favor ingrese sus credenciales para continuar.
                </p>

                <?php if (!empty($error_mensaje)): ?>
                    <div class="error-message">
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
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
                        Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>