<?php


require_once 'config/database.php'; // Incluir la clase de DB
require_once 'config/session.php'; // Incluir SessionManager
require_once 'functions/utils.php';

// Inicializar DB y manejar endpoints AJAX ANTES de incluir cualquier HTML
$db = Database::getInstance(); // Instancia de la DB (necesaria para endpoints AJAX)

// Activar buffering temprano para poder limpiar salida accidental antes de responder JSON
if (!ob_get_level()) ob_start();

// --- ENDPOINT AJAX para obtener municipios por departamento ---
if (isset($_GET['action']) && $_GET['action'] === 'get_municipios' && isset($_GET['departamento'])) {
    // Limpiar cualquier output previo
    if (ob_get_level()) ob_clean();
    
    header('Content-Type: application/json');
    
    $departamento = $_GET['departamento'];
    $municipios = getMunicipiosPorDepartamento($departamento);
    
    $options = '<option value="" disabled selected hidden>Seleccione...</option>';
    foreach ($municipios as $municipio) {
        $options .= '<option value="' . htmlspecialchars($municipio) . '">' . htmlspecialchars($municipio) . '</option>';
    }
    
    echo json_encode(['success' => true, 'options' => $options]);
    exit();
}

// --- Manejo TEMPRANO de CREACIÓN por POST (intercepta peticiones AJAX antes de que header.php imprima HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['_action'])) {
    $datos_raw = $_POST;
    $datos_limpios = [];
    foreach ($datos_raw as $k => $v) {
        if ($k === 'id_empleado' || $k === 'submit') continue;
        $datos_limpios[$k] = limpiar_entrada($v);
    }

    // Validaciones rápidas
    if (isset($datos_limpios['email']) && $datos_limpios['email'] !== '' && !validar_email_unico($datos_limpios['email'])) {
        if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json')!==false)) {
            if (ob_get_level()) ob_clean(); header('Content-Type: application/json'); echo json_encode(['error'=>'El correo electrónico ya está registrado.']); exit();
        }
        $error = 'Error: El correo electrónico ya está registrado.';
    }
    if (empty($error) && isset($datos_limpios['cedula']) && $datos_limpios['cedula'] !== '' && !validar_cedula_unica($datos_limpios['cedula'])) {
        if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json')!==false)) {
            if (ob_get_level()) ob_clean(); header('Content-Type: application/json'); echo json_encode(['error'=>'La cédula ya está registrada.']); exit();
        }
        $error = 'Error: La cédula ya está registrada.';
    }

    if (empty($error)) {
        // Construir insert_data (mismos campos que el flujo principal)
        $insert_data = [];
        $allowed = [
            'codigo','cedula','nombre_completo','email','telefono','direccion','fecha_nacimiento','genero','grupo_sanguineo',
            'tipo_contrato','estado','fecha_ingreso','fecha_retiro','sede','cargo','nivel','calidad','programa','area',
            'departamento','municipio','servicio','nivel_riesgo','salario','valor_por_evento','mesada','pres_mensual','pres_anual','extras_legales',
            'auxilio_transporte','banco','tipo_cuenta','numero_cuenta','eps','afp','arl','caja_compensacion','poliza',
        ];
        foreach ($allowed as $col) {
            if (isset($datos_limpios[$col]) && $datos_limpios[$col] !== '') $insert_data[$col] = $datos_limpios[$col];
        }
        // aliases
        if (isset($datos_limpios['num_cuenta']) && $datos_limpios['num_cuenta'] !== '') $insert_data['numero_cuenta'] = $datos_limpios['num_cuenta'];
        if (isset($datos_limpios['entidad_bancaria']) && $datos_limpios['entidad_bancaria'] !== '') $insert_data['banco'] = $datos_limpios['entidad_bancaria'];
        if (isset($datos_limpios['aux_transporte']) && $datos_limpios['aux_transporte'] !== '') $insert_data['auxilio_transporte'] = $datos_limpios['aux_transporte'];

        // nombre/apellido
        $nombre_completo = isset($datos_limpios['nombre_completo']) ? trim($datos_limpios['nombre_completo']) : '';
        if ($nombre_completo !== '') {
            $parts = preg_split('/\s+/', $nombre_completo);
            $apellido_completo = count($parts) > 1 ? end($parts) : $nombre_completo;
            $insert_data['nombre_completo'] = $nombre_completo;
            $insert_data['apellido_completo'] = $apellido_completo;
        }

        // defaults y tipos
        if (empty($insert_data['tipo_contrato'])) $insert_data['tipo_contrato'] = 'LAB';
        if (empty($insert_data['estado'])) $insert_data['estado'] = 'ACTIVO';
        if (empty($insert_data['fecha_ingreso'])) $insert_data['fecha_ingreso'] = date('Y-m-d');
    if (isset($insert_data['salario'])) $insert_data['salario'] = (float)str_replace(',', '.', $insert_data['salario']);
    if (isset($insert_data['valor_por_evento'])) $insert_data['valor_por_evento'] = (float)str_replace(',', '.', $insert_data['valor_por_evento']);
    if (isset($insert_data['mesada'])) $insert_data['mesada'] = (float)str_replace(',', '.', $insert_data['mesada']);

        $nuevo_id = $db->insert('empleados', $insert_data);
        if ($nuevo_id) {
            $mensaje = "¡Empleado creado con éxito! ID: " . $nuevo_id;
            // Mejorar detección de AJAX
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
                      (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json') !== false);
            
            if ($isAjax) { 
                // Limpiar cualquier output buffer y devolver JSON
                if (ob_get_level()) ob_clean(); 
                header('Content-Type: application/json'); 
                echo json_encode(['success' => true, 'id' => $nuevo_id, 'message' => $mensaje]); 
                exit(); 
            }
            if (!headers_sent()) { header("Location: empleados.php?success=" . urlencode($mensaje)); exit(); }
        } else {
            $conn = $db->getConnection(); 
            $dbErr = ($conn instanceof mysqli) ? $conn->error : '';
            if ($dbErr && (stripos($dbErr,'Duplicate entry') !== false || ($conn instanceof mysqli && $conn->errno === 1062))) {
                $friendly = stripos($dbErr,'cedula') !== false ? 'Error: La cédula ya está registrada.' : 
                           (stripos($dbErr,'email') !== false ? 'Error: El correo electrónico ya está registrado.' : 
                            'Error: Ya existe un registro con valores duplicados.');
                
                // Mejorar detección de AJAX para errores
                $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
                          (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json') !== false);
                
                if ($isAjax) { 
                    if (ob_get_level()) ob_clean(); 
                    header('Content-Type: application/json'); 
                    echo json_encode(['error' => $friendly]); 
                    exit(); 
                }
                $error = $friendly;
            } else {
                $errorMsg = 'Error al crear el empleado.' . ($dbErr ? ' DB: '.$dbErr : '');
                
                // Verificar si es AJAX y devolver JSON para errores generales
                $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
                          (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json') !== false);
                
                if ($isAjax) { 
                    if (ob_get_level()) ob_clean(); 
                    header('Content-Type: application/json'); 
                    echo json_encode(['error' => $errorMsg]); 
                    exit(); 
                }
                $error = $errorMsg;
            }
        }
    }
    // Si llegamos aquí y no fue AJAX, dejamos que el flujo normal continúe y muestre $error/estado
}

// --- Endpoints AJAX ligeros: GET ?get=<id> -> devuelve JSON del empleado
if (isset($_GET['get']) && is_numeric($_GET['get'])) {
    $id = (int) $_GET['get'];
    $row = $db->query("SELECT * FROM empleados WHERE id = " . $id . " LIMIT 1");
    if ($row && is_array($row) && count($row) > 0) {
        $data = is_array($row[0]) ? $row[0] : $row;
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Empleado no encontrado']);
        exit();
    }
}

// --- Manejo de acciones por POST para AJAX: delete y update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])) {
    $action = $_POST['_action'];
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        if (!SessionManager::tienePermiso('gerente')) {
            header('HTTP/1.1 403 Forbidden'); echo json_encode(['error'=>'Sin permiso']); exit();
        }
        $conn = $db->getConnection();
        $stmt = $conn->prepare('DELETE FROM empleados WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        if ($ok) { echo json_encode(['success'=>true]); } else { header('HTTP/1.1 500 Internal Server Error'); echo json_encode(['error'=>$conn->error]); }
        exit();
    }
    if ($action === 'update' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        if (!SessionManager::tienePermiso('gerente')) {
            header('HTTP/1.1 403 Forbidden'); echo json_encode(['error'=>'Sin permiso']); exit();
        }
        // Aceptar alias usados en el formulario: num_cuenta -> numero_cuenta, entidad_bancaria -> banco
    $allowed = ['codigo','cedula','email','telefono','direccion','fecha_nacimiento','genero','grupo_sanguineo','tipo_contrato','estado','fecha_ingreso','fecha_retiro','sede','cargo','nivel','departamento','municipio','nivel_riesgo','valor_por_evento','mesada','salario','auxilio_transporte','banco','tipo_cuenta','numero_cuenta','num_cuenta','entidad_bancaria'];
        $updates = [];
        $types = '';
        $values = [];
        foreach ($allowed as $col) {
            if (isset($_POST[$col])) {
                // Mapear alias a los nombres reales de columna en la DB
                if ($col === 'num_cuenta') {
                    $updates[] = "numero_cuenta = ?";
                    $values[] = $_POST[$col];
                } elseif ($col === 'entidad_bancaria') {
                    $updates[] = "banco = ?";
                    $values[] = $_POST[$col];
                } else {
                    $updates[] = "$col = ?";
                    $values[] = $_POST[$col];
                }
                $types .= 's';
            }
        }
        // Validar unicidad de email si viene en el payload
        if (isset($_POST['email']) && $_POST['email'] !== '') {
            require_once 'functions/utils.php';
            if (!validar_email_unico($_POST['email'], $id)) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'El email ya pertenece a otro empleado']);
                exit();
            }
        }
        if (!empty($updates)) {
            $sql = 'UPDATE empleados SET ' . implode(', ', $updates) . ' WHERE id = ?';
            $types .= 'i';
            $values[] = $id;
            $conn = $db->getConnection();
            $stmt = $conn->prepare($sql);
            $refs = [];
            foreach ($values as $k => $v) { $refs[$k] = &$values[$k]; }
            array_unshift($refs, $types);
            call_user_func_array([$stmt, 'bind_param'], $refs);
            $ok = $stmt->execute();
            if ($ok) {
                echo json_encode(['success'=>true]);
            } else {
                // Detect duplicate key error (1062) and return user-friendly JSON error
                $dbErr = $conn->error ?? '';
                $errno = $conn->errno ?? 0;
                if ($errno === 1062 || stripos($dbErr, 'Duplicate entry') !== false) {
                    $friendly = 'Ya existe un registro con valores duplicados';
                    if (stripos($dbErr, 'cedula') !== false) $friendly = 'La c\u00e9dula ya est\u00e1 registrada.';
                    if (stripos($dbErr, 'email') !== false) $friendly = 'El correo electr\u00f3nico ya est\u00e1 registrado.';
                    if (ob_get_level()) ob_clean();
                    header('HTTP/1.1 400 Bad Request');
                    header('Content-Type: application/json');
                    echo json_encode(['error' => $friendly]);
                } else {
                    if (ob_get_level()) ob_clean();
                    header('HTTP/1.1 500 Internal Server Error');
                    header('Content-Type: application/json');
                    echo json_encode(['error'=>$dbErr]);
                }
            }
            exit();
        }
        echo json_encode(['error'=>'No fields to update']); exit();
    }
    $datos_limpios = [];
    foreach ($datos_raw as $k => $v) {
        if ($k === 'id_empleado' || $k === 'submit') continue;
        $datos_limpios[$k] = limpiar_entrada($v);
    }

    // Validaciones
    if (isset($datos_limpios['email']) && $datos_limpios['email'] !== '') {
        if (!validar_email_unico($datos_limpios['email'])) {
            if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json')!==false)) {
                if (ob_get_level()) ob_clean(); header('Content-Type: application/json'); echo json_encode(['error'=>'El correo electrónico ya está registrado.']); exit();
            }
            $error = 'Error: El correo electrónico ya está registrado.';
        }
    }
    if (empty($error) && isset($datos_limpios['cedula']) && $datos_limpios['cedula'] !== '') {
        if (!validar_cedula_unica($datos_limpios['cedula'])) {
            if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json')!==false)) {
                if (ob_get_level()) ob_clean(); header('Content-Type: application/json'); echo json_encode(['error'=>'La cédula ya está registrada.']); exit();
            }
            $error = 'Error: La cédula ya está registrada.';
        }
    }

    if (empty($error)) {
        $insert_data = [];
    $allowed = ['codigo','cedula','nombre_completo','apellido_completo','email','telefono','direccion','fecha_nacimiento','genero','grupo_sanguineo','tipo_contrato','estado','fecha_ingreso','fecha_retiro','sede','cargo','nivel','departamento','municipio','nivel_riesgo','valor_por_evento','mesada','salario','auxilio_transporte','banco','tipo_cuenta','numero_cuenta'];
        foreach ($allowed as $col) {
            if (isset($datos_limpios[$col]) && $datos_limpios[$col] !== '') $insert_data[$col] = $datos_limpios[$col];
        }
        // Map aliases (form uses variants in different templates)
        if (isset($datos_limpios['num_cuenta']) && $datos_limpios['num_cuenta'] !== '') $insert_data['numero_cuenta'] = $datos_limpios['num_cuenta'];
        if (isset($datos_limpios['entidad_bancaria']) && $datos_limpios['entidad_bancaria'] !== '') $insert_data['banco'] = $datos_limpios['entidad_bancaria'];
        if (isset($datos_limpios['aux_transporte']) && $datos_limpios['aux_transporte'] !== '') $insert_data['auxilio_transporte'] = $datos_limpios['aux_transporte'];

        // Defaults
        if (empty($insert_data['tipo_contrato'])) $insert_data['tipo_contrato'] = 'LAB';
        if (empty($insert_data['estado'])) $insert_data['estado'] = 'ACTIVO';
        if (empty($insert_data['fecha_ingreso'])) $insert_data['fecha_ingreso'] = date('Y-m-d');

        $nuevo_id = $db->insert('empleados', $insert_data);
        if ($nuevo_id) {
            $mensaje = "¡Empleado creado con éxito! ID: " . $nuevo_id;
            // Responder JSON si es AJAX
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json')!==false);
            if ($isAjax) { if (ob_get_level()) ob_clean(); header('Content-Type: application/json'); echo json_encode(['success'=>true,'id'=>$nuevo_id,'message'=>$mensaje]); exit(); }
            // No AJAX: dejar que el flujo normal continúe y el include/header.php se muestre
        } else {
            $conn = $db->getConnection(); $dbErr = ($conn instanceof mysqli) ? $conn->error : '';
            if ($dbErr && (stripos($dbErr,'Duplicate entry')!==false || ($conn instanceof mysqli && $conn->errno===1062))) {
                $friendly = stripos($dbErr,'cedula')!==false ? 'Error: La cédula ya está registrada.' : (stripos($dbErr,'email')!==false ? 'Error: El correo electrónico ya está registrado.' : 'Error: Ya existe un registro con valores duplicados.');
                if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json')!==false)) { if (ob_get_level()) ob_clean(); header('Content-Type: application/json'); echo json_encode(['error'=>$friendly]); exit(); }
                $error = $friendly;
            } else {
                $error = 'Error al crear el empleado.' . ($dbErr ? ' DB: '.$dbErr : '');
            }
        }
    }
    // Si no fue AJAX y hubo error, no hacemos exit: la página mostrará $error más abajo.
}

// Evitar "headers already sent" durante desarrollo: activar output buffering
// para permitir usar header() incluso si includes/header.php ya imprime HTML.
// Esto es un parche mínimo; idealmente el manejo de POST debería ocurrir
// antes de cualquier salida o el header.php debería no imprimir mientras
// se procesan formularios. Aquí usamos buffering para mantener el cambio pequeño.
if (!ob_get_level()) ob_start();
require_once 'includes/header.php';

// Verificar permisos
// ASUME QUE EXISTE SessionManager en algun 'require_once'
if (!SessionManager::tienePermiso('gerente')) {
    header('Location: dashboard.php');
    exit();
}

$db = Database::getInstance(); // Instancia de la DB
$mensaje = ''; // Para mensajes de éxito
$error = '';   // Para mensajes de error

// --- Endpoints AJAX ligeros: GET ?get=<id> -> devuelve JSON del empleado
if (isset($_GET['get']) && is_numeric($_GET['get'])) {
    $id = (int) $_GET['get'];
    $row = $db->query("SELECT * FROM empleados WHERE id = " . $id . " LIMIT 1");
    if ($row && is_array($row) && count($row) > 0) {
        // si query devuelve array de filas, tomar la primera
        $data = is_array($row[0]) ? $row[0] : $row;
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    } else {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Empleado no encontrado']);
        exit();
    }
}

// --- Manejo de acciones por POST para AJAX: delete y update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])) {
    $action = $_POST['_action'];
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        if (!SessionManager::tienePermiso('gerente')) {
            header('HTTP/1.1 403 Forbidden'); echo json_encode(['error'=>'Sin permiso']); exit();
        }
        $conn = $db->getConnection();
        $stmt = $conn->prepare('DELETE FROM empleados WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        if ($ok) { echo json_encode(['success'=>true]); } else { header('HTTP/1.1 500 Internal Server Error'); echo json_encode(['error'=>$conn->error]); }
        exit();
    }
    if ($action === 'update' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        if (!SessionManager::tienePermiso('gerente')) {
            header('HTTP/1.1 403 Forbidden'); echo json_encode(['error'=>'Sin permiso']); exit();
        }
        // Construir array de campos permitidos para update
    $allowed = ['codigo','cedula','email','telefono','direccion','fecha_nacimiento','genero','grupo_sanguineo','tipo_contrato','estado','fecha_ingreso','fecha_retiro','sede','cargo','nivel','departamento','municipio','nivel_riesgo','valor_por_evento','mesada','salario','auxilio_transporte','banco','tipo_cuenta','numero_cuenta'];
        $updates = [];
        $types = '';
        $values = [];
        foreach ($allowed as $col) {
            if (isset($_POST[$col])) {
                $updates[] = "$col = ?";
                $values[] = $_POST[$col];
                $types .= 's';
            }
        }
        if (!empty($updates)) {
            $sql = 'UPDATE empleados SET ' . implode(', ', $updates) . ' WHERE id = ?';
            $types .= 'i';
            $values[] = $id;
            $conn = $db->getConnection();
            $stmt = $conn->prepare($sql);
            // bind params dinamicamente
            $refs = [];
            foreach ($values as $k => $v) { $refs[$k] = &$values[$k]; }
            array_unshift($refs, $types);
            call_user_func_array([$stmt, 'bind_param'], $refs);
            $ok = $stmt->execute();
            if ($ok) {
                echo json_encode(['success'=>true]);
            } else {
                $dbErr = $conn->error ?? '';
                $errno = $conn->errno ?? 0;
                if ($errno === 1062 || stripos($dbErr, 'Duplicate entry') !== false) {
                    $friendly = 'Ya existe un registro con valores duplicados';
                    if (stripos($dbErr, 'cedula') !== false) $friendly = 'La cédula ya está registrada.';
                    if (stripos($dbErr, 'email') !== false) $friendly = 'El correo electrónico ya está registrado.';
                    if (ob_get_level()) ob_clean();
                    header('HTTP/1.1 400 Bad Request');
                    header('Content-Type: application/json');
                    echo json_encode(['error' => $friendly]);
                } else {
                    if (ob_get_level()) ob_clean();
                    header('HTTP/1.1 500 Internal Server Error');
                    header('Content-Type: application/json');
                    echo json_encode(['error'=>$dbErr]);
                }
            }
            exit();
        }
        echo json_encode(['error'=>'No fields to update']); exit();
    }
}

