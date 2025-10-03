<?php
/**
 * Configuración y Conexión a la Base de Datos
 * Sistema de Gestión de Recursos Humanos (HRMS)
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'rrhh_personal');
define('DB_USER', 'root');
define('DB_PASS', '123456789');

class Database {
    private static $instance = null;
    private $connection;
    
    // ... (El constructor y getInstance permanecen igual) ...
    
    private function __construct() {
        try {
            // Conexión real a la base de datos usando mysqli
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            // Verificamos si la conexión fue exitosa
            if ($this->connection->connect_error) {
                throw new Exception("Error de conexión a la base de datos: " . $this->connection->connect_error);
            }
            
            // Establecemos el charset a UTF-8 para evitar problemas con tildes y caracteres especiales
            $this->connection->set_charset("utf8mb4");

        } catch (Exception $e) {
            // En un entorno de producción, esto debería loguearse, no morir.
            die("Error Fatal de DB: " . $e->getMessage());
        }
    }
    
    // Método para obtener la única instancia de la clase (Singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
      // Evitar clonación y deserialización
    private function __clone() {}
    public function __wakeup() {}

// ===============================================
    // Métodos CRUD básicos
    // ===============================================

    /**
     * Ejecuta una consulta SQL simple y retorna un array asociativo.
     * @param string $sql
     * @return array|bool Retorna el resultado como array o false en caso de error.
     */
    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$result) {
            error_log("Error en la consulta: " . $this->connection->error . " | SQL: " . $sql);
            return false;
        }

        $data = [];
        // Verificar si la consulta es de selección (SELECT)
        if ($result instanceof mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $result->free();
        }
        return $data;
    }

    /**
     * Obtiene un usuario por su nombre de usuario (utilizado en login.php).
     * @param string $username
     * @return array|null
     */
    public function getUserByUsername(string $username) {
        $sql = "SELECT id, username, email, password_hash, rol, activo FROM users WHERE username = ?";
        
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            error_log("Error al preparar getUserByUsername: " . $this->connection->error);
            return null;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        $stmt->close();
        return $user;
    }


    /**
     * Prepara y ejecuta una sentencia INSERT usando sentencias preparadas (Prepared Statements).
     * @param string $tabla Nombre de la tabla (e.g., 'empleados').
     * @param array $datos Array asociativo [columna => valor].
     * @return int|bool ID del nuevo registro o false si falla.
     */
    public function insert(string $tabla, array $datos) {
        if (empty($datos)) return false;
        

        // Preparar columnas y placeholders
        $columnas = implode(', ', array_keys($datos));
        $placeholders = implode(', ', array_fill(0, count($datos), '?'));
        
        $sql = "INSERT INTO {$tabla} ({$columnas}) VALUES ({$placeholders})";
        
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            error_log("Error al preparar INSERT: " . $this->connection->error . " | SQL: " . $sql);
            return false;
        }

        // Obtener tipos y valores para bind_param
        $valores = array_values($datos);
        // Función simple de detección de tipos (i: int, d: float/double, s: string)
        $tipos = str_repeat('s', count($valores)); 

        // Preparar parámetros pasando referencias correctamente (necesario para mysqli_stmt::bind_param)
        $params = [];
        $params[] = $tipos;
        for ($i = 0; $i < count($valores); $i++) {
            // crear referencia a cada elemento
            $params[] = & $valores[$i];
        }

        // Llamada a bind_param con parámetros por referencia
        call_user_func_array([$stmt, 'bind_param'], $params);

        if ($stmt->execute()) {
            $nuevo_id = $this->connection->insert_id;
            $stmt->close();
            return $nuevo_id;
        }
        
        error_log("Error al ejecutar INSERT: " . $stmt->error);
        $stmt->close();
        return false;
    }

    /** Helper para resolver el problema de bind_param con call_user_func_array */
    private function refValues(array $arr) {
        if (strnatcmp(phpversion(),'5.3') >= 0) {
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }

    /** Obtiene la conexión raw (útil para transacciones avanzadas, no recomendado para uso general) */
    
    public function getConnection() {
        return $this->connection;
    }
    
    // /**
    //  * Valida un usuario y su contraseña desde la base de datos real.
    //  * @param string $usuario
    //  * @return array|null Retorna los datos del usuario si las credenciales son válidas, de lo contrario null.
    //  */
    // public function getUserByUsername(string $usuario): ?array {
    //     // Consulta preparada para prevenir inyecciones SQL
    //     $stmt = $this->connection->prepare("SELECT id, usuario, password_hash, activo, rol FROM usuarios WHERE usuario = ?");
    //     $stmt->bind_param("s", $usuario);
    //     $stmt->execute();
    //     $resultado = $stmt->get_result();
        
    //     $user = $resultado->fetch_assoc();
        
    //     $stmt->close();
        
    //     return $user;
    // }

    // /**
    //  * Inserta un nuevo registro de personal en la base de datos.
    //  * @param array $datos - Array asociativo con todos los datos del empleado.
    //  * @return int|bool Retorna el ID del nuevo registro o false si falla.
    //  */
    // public function crearNuevoPersonal(array $datos): int|bool {
    //     // Mapeo directo de campos de la tabla 'personal'
    //     $campos = [
    //         'tipo_contrato', 'codigo', 'estado', 'cedula', 'nombre_completo', 'fecha_nacimiento', 'direccion', 
    //         'email', 'telefono', 'grupo_sanguineo', 'genero', 'fecha_ingreso', 'fecha_fin_zo_ingreso', 'fecha_fin_zo_egreso',
    //         'contacto_emergencia', 'sede', 'cargo', 'nivel', 'calidad', 'programa', 'area', 'intramural', 
    //         'departamento', 'municipio', 'servicio', 'fecha_inicio', 'fecha_fin', 'fecha_fin_contrato', 
    //         'nivel_riesgo', 'eps', 'arl', 'afp', 'fecha_vencimiento_registro', 'dias_trabajados', 
    //         'valor_por_evento', 'mesada', 'pres_mensual', 'pres_anual', 'extras_legales', 'aux_transporte', 
    //         'num_cuenta', 'entidad_bancaria', 'tasa_arl', 'ap_salud_mes', 'ap_pension_mes', 
    //         'ap_arl_mes_ap_caja_mes', 'ap_sena_mes', 'ap_icbf_mes', 'ap_cesantia_anual', 
    //         'ap_interes_cesantias_anual', 'ap_prima_anual', 
    //         'vigencia_soporte_vital_avanzado', 'vigencia_victimas_violencia_sexual', 
    //         'vigencia_soporte_vital_basico', 'vigencia_manejo_dolor_cuidados_paliativos', 
    //         'vigencia_humanizacion_toma_muestras', 'vigencia_manejo_duelo', 'vigencia_manejo_residuos', 
    //         'vigencia_seguridad_vial', 'vigencia_vigiflow'
    //     ];
        
    //     // Crear marcadores y string de campos
    //     $marcadores = implode(', ', array_fill(0, count($campos), '?'));
    //     $campos_sql = implode(', ', $campos);
        
    //     $sql = "INSERT INTO personal ({$campos_sql}) VALUES ({$marcadores})";
        
    //     $stmt = $this->connection->prepare($sql);
        
    //     // Obtener los valores en el orden correcto y definir tipos (simplificado a 's' para la mayoría, 'd' para decimales/mesada)
    //     // **IMPORTANTE**: La tabla `personal` no tiene 'nombre_completo', 'cedula', 'email', etc., si no están en `schema.sql`.
    //     // Para que esto funcione, es crucial que primero se ejecute la **actualización del schema.sql** (ver sección 3.1).
        
    //     // Simulación de los valores (asumiendo que $datos ya está limpio y validado)
    //     $valores = array_values($datos);
        
    //     // Detección automática de tipos para mysqli_stmt_bind_param (simplificado)
    //     $tipos = str_repeat('s', count($valores));
        
    //     // Uso de referencias para bind_param
    //     $params = array_merge([$tipos], $this->refValues($valores));
        
    //     // @phpstan-ignore-next-line
    //     call_user_func_array([$stmt, 'bind_param'], $params);

    //     if ($stmt->execute()) {
    //         $nuevo_id = $this->connection->insert_id;
    //         $stmt->close();
    //         return $nuevo_id;
    //     }
        
    //     $stmt->close();
    //     return false;
    // }

    /** Helper para resolver el problema de bind_param con call_user_func_array */
    
    // ... (getEmpleados() y otros métodos si son necesarios) ...
}