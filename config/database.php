<?php
/**
 * Configuración y Conexión a la Base de Datos
 * Sistema de Gestión de Recursos Humanos (HRMS)
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'hrms_system');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            // Conexión real a la base de datos usando mysqli
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            // Verificamos si la conexión fue exitosa
            if ($this->connection->connect_error) {
                // En un entorno de producción, es mejor registrar este error en un log en lugar de mostrarlo
                throw new Exception("Error de conexión a la base de datos: " . $this->connection->connect_error);
            }
            
            // Establecemos el charset a UTF-8 para evitar problemas con tildes y caracteres especiales
            $this->connection->set_charset("utf8mb4");

        } catch (Exception $e) {
            die("Error Fatal: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Valida un usuario y su contraseña desde la base de datos real.
     * @param string $usuario
     * @return array|null Retorna los datos del usuario si las credenciales son válidas, de lo contrario null.
     */
    public function getUserByUsername(string $usuario): ?array {
        // Consulta preparada para prevenir inyecciones SQL
        $stmt = $this->connection->prepare("SELECT id, usuario, password_hash, activo, rol FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario); // 's' indica que el parámetro es un string
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $user = $resultado->fetch_assoc();
        
        $stmt->close();
        
        return $user;
    }
    
    // Aquí puedes agregar otros métodos para interactuar con la base de datos
    // Por ejemplo, para obtener empleados, proyectos, etc.
}
?>