// =========================================================================
// INICIO: LÓGICA DE PROCESAMIENTO (MIGRADA DE nuevo_empleado.php)
// =========================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitización de Datos (Usando limpiar_entrada de utils.php)
    $datos_raw = $_POST;
    $datos_limpios = [];

    // Mapeo simple de POST a columnas de la DB, usando limpieza de string por defecto
    foreach ($datos_raw as $key => $value) {
        // Excluir claves que no sean campos de la tabla o que sean botones de submit
        if ($key === 'id_empleado' || $key === 'submit') continue;
        $datos_limpios[$key] = limpiar_entrada($value);
    }
    
    // 2. Validación de Unicidad (email + cédula)
    // Comprobar email si viene
    if (isset($datos_limpios['email']) && $datos_limpios['email'] !== '') {
        if (!validar_email_unico($datos_limpios['email'])) {
            $error = "Error: El correo electrónico ya está registrado.";
        }
    }
    // Comprobar cédula si viene
    if (empty($error) && isset($datos_limpios['cedula']) && $datos_limpios['cedula'] !== '') {
        if (!validar_cedula_unica($datos_limpios['cedula'])) {
            $error = "Error: La cédula ya está registrada.";
        }
    }

    // Si hay error de validación, dejamos $error y no intentamos el INSERT; el formulario
    // se reabrirá en el cliente con el mismo POST para permitir la corrección.
    if (empty($error)) {
        // 3. Procesamiento (Insertar en la DB)
        
        // El formulario envía 'nombre_completo' - guardaremos en las columnas
        // existentes 'nombre_completo' y 'apellido_completo'.
        $nombre_completo = isset($datos_limpios['nombre_completo']) ? trim($datos_limpios['nombre_completo']) : '';
        $apellido_completo = '';
        if ($nombre_completo !== '') {
            $parts = preg_split('/\s+/', $nombre_completo);
            // Si no hay apellidos separados, duplicamos el nombre para evitar NOT NULL
            $apellido_completo = count($parts) > 1 ? end($parts) : $nombre_completo;
        }

        // Asignar código si no se proporciona
        if (empty($datos_limpios['codigo'])) {
            $datos_limpios['codigo'] = 'EMP-' . time();
        }

        // Mapear campos del formulario a las columnas reales de la tabla
        $insert_data = [];
        // Campos permitidos y esperados por la tabla (filtrar)
        $allowed = [
            'codigo','cedula','email','telefono','direccion','fecha_nacimiento','genero','grupo_sanguineo',
            'tipo_contrato','estado','fecha_ingreso','fecha_retiro','sede','cargo','nivel','calidad','programa','area',
            'departamento','municipio','servicio','nivel_riesgo','salario','valor_por_evento','mesada','pres_mensual','pres_anual','extras_legales',
            'auxilio_transporte','banco','tipo_cuenta','numero_cuenta','eps','afp','arl','caja_compensacion','poliza',
            'certificado_manipulacion_alimentos','certificado_rcp','certificado_altura','certificado_bioseguridad',
            'fecha_fin_zo_ingreso','fecha_fin_zo_egreso','contacto_emergencia','fecha_vencimiento_registro',
            'vigencia_soporte_vital_avanzado','vigencia_victimas_violencia_sexual','vigencia_soporte_vital_basico',
            'vigencia_manejo_dolor_cuidados_paliativos','vigencia_humanizacion','vigencia_toma_muestras_lab',
            'vigencia_manejo_duelo','vigencia_manejo_residuos','vigencia_seguridad_vial','vigencia_vigiflow'
        ];

        foreach ($allowed as $col) {
            if (isset($datos_limpios[$col]) && $datos_limpios[$col] !== '') {
                $insert_data[$col] = $datos_limpios[$col];
            }
        }

        // Mapear campos del formulario que usan nombres distintos a las columnas
        if (isset($datos_limpios['num_cuenta']) && $datos_limpios['num_cuenta'] !== '') {
            $insert_data['numero_cuenta'] = $datos_limpios['num_cuenta'];
        }
        if (isset($datos_limpios['entidad_bancaria']) && $datos_limpios['entidad_bancaria'] !== '') {
            $insert_data['banco'] = $datos_limpios['entidad_bancaria'];
        }
        // El formulario usa 'aux_transporte' para mostrar el auxilio; mapear a la columna 'auxilio_transporte'
        if (isset($datos_limpios['aux_transporte']) && $datos_limpios['aux_transporte'] !== '') {
            $insert_data['auxilio_transporte'] = $datos_limpios['aux_transporte'];
        }

    // Añadir nombre_completo / apellido_completo (columnas reales de la tabla)
    if ($nombre_completo !== '') $insert_data['nombre_completo'] = $nombre_completo;
    if ($apellido_completo !== '') $insert_data['apellido_completo'] = $apellido_completo;

        // Saneamiento de tipos básicos
    if (isset($insert_data['salario'])) $insert_data['salario'] = (float)str_replace(',', '.', $insert_data['salario']);
    if (isset($insert_data['valor_por_evento'])) $insert_data['valor_por_evento'] = (float)str_replace(',', '.', $insert_data['valor_por_evento']);
    if (isset($insert_data['mesada'])) $insert_data['mesada'] = (float)str_replace(',', '.', $insert_data['mesada']);

        // Valores por defecto para columnas NOT NULL en la tabla
        if (empty($insert_data['tipo_contrato'])) $insert_data['tipo_contrato'] = 'LAB';
        if (empty($insert_data['estado'])) $insert_data['estado'] = 'ACTIVO';
        if (empty($insert_data['fecha_ingreso'])) $insert_data['fecha_ingreso'] = date('Y-m-d');
        // Usar 'mesada' como fuente principal: sincronizar salario = mesada
        if (!empty($insert_data['mesada'])) {
            $insert_data['salario'] = $insert_data['mesada'];
        } elseif (empty($insert_data['salario']) && !empty($insert_data['mesada'])) {
            $insert_data['salario'] = $insert_data['mesada'];
        }

        // Calcular días trabajados si el formulario envía fechas (leer desde POST limpio)
        $fecha_inicio = $datos_limpios['fecha_inicio'] ?? '';
        $fecha_fin = $datos_limpios['fecha_fin'] ?? '';
        if (!function_exists('calcular_dias_trabajados')) {
            // should not happen; utils.php debe definirla
        }
        $insert_data['dias_trabajados'] = (int) (calcular_dias_trabajados($fecha_inicio, $fecha_fin));

        // Calcular auxilio_transporte, pres_anual y pres_mensual en servidor
        $mesada_val = (float) ($insert_data['mesada'] ?? 0);
        $insert_data['auxilio_transporte'] = (float) calcular_aux_transporte($insert_data['tipo_contrato'] ?? '', $mesada_val);
        $insert_data['pres_anual'] = (float) calcular_pres_anual($mesada_val, $insert_data['dias_trabajados']);
        // calcular_pres_mensual espera un array con keys como mesada, extras_legales, ap_* etc.
        $insert_data['pres_mensual'] = (float) calcular_pres_mensual($insert_data);

        // Filtrar $insert_data para dejar sólo columnas que realmente existen en la tabla
        try {
            $conn = $db->getConnection();
            $existing_cols = [];
            if ($conn instanceof mysqli) {
                $schema = defined('DB_NAME') ? DB_NAME : '';
                $sqlCols = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . $conn->real_escape_string($schema) . "' AND TABLE_NAME = 'empleados'";
                $resCols = $conn->query($sqlCols);
                if ($resCols) {
                    while ($rowCol = $resCols->fetch_assoc()) {
                        $existing_cols[] = $rowCol['COLUMN_NAME'];
                    }
                    $resCols->free();
                }
            }
            // Si obtuvimos columnas, filtrar
            if (!empty($existing_cols)) {
                foreach (array_keys($insert_data) as $k) {
                    if (!in_array($k, $existing_cols, true)) {
                        unset($insert_data[$k]);
                    }
                }
            }
        } catch (Exception $e) {
            // Si falla la verificación, seguir intentando el INSERT (se capturará el error más abajo)
        }

        $nuevo_id = $db->insert('empleados', $insert_data);

        if ($nuevo_id) {
            $mensaje = "¡Empleado creado con éxito! ID: " . $nuevo_id;
            // Detectar si la petición es AJAX (XHR) o acepta JSON
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                      (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
            if ($isAjax) {
                if (ob_get_level()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'id' => $nuevo_id, 'message' => $mensaje]);
                exit();
            }
            // Intentar redireccionar para evitar re-envío del formulario.
            if (!headers_sent()) {
                header("Location: empleados.php?success=" . urlencode($mensaje));
                exit();
            }
        } else {
            // Manejar errores de la BD, en particular Duplicate Key (1062)
            $conn = $db->getConnection();
            $dbError = '';
            $friendly = '';
            if ($conn instanceof mysqli) {
                $dbError = $conn->error ?: '';
                $errno = $conn->errno ?: 0;
                // Código MySQL 1062 -> Duplicate entry
                if ($errno === 1062 || stripos($dbError, 'Duplicate entry') !== false) {
                    // Determinar qué índice causó el conflicto
                    if (stripos($dbError, 'ux_empleados_cedula') !== false || stripos($dbError, 'cedula') !== false) {
                        $friendly = 'Error: La cédula ya está registrada.';
                    } elseif (stripos($dbError, 'ux_empleados_email') !== false || stripos($dbError, 'email') !== false) {
                        $friendly = 'Error: El correo electrónico ya está registrado.';
                    } else {
                        $friendly = 'Error: Ya existe un registro con valores duplicados.';
                    }
                }
            }
            if ($friendly !== '') {
                $error = $friendly;
            } else {
                // Mensaje genérico con información de depuración local si está disponible
                $error = "Error al crear el empleado. Consulte los logs de la base de datos." . ($dbError ? ' DB error: ' . $dbError : '');
            }
        }
    }
}

