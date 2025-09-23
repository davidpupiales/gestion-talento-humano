<?php
/**
 * Configuración de Base de Datos
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
        // Por ahora usamos datos mock, pero aquí iría la conexión real
        // $this->connection = new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME, DB_USER, DB_PASS);
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
    
    // Datos mock para desarrollo
    public static function getMockUsers() {
        return [
            [
                'id' => 1,
                'usuario' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'nombre' => 'Administrador Sistema',
                'email' => 'admin@empresa.com',
                'rol' => 'administrador',
                'activo' => 1
            ],
            [
                'id' => 2,
                'usuario' => 'gerente1',
                'password' => password_hash('gerente123', PASSWORD_DEFAULT),
                'nombre' => 'María González',
                'email' => 'maria.gonzalez@empresa.com',
                'rol' => 'gerente',
                'activo' => 1
            ],
            [
                'id' => 3,
                'usuario' => 'empleado1',
                'password' => password_hash('empleado123', PASSWORD_DEFAULT),
                'nombre' => 'Juan Pérez',
                'email' => 'juan.perez@empresa.com',
                'rol' => 'empleado',
                'activo' => 1
            ]
        ];
    }
    
    // Datos mock de empleados
    public static function getMockEmpleados() {
        return [
            [
                'id' => 1,
                'codigo' => 'EMP001',
                'nombre' => 'Juan Pérez',
                'apellido' => 'García',
                'cedula' => '12345678',
                'email' => 'juan.perez@empresa.com',
                'telefono' => '555-0123',
                'departamento' => 'Desarrollo',
                'cargo' => 'Desarrollador Senior',
                'fecha_ingreso' => '2023-01-15',
                'salario_base' => 50000,
                'estado' => 'activo'
            ],
            [
                'id' => 2,
                'codigo' => 'EMP002',
                'nombre' => 'María González',
                'apellido' => 'López',
                'cedula' => '87654321',
                'email' => 'maria.gonzalez@empresa.com',
                'telefono' => '555-0124',
                'departamento' => 'Recursos Humanos',
                'cargo' => 'Gerente RRHH',
                'fecha_ingreso' => '2022-03-10',
                'salario_base' => 75000,
                'estado' => 'activo'
            ]
        ];
    }
}
?>
