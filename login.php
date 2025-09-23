<?php
// Incluimos los archivos de configuración y clases necesarias.
// Nota: Para producción, la clase Database debe conectarse a una BD real.
require_once 'config/database.php';
require_once 'config/session.php';

// Redireccionamos al dashboard si el usuario ya ha iniciado sesión.
// Esto evita que un usuario autenticado pueda ver la página de login.
if (SessionManager::verificarSesion()) {
    header('Location: dashboard.php');
    exit();
}

$error_mensaje = '';

// Verificamos si la solicitud HTTP es de tipo POST (formulario enviado).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenemos y saneamos las entradas del usuario para prevenir XSS.
    // Usamos el operador de fusión de null (??) para evitar errores si no se envían.
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validamos que los campos no estén vacíos.
    if (empty($usuario) || empty($password)) {
        $error_mensaje = 'Por favor complete todos los campos';
    } else {
        // --- Optimización clave para producción ---
        // En lugar de usar getMockUsers() y un bucle foreach,
        // debemos usar una consulta directa a la base de datos real.
        // Ejemplo de código optimizado:
        
        // 1. Conexión a la base de datos (se asume que Database::conectar() lo hace).
        $db = Database::conectar();

        // 2. Consulta a la base de datos para obtener el usuario.
        // Se usa una consulta preparada para prevenir inyecciones SQL.
        $query = "SELECT id, nombre, usuario, password_hash, activo FROM usuarios WHERE usuario = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('s', $usuario); // 's' indica que el parámetro es un string.
        $stmt->execute();
        $resultado = $stmt->get_result();

        // 3. Verificamos si se encontró un usuario.
        if ($resultado->num_rows > 0) {
            $user = $resultado->fetch_assoc();
            
            // 4. Verificamos la contraseña hasheada y el estado del usuario.
            if (password_verify($password, $user['password_hash'])) {
                if ($user['activo']) {
                    // Inicio de sesión exitoso, guardamos los datos en la sesión.
                    SessionManager::iniciarSesion($user);
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error_mensaje = 'Usuario inactivo. Contacte al administrador.';
                }
            } else {
                // Contraseña incorrecta.
                $error_mensaje = 'Usuario o contraseña incorrectos';
            }
        } else {
            // Usuario no encontrado.
            $error_mensaje = 'Usuario o contraseña incorrectos';
        }

        // Cierre de la conexión a la base de datos.
        $stmt->close();
        $db->close();
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