// =========================================================================
// INICIO: Bloque de Carga de Datos con Paginación
// =========================================================================

// Configuración de paginación
$empleados_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$pagina_actual = max(1, $pagina_actual); // Asegurar que la página sea al menos 1

// Calcular OFFSET para la consulta
$offset = ($pagina_actual - 1) * $empleados_por_pagina;

// 1. Primero contar el total de empleados
$sql_count = "SELECT COUNT(*) as total FROM empleados WHERE estado != 'LIQUIDADO'";
$result_count = $db->query($sql_count);
$total_empleados = $result_count ? $result_count[0]['total'] : 0;

// Calcular total de páginas
$total_paginas = ceil($total_empleados / $empleados_por_pagina);

// 2. Cargar los empleados de la página actual
$sql_select = "SELECT * FROM empleados WHERE estado != 'LIQUIDADO' ORDER BY nombre_completo, apellido_completo LIMIT $empleados_por_pagina OFFSET $offset";
$empleados = $db->query($sql_select); 

if ($empleados === false) {
    // Si la consulta falla (p. ej., la tabla aún no existe o hay un error de conexión)
    $error = "Error al cargar los datos de empleados.";
    $empleados = [];
}

// =========================================================================
// FIN: Bloque de Carga de Datos con Paginación
// =========================================================================

?>
<link rel="stylesheet" href="assets/css/empleados.css">

<?php if ($mensaje): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php
// Si hubo un POST y se produjo un error de validación, inyectamos JS para reabrir
// el modal del formulario y marcar los campos implicados sin redirigir.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error)) {
    // Detectar si el error fue por cedula o email para marcar el campo
    $markCedula = (strpos($error, 'cédula') !== false) ? true : false;
    $markEmail = (strpos($error, 'correo') !== false || strpos($error, 'email') !== false) ? true : false;
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Abrir modal (usa función global definida en assets/js/empleados.js)
            if (typeof abrirModalEmpleado === 'function') {
                abrirModalEmpleado();
            }
            // Añadir clase 'is-invalid' a los campos afectados
            try {
                <?php if ($markCedula): ?>
                var ced = document.querySelector('[name="cedula"]'); if (ced) ced.classList.add('is-invalid');
                <?php endif; ?>
                <?php if ($markEmail): ?>
                var em = document.querySelector('[name="email"]'); if (em) em.classList.add('is-invalid');
                <?php endif; ?>
            } catch (e) { console.warn(e); }
        });
    </script>
    <?php
}
?>



<div class="page-content fade-in">
    <div class="page-header">
        <div class="page-actions">
            <button class="btn btn-primary" onclick="abrirModalEmpleado()">
                <i class="fas fa-user-plus"></i> Nuevo Empleado
            </button>
            <button class="btn btn-secondary" onclick="exportarEmpleados()">
                <i class="fas fa-file-export"></i> Exportar
            </button>
        </div>
        <div class="page-filters">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control" placeholder="Buscar empleado..." id="buscar-empleado">
            </div>
            <select class="form-control" id="filtro-departamento">
                <option value="">Todos los departamentos</option>
                <option value="Desarrollo">Desarrollo</option>
                <option value="Recursos Humanos">Recursos Humanos</option>
                <option value="Ventas">Ventas</option>
                <option value="Marketing">Marketing</option>
            </select>
        </div>
    </div>
    
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-value"><?php echo $total_empleados; ?></div>
            <div class="metric-label">Total Empleados</div>
            <div class="metric-icon"><i class="fas fa-users"></i></div>
        </div>
        <div class="metric-card success">
            <div class="metric-value"><?php 
                // Para el cálculo de departamentos, necesitamos todos los empleados
                $sql_dept = "SELECT DISTINCT departamento FROM empleados WHERE estado != 'LIQUIDADO' AND departamento IS NOT NULL AND departamento != ''";
                $departamentos_result = $db->query($sql_dept);
                echo $departamentos_result ? count($departamentos_result) : 0;
            ?></div> 
            <div class="metric-label">Departamentos</div>
            <div class="metric-icon"><i class="fas fa-building"></i></div>
        </div>
        <div class="metric-card warning">
             <div class="metric-value"><?php 
                // Calcular porcentaje de empleados activos
                $sql_activos = "SELECT COUNT(*) as activos FROM empleados WHERE estado != 'LIQUIDADO' AND LOWER(estado) = 'activo'";
                $activos_result = $db->query($sql_activos);
                $activos_count = $activos_result ? $activos_result[0]['activos'] : 0;
                echo $total_empleados > 0 ? round(($activos_count / $total_empleados) * 100) : 0;
             ?>%</div> 
            <div class="metric-label">Empleados Activos</div>
            <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="metric-card danger">
             <div class="metric-value"><?php 
                // Empleados nuevos este mes
                $este_mes = date('Y-m');
                $sql_nuevos = "SELECT COUNT(*) as nuevos FROM empleados WHERE estado != 'LIQUIDADO' AND DATE_FORMAT(fecha_ingreso, '%Y-%m') = '$este_mes'";
                $nuevos_result = $db->query($sql_nuevos);
                echo $nuevos_result ? $nuevos_result[0]['nuevos'] : 0;
             ?></div> 
            <div class="metric-label">Nuevos Este Mes</div>
            <div class="metric-icon"><i class="fas fa-user-plus"></i></div>
        </div>
    </div>
    
    <div class="employees-container">
        <div class="employees-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="header-text">
                    <h2 class="header-title">Lista de Empleados</h2>
                    <p class="header-subtitle">Gestión completa de empleados de la empresa</p>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($empleados); ?></span>
                    <span class="stat-label">Total Empleados</span>
                </div>
            </div>
        </div>
        
        <?php if (empty($empleados)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3 class="empty-title">No hay empleados registrados</h3>
            <p class="empty-message">Comienza agregando empleados al sistema para gestionar tu equipo de trabajo.</p>
            <button class="btn btn-primary btn-add-employee" onclick="abrirModalEmpleado()">
                <i class="fas fa-plus"></i> Agregar Primer Empleado
            </button>
        </div>
        <?php else: ?>
        
        <!-- Lista vertical moderna -->
        <div class="employees-list-vertical">
            <?php foreach ($empleados as $empleado): ?>
            <div class="employee-card-unified">
                <div class="employee-content">
                    <!-- Avatar compacto -->
                    <div class="employee-avatar-compact">
                        <span class="avatar-initials">
                            <?php 
                            $nombre_completo = $empleado['nombre_completo'] ?? '';
                            $palabras = explode(' ', trim($nombre_completo));
                            $iniciales = '';
                            if (count($palabras) >= 2) {
                                $iniciales = strtoupper(substr($palabras[0], 0, 1) . substr($palabras[1], 0, 1));
                            } else if (count($palabras) == 1) {
                                $iniciales = strtoupper(substr($palabras[0], 0, 2));
                            } else {
                                $iniciales = 'NA';
                            }
                            echo $iniciales;
                            ?>
                        </span>
                        <div class="status-indicator status-<?php echo strtolower($empleado['estado']); ?>"></div>
                    </div>
                    
                    <!-- Información horizontal del empleado -->
                    <div class="employee-info-horizontal">
                        <!-- Nombre y cargo en la primera columna (más ancha) -->
                        <div class="employee-main-info">
                            <div class="employee-name-inline" title="<?php echo htmlspecialchars($empleado['nombre_completo'] ?? 'Sin nombre'); ?>">
                                <?php echo htmlspecialchars($empleado['nombre_completo'] ?? 'Sin nombre'); ?>
                            </div>
                            <div class="employee-position-inline" title="<?php echo htmlspecialchars($empleado['cargo'] ?? 'Sin cargo'); ?>">
                                <?php echo htmlspecialchars($empleado['cargo'] ?? 'Sin cargo'); ?>
                            </div>
                        </div>
                        
                        <!-- Campos organizados en columnas -->
                        <div class="employee-field">
                            <div class="field-label">Cédula</div>
                            <div class="field-value"><?php echo htmlspecialchars($empleado['cedula']); ?></div>
                        </div>
                        
                        <div class="employee-field">
                            <div class="field-label">Email</div>
                            <div class="field-value email-field" title="<?php echo htmlspecialchars($empleado['email']); ?>">
                                <?php echo htmlspecialchars($empleado['email']); ?>
                            </div>
                        </div>
                        
                        <div class="employee-field">
                            <div class="field-label">Teléfono</div>
                            <div class="field-value"><?php echo htmlspecialchars($empleado['telefono'] ?? 'No registrado'); ?></div>
                        </div>
                        
                        <div class="employee-field">
                            <div class="field-label">Ubicación</div>
                            <div class="field-value" title="<?php echo htmlspecialchars($empleado['departamento']); ?>">
                                <?php echo htmlspecialchars($empleado['departamento']); ?>
                            </div>
                        </div>
                        
                        <div class="employee-field">
                            <div class="field-label">Ingreso</div>
                            <div class="field-value"><?php echo date('d/m/Y', strtotime($empleado['fecha_ingreso'])); ?></div>
                        </div>
                    </div>
                    
                    <!-- Badges compactos -->
                    <div class="employee-badges-compact">
                        <span class="badge-primary" title="<?php echo htmlspecialchars($empleado['tipo_contrato'] ?? 'N/A'); ?>">
                            <?php echo strtoupper($empleado['tipo_contrato'] ?? 'N/A'); ?>
                        </span>
                        <span class="badge-status status-<?php echo strtolower($empleado['estado']); ?>" title="<?php echo htmlspecialchars($empleado['estado']); ?>">
                            <?php echo strtoupper($empleado['estado']); ?>
                        </span>
                    </div>
                    
                    <!-- Acciones -->
                    <div class="employee-actions-inline">
                        <button class="action-btn action-view" onclick="verEmpleado(<?php echo $empleado['id']; ?>)" title="Ver Detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn action-edit" onclick="editarEmpleado(<?php echo $empleado['id']; ?>)" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if (SessionManager::tienePermiso('administrador')): ?>
                        <button class="action-btn action-delete" onclick="eliminarEmpleado(<?php echo $empleado['id']; ?>)" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Controles de Paginación -->
        <?php if ($total_paginas > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                <span class="pagination-text">
                    Mostrando <?php echo count($empleados); ?> de <?php echo $total_empleados; ?> empleados
                    (Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?>)
                </span>
            </div>
            
            <div class="pagination-controls">
                <?php if ($pagina_actual > 1): ?>
                    <a href="?pagina=1" class="pagination-btn pagination-first" title="Primera página">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?pagina=<?php echo $pagina_actual - 1; ?>" class="pagination-btn pagination-prev" title="Página anterior">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php 
                // Mostrar números de página (máximo 5 páginas visibles)
                $inicio = max(1, $pagina_actual - 2);
                $fin = min($total_paginas, $pagina_actual + 2);
                
                for ($i = $inicio; $i <= $fin; $i++): 
                ?>
                    <a href="?pagina=<?php echo $i; ?>" 
                       class="pagination-btn pagination-number <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_actual + 1; ?>" class="pagination-btn pagination-next" title="Página siguiente">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?pagina=<?php echo $total_paginas; ?>" class="pagination-btn pagination-last" title="Última página">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</div>


<!-- did nuevo empleado -->

<div id="modal-empleado" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-titulo">Nuevo Empleado</h3>
            <button class="modal-close" onclick="cerrarModalEmpleado()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <form id="form-nuevo-empleado" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"> 
            
                <div class="tab-header mb-lg">
                    <button type="button" class="tab-button active" onclick="mostrarSeccionModal('personal', event)">INFORMACIÓN PERSONAL</button>
                    <button type="button" class="tab-button" onclick="mostrarSeccionModal('laboral', event)">INFORMACIÓN LABORAL</button>
                    <button type="button" class="tab-button" onclick="mostrarSeccionModal('pagos', event)">INFORMACIÓN PAGOS</button>
                    <button type="button" class="tab-button" onclick="mostrarSeccionModal('aportes', event)">INFORMACIÓN APORTES</button>
                    <button type="button" class="tab-button" onclick="mostrarSeccionModal('cursos', event)">VIGENCIA DE CURSOS</button>
                </div>

                <div id="personal" class="tab-content-modal active grid-2" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    
                    <div class="form-group">
                        <label for="tipo_contrato">CONTRATO</label>
                        <select id="tipo_contrato" name="tipo_contrato" class="form-control" required>
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getContratoOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="codigo">CÓDIGO</label>
                        <input type="text" id="codigo" name="codigo" class="form-control" placeholder="Se autogenerará si está vacío">
                    </div>
                    <div class="form-group">
                        <label for="estado">ESTADO *</label>
                        <select id="estado" name="estado" class="form-control" required>
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getEstadoOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cedula">CÉDULA (DOCUMENTO DE IDENTIDAD)</label>
                        <input type="text" id="cedula" name="cedula" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="nombre_completo">NOMBRE</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">FECHA DE NACIMIENTO</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="direccion">DIRECCIÓN</label>
                        <input type="text" id="direccion" name="direccion" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="email">CORREO (CORREO ELECTRÓNICO)</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">CELULAR</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="poliza">PÓLIZA</label>
                        <select id="poliza" name="poliza" class="form-control">
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getPolizaOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="genero">GÉNERO *</label>
                        <select id="genero" name="genero" class="form-control" required>
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getGeneroOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="grupo_sanguineo">GRUPO SANGUÍNEO (RH) *</label>
                        <select id="grupo_sanguineo" name="grupo_sanguineo" class="form-control" required>
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getGrupoSanguineoOptions()); ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_fin_zo_ingreso">FECHA EXM. OCUPACIONAL INGRESO</label>
                        <input type="date" id="fecha_fin_zo_ingreso" name="fecha_fin_zo_ingreso" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin_zo_egreso">FECHA EXM. OCUPACIONAL EGRESO</label>
                        <input type="date" id="fecha_fin_zo_egreso" name="fecha_fin_zo_egreso" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contacto_emergencia">CONTACTO EMERGENCIA</label>
                        <input type="text" id="contacto_emergencia" name="contacto_emergencia" class="form-control" placeholder="Nombre y Teléfono">
                    </div>
                    
                </div>

                <div id="laboral" class="tab-content-modal grid-2" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    
                    <div class="form-group">
                        <label for="sede">SEDE</label>
                        <input type="text" id="sede" name="sede" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="cargo">CARGO </label>
                        <select id="cargo" name="cargo" class="form-control" required>
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getCargoOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nivel">NIVEL</label>
                        <select id="nivel" name="nivel" class="form-control">
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getNivelOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="calidad">CALIDAD</label>
                        <select id="calidad" name="calidad" class="form-control">
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getCalidadOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="programa">PROGRAMA</label>
                        <select id="programa" name="programa" class="form-control">
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getProgramaOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="area">ÁREA</label>
                        <select id="area" name="area" class="form-control">
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getAreaOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="intramural">INTRAMURAL</label>
                        <input type="text" id="intramural" name="intramural" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="departamento">DEPARTAMENTO </label>
                        <select id="departamento" name="departamento" class="form-control" required>
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getDepartamentoOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="municipio">MUNICIPIO </label>
                        <select id="municipio" name="municipio" class="form-control" required>
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getMunicipioOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="servicio">SERVICIO</label>
                        <select id="servicio" name="servicio" class="form-control">
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getServicioOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha_inicio">FECHA DE INICIO (Contrato)</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">FECHA FIN (Contrato)</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" onchange="calcularDiasModal()">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin_contrato">FECHA FIN DE CONTRATO</label>
                        <input type="date" id="fecha_fin_contrato" name="fecha_fin_contrato" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="nivel_riesgo">NIVEL DE RIESGO</label>
                        <select id="nivel_riesgo" name="nivel_riesgo" class="form-control">
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getNivelRiesgoOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="eps">EPS</label>
                        <input type="text" id="eps" name="eps" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="arl">ARL</label>
                        <input type="text" id="arl" name="arl" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="afp">AFP</label>
                        <input type="text" id="afp" name="afp" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="fecha_vencimiento_registro">FECHA VENCIMIENTO DE REGISTRO</label>
                        <input type="date" id="fecha_vencimiento_registro" name="fecha_vencimiento_registro" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="dias_trabajados">DÍAS TRABAJADOS (Auto)</label>
                        <input type="number" id="dias_trabajados" name="dias_trabajados" class="form-control" readonly placeholder="Calculado por DB">
                    </div>
                </div>

                <div id="pagos" class="tab-content-modal grid-2" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    
                    <div class="form-group">
                        <label for="valor_por_evento">VALOR POR EVENTO</label>
                        <input type="number" step="0.01" id="valor_por_evento" name="valor_por_evento" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="mesada">MESADA</label>
                        <input type="number" step="0.01" id="mesada" name="mesada" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="pres_mensual">PRES MENSUAL (Auto)</label>
                        <input type="number" step="0.01" id="pres_mensual" name="pres_mensual" class="form-control" readonly placeholder="Calculado por DB">
                    </div>
                    <div class="form-group">
                        <label for="pres_anual">PRES ANUAL (Auto)</label>
                        <input type="number" step="0.01" id="pres_anual" name="pres_anual" class="form-control" readonly placeholder="Calculado por DB">
                    </div>
                    <div class="form-group">
                        <label for="extras_legales">EXTRAS LEGALES</label>
                        <input type="number" step="0.01" id="extras_legales" name="extras_legales" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="auxilio_transporte">AUX. TRANSPORTE (Auto)</label>
                        <input type="number" step="0.01" id="auxilio_transporte" name="auxilio_transporte" class="form-control" readonly placeholder="Calculado por DB">
                    </div>
                    <div class="form-group">
                        <label for="num_cuenta">NUM. CUENTA</label>
                        <input type="text" id="num_cuenta" name="numero_cuenta" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="entidad_bancaria">ENTIDAD BANCARIA</label>
                        <input type="text" id="entidad_bancaria" name="banco" class="form-control">
                    </div>
                </div>

                <div id="aportes" class="tab-content-modal grid-2" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    
                    <div class="form-group">
                        <label for="tasa_arl">TASA ARL (Auto)</label>
                        <input type="text" id="tasa_arl" name="tasa_arl" class="form-control" readonly placeholder="Calculado por DB">
                    </div>
                    <div class="form-group">
                        <label for="ap_salud_mes">AP. SALUD MES</label>
                        <input type="number" step="0.01" id="ap_salud_mes" name="ap_salud_mes" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ap_pension_mes">AP. PENSIÓN MES</label>
                        <input type="number" step="0.01" id="ap_pension_mes" name="ap_pension_mes" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ap_arl_mes">AP. ARL MES</label>
                        <input type="number" step="0.01" id="ap_arl_mes" name="ap_arl_mes" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ap_caja_mes">AP. CAJA MES</label>
                        <input type="number" step="0.01" id="ap_caja_mes" name="ap_caja_mes" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ap_sena_mes">AP. SENA MES</label>
                        <input type="number" step="0.01" id="ap_sena_mes" name="ap_sena_mes" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ap_icbf_mes">AP. ICBF MES</label>
                        <input type="number" step="0.01" id="ap_icbf_mes" name="ap_icbf_mes" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ap_cesantia_anual">AP. CESANTÍA ANUAL</label>
                        <input type="number" step="0.01" id="ap_cesantia_anual" name="ap_cesantia_anual" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ap_interes_cesantias_anual">AP. INTERÉS CESANTÍAS ANUAL</label>
                        <input type="number" step="0.01" id="ap_interes_cesantias_anual" name="ap_interes_cesantias_anual" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="ap_prima_anual">AP. PRIMA ANUAL</label>
                        <input type="number" step="0.01" id="ap_prima_anual" name="ap_prima_anual" class="form-control">
                    </div>
                </div>

                <div id="cursos" class="tab-content-modal grid-2" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    
                    <div class="form-group">
                        <label for="vigencia_soporte_vital_avanzado">SOPORTE VITAL AVANZADO (Vigencia)</label>
                        <input type="date" id="vigencia_soporte_vital_avanzado" name="vigencia_soporte_vital_avanzado" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_victimas_violencia_sexual">ATENCIÓN VÍCTIMAS DE VIOLENCIA SEXUAL (Vigencia)</label>
                        <input type="date" id="vigencia_victimas_violencia_sexual" name="vigencia_victimas_violencia_sexual" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_soporte_vital_basico">CURSO SOPORTE VITAL BÁSICO (Vigencia)</label>
                        <input type="date" id="vigencia_soporte_vital_basico" name="vigencia_soporte_vital_basico" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_manejo_dolor_cuidados_paliativos">CURSO MANEJO DEL DOLOR Y CP (Vigencia)</label>
                        <input type="date" id="vigencia_manejo_dolor_cuidados_paliativos" name="vigencia_manejo_dolor_cuidados_paliativos" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_humanizacion">CURSO HUMANIZACIÓN (Vigencia)</label>
                        <input type="date" id="vigencia_humanizacion" name="vigencia_humanizacion" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_toma_muestras_lab">CURSO DE TOMA DE MUESTRAS LABORATORIO (Vigencia)</label>
                        <input type="date" id="vigencia_toma_muestras_lab" name="vigencia_toma_muestras_lab" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_manejo_duelo">MANEJO DEL DUELO (Vigencia)</label>
                        <input type="date" id="vigencia_manejo_duelo" name="vigencia_manejo_duelo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_manejo_residuos">MANEJO RESIDUOS (Vigencia)</label>
                        <input type="date" id="vigencia_manejo_residuos" name="vigencia_manejo_residuos" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_seguridad_vial">SEGURIDAD VIAL (Vigencia)</label>
                        <input type="date" id="vigencia_seguridad_vial" name="vigencia_seguridad_vial" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_vigiflow">VIGIFLOW (Vigencia)</label>
                        <input type="date" id="vigencia_vigiflow" name="vigencia_vigiflow" class="form-control">
                    </div>
                </div>
            </form>
            </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-danger" onclick="cerrarModalEmpleado()">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary" form="form-nuevo-empleado">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>
<!-- fin nuevo empleado -->

<script src="assets/js/empleados.js"></script>

<script>
    // Función de JavaScript para cambiar entre secciones (TABS) dentro del modal
    function mostrarSeccionModal(seccionId, event) {
        // Prevenir propagación del evento para evitar conflictos
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Oculta todas las secciones
        document.querySelectorAll('#modal-empleado .tab-content-modal').forEach(content => {
            content.style.display = 'none';
            content.classList.remove('active');
        });
        
        // Desactiva todos los botones del modal específicamente
        document.querySelectorAll('#modal-empleado .tab-header .tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Muestra la sección seleccionada y la activa
        const selSection = document.getElementById(seccionId);
        if (selSection) {
            selSection.style.display = 'grid';
            selSection.classList.add('active');
        }

        // Activa el botón de la pestaña correspondiente
        const tabBtn = document.querySelector(`#modal-empleado .tab-header button[onclick*="'${seccionId}'"]`);
        if (tabBtn) {
            tabBtn.classList.add('active');
        }
    }
    
    // Función para inicializar el modal
    function inicializarModalEmpleado() {
        mostrarSeccionModal('personal'); // Mostrar 'INFORMACIÓN PERSONAL' al inicio
        
        // Añadir event listeners específicos para los botones del modal
        const modalButtons = document.querySelectorAll('#modal-empleado .tab-header .tab-button');
        modalButtons.forEach(button => {
            // Remover listeners previos para evitar duplicados
            button.removeEventListener('click', handleModalTabClick);
            // Añadir nuevo listener
            button.addEventListener('click', handleModalTabClick);
        });
        
        // Inicializar el manejo de campos según tipo de contrato
        const tipoContratoSelect = document.getElementById('tipo_contrato');
        if (tipoContratoSelect) {
            // Remover listeners previos para evitar duplicados
            tipoContratoSelect.removeEventListener('change', manejarCamposContrato);
            // Añadir nuevo listener
            tipoContratoSelect.addEventListener('change', manejarCamposContrato);
            
            // Ejecutar una vez para inicializar el estado
            setTimeout(() => manejarCamposContrato(), 50);
        }
        
        // Inicializar el manejo de municipios según departamento
        const departamentoSelect = document.getElementById('departamento');
        if (departamentoSelect) {
            // Remover listeners previos para evitar duplicados
            departamentoSelect.removeEventListener('change', cargarMunicipiosPorDepartamento);
            // Añadir nuevo listener
            departamentoSelect.addEventListener('change', cargarMunicipiosPorDepartamento);
            
            // Si ya hay un departamento seleccionado, cargar sus municipios
            if (departamentoSelect.value) {
                setTimeout(() => cargarMunicipiosPorDepartamento(), 50);
            }
        }
    }
    
    // Handler específico para clics en pestañas del modal
    function handleModalTabClick(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const button = event.currentTarget;
        const onclick = button.getAttribute('onclick');
        
        // Extraer el ID de sección del onclick
        const match = onclick.match(/mostrarSeccionModal\('([^']+)'/);
        if (match) {
            const seccionId = match[1];
            mostrarSeccionModal(seccionId, event);
        }
    }
    
    // Hacer la función disponible globalmente
    window.inicializarModalEmpleado = inicializarModalEmpleado;
    
    // Inicializar cuando se cargue la página
    document.addEventListener('DOMContentLoaded', () => {
        inicializarModalEmpleado();
    });
    
    // También inicializar cuando se abra el modal
    const modalElement = document.getElementById('modal-empleado');
    if (modalElement) {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const modal = mutation.target;
                    if (modal.style.display === 'flex' || modal.classList.contains('show')) {
                        setTimeout(() => inicializarModalEmpleado(), 50);
                    }
                }
            });
        });
        observer.observe(modalElement, { attributes: true, attributeFilter: ['style', 'class'] });
    }

    // Función para el cálculo de Días Trabajados en el frontend del modal
    function calcularDiasModal() {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;
        
        if (fechaInicio && fechaFin) {
            const date1 = new Date(fechaInicio);
            const date2 = new Date(fechaFin);
            const diffTime = Math.abs(date2 - date1);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
            document.getElementById('dias_trabajados').value = diffDays;
        } else {
            document.getElementById('dias_trabajados').value = 0;
        }
    }

    // Función para manejar campos según tipo de contrato
    function manejarCamposContrato() {
        const tipoContrato = document.getElementById('tipo_contrato').value;
        const campoMesada = document.getElementById('mesada');
        const campoValorEvento = document.getElementById('valor_por_evento');
        
        if (tipoContrato === 'OPS') {
            // Si es OPS: deshabilitar MESADA, habilitar VALOR POR EVENTO
            campoMesada.disabled = true;
            campoMesada.value = '';
            campoMesada.placeholder = 'No aplica para contrato OPS';
            
            campoValorEvento.disabled = false;
            campoValorEvento.placeholder = '';
        } else if (tipoContrato === 'LAB') {
            // Si es LAB: deshabilitar VALOR POR EVENTO, habilitar MESADA
            campoValorEvento.disabled = true;
            campoValorEvento.value = '';
            campoValorEvento.placeholder = 'No aplica para contrato LAB';
            
            campoMesada.disabled = false;
            campoMesada.placeholder = '';
        } else {
            // Si no hay selección: habilitar ambos campos
            campoMesada.disabled = false;
            campoMesada.placeholder = '';
            
            campoValorEvento.disabled = false;
            campoValorEvento.placeholder = '';
        }
    }

    // Función para cargar municipios según departamento seleccionado
    function cargarMunicipiosPorDepartamento() {
        const departamentoSelect = document.getElementById('departamento');
        const municipioSelect = document.getElementById('municipio');
        
        if (!departamentoSelect || !municipioSelect) return;
        
        const departamento = departamentoSelect.value;
        
        if (!departamento) {
            // Si no hay departamento seleccionado, limpiar municipios
            municipioSelect.innerHTML = '<option value="" disabled selected hidden>Seleccione...</option>';
            return;
        }
        
        // Mostrar loading
        municipioSelect.innerHTML = '<option value="" disabled selected>Cargando...</option>';
        municipioSelect.disabled = true;
        
        // Hacer petición AJAX
        fetch(`empleados.php?action=get_municipios&departamento=${encodeURIComponent(departamento)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    municipioSelect.innerHTML = data.options;
                    municipioSelect.disabled = false;
                } else {
                    municipioSelect.innerHTML = '<option value="" disabled selected>Error al cargar</option>';
                    municipioSelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error al cargar municipios:', error);
                municipioSelect.innerHTML = '<option value="" disabled selected>Error al cargar</option>';
                municipioSelect.disabled = false;
            });
    }

    // Función auxiliar para resetear el select de municipios
    function resetearMunicipios() {
        const municipioSelect = document.getElementById('municipio');
        if (municipioSelect) {
            municipioSelect.innerHTML = '<option value="" disabled selected hidden>Seleccione...</option>';
            municipioSelect.disabled = false;
        }
    }
</script>


<?php require_once 'includes/footer.php'; ?>