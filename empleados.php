<?php


require_once 'config/database.php'; // Incluir la clase de DB
require_once 'config/session.php'; // Incluir SessionManager
require_once 'functions/utils.php';

// Inicializar DB y manejar endpoints AJAX ANTES de incluir cualquier HTML
$db = Database::getInstance(); // Instancia de la DB (necesaria para endpoints AJAX)

// Activar buffering temprano para poder limpiar salida accidental antes de responder JSON
if (!ob_get_level()) ob_start();

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
            'departamento','municipio','servicio','nivel_riesgo','smlv','salario','valor_por_evento','mesada','pres_mensual','pres_anual','extras_legales',
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
    if (isset($insert_data['smlv'])) $insert_data['smlv'] = (float)str_replace(',', '.', $insert_data['smlv']);
    if (isset($insert_data['salario'])) $insert_data['salario'] = (float)str_replace(',', '.', $insert_data['salario']);
    if (isset($insert_data['valor_por_evento'])) $insert_data['valor_por_evento'] = (float)str_replace(',', '.', $insert_data['valor_por_evento']);
    if (isset($insert_data['mesada'])) $insert_data['mesada'] = (float)str_replace(',', '.', $insert_data['mesada']);

        $nuevo_id = $db->insert('empleados', $insert_data);
        if ($nuevo_id) {
            $mensaje = "¡Empleado creado con éxito! ID: " . $nuevo_id;
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json')!==false);
            if ($isAjax) { if (ob_get_level()) ob_clean(); header('Content-Type: application/json'); echo json_encode(['success'=>true,'id'=>$nuevo_id,'message'=>$mensaje]); exit(); }
            if (!headers_sent()) { header("Location: empleados.php?success=" . urlencode($mensaje)); exit(); }
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
            'departamento','municipio','servicio','nivel_riesgo','smlv','salario','valor_por_evento','mesada','pres_mensual','pres_anual','extras_legales',
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
    if (isset($insert_data['smlv'])) $insert_data['smlv'] = (float)str_replace(',', '.', $insert_data['smlv']);
    if (isset($insert_data['salario'])) $insert_data['salario'] = (float)str_replace(',', '.', $insert_data['salario']);
    if (isset($insert_data['valor_por_evento'])) $insert_data['valor_por_evento'] = (float)str_replace(',', '.', $insert_data['valor_por_evento']);
    if (isset($insert_data['mesada'])) $insert_data['mesada'] = (float)str_replace(',', '.', $insert_data['mesada']);

        // Valores por defecto para columnas NOT NULL en la tabla
        if (empty($insert_data['tipo_contrato'])) $insert_data['tipo_contrato'] = 'LAB';
        if (empty($insert_data['estado'])) $insert_data['estado'] = 'ACTIVO';
        if (empty($insert_data['fecha_ingreso'])) $insert_data['fecha_ingreso'] = date('Y-m-d');
        if (empty($insert_data['smlv'])) $insert_data['smlv'] = SMLV;
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
// INICIO: Bloque de Carga de Datos (SELECT)
// =========================================================================

// 1. Cargar los empleados desde la base de datos
$sql_select = "SELECT * FROM empleados WHERE estado != 'LIQUIDADO' ORDER BY apellido_completo, nombre_completo";
$empleados = $db->query($sql_select); 

if ($empleados === false) {
    // Si la consulta falla (p. ej., la tabla aún no existe o hay un error de conexión)
    $error = "Error al cargar los datos de empleados.";
    $empleados = [];
}

// =========================================================================
// FIN: Bloque de Carga de Datos
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
            <div class="metric-value"><?php echo count($empleados); ?></div>
            <div class="metric-label">Total Empleados</div>
            <div class="metric-icon"><i class="fas fa-users"></i></div>
        </div>
        <div class="metric-card success">
            <div class="metric-value">0</div> 
            <div class="metric-label">Departamentos</div>
            <div class="metric-icon"><i class="fas fa-building"></i></div>
        </div>
        <div class="metric-card warning">
             <div class="metric-value">0%</div> 
            <div class="metric-label">Empleados Activos</div>
            <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="metric-card danger">
             <div class="metric-value">0</div> 
            <div class="metric-label">Nuevos Este Mes</div>
            <div class="metric-icon"><i class="fas fa-user-plus"></i></div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i>
                Lista de Empleados
            </h3>
            <div class="card-subtitle">
                Gestión completa de empleados de la empresa
            </div>
        </div>
        
        <div class="table-container d-none d-lg-block">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Departamento</th>
                        <th>Cargo</th>
                        <th>Fecha Ingreso</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // El bucle sigue intacto y funcionará cuando $empleados contenga datos de la DB
                    foreach ($empleados as $empleado): 
                    ?>
                    <tr>
                        <td>
                            <span class="badge badge-info">
                                <?php echo htmlspecialchars($empleado['codigo']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-center gap-md">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr(($empleado['nombre_completo'] ?? ''), 0, 1) . substr(($empleado['apellido_completo'] ?? ''), 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="text-primary">
                                        <?php echo htmlspecialchars(trim(($empleado['nombre_completo'] ?? '') . ' ' . ($empleado['apellido_completo'] ?? ''))); ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        <?php echo htmlspecialchars($empleado['cedula']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                        <td>
                            <span class="badge badge-secondary">
                                <?php echo htmlspecialchars($empleado['departamento']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($empleado['cargo']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($empleado['fecha_ingreso'])); ?></td>
                        <td>
                            <span class="badge badge-success">
                                <?php echo ucfirst($empleado['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-sm">
                                <button class="btn btn-sm btn-secondary" onclick="verEmpleado(<?php echo $empleado['id']; ?>)" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="editarEmpleado(<?php echo $empleado['id']; ?>)" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if (SessionManager::tienePermiso('administrador')): ?>
                                <button class="btn btn-sm btn-danger" onclick="eliminarEmpleado(<?php echo $empleado['id']; ?>)" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($empleados)): ?>
            <div class="text-center p-3 text-muted">No hay empleados registrados en el sistema.</div>
            <?php endif; ?>
        </div>
        
        <div class="employee-list d-block d-lg-none">
            <?php foreach ($empleados as $empleado): ?>
            <div class="employee-card">
                <div class="card-header-mobile">
                    <div class="user-avatar-mobile">
                        <?php echo strtoupper(substr(($empleado['nombre_completo'] ?? ''), 0, 1) . substr(($empleado['apellido_completo'] ?? ''), 0, 1)); ?>
                    </div>
                    <div class="employee-info">
                        <h4 class="employee-name"><?php echo htmlspecialchars(trim(($empleado['nombre_completo'] ?? '') . ' ' . ($empleado['apellido_completo'] ?? ''))); ?></h4>
                        <div class="employee-meta">
                            <span class="badge badge-info"><?php echo htmlspecialchars($empleado['codigo']); ?></span>
                            <span class="badge badge-secondary"><?php echo htmlspecialchars($empleado['departamento']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-body-mobile">
                    <div class="details-item">
                        <strong>Cargo:</strong> <span><?php echo htmlspecialchars($empleado['cargo']); ?></span>
                    </div>
                    <div class="details-item">
                        <strong>Email:</strong> <span><?php echo htmlspecialchars($empleado['email']); ?></span>
                    </div>
                    <div class="details-item">
                        <strong>Teléfono:</strong> <span><?php echo htmlspecialchars($empleado['telefono']); ?></span>
                    </div>
                    <div class="details-item">
                        <strong>Ingreso:</strong> <span><?php echo date('d/m/Y', strtotime($empleado['fecha_ingreso'])); ?></span>
                    </div>
                    <div class="details-item">
                        <strong>Estado:</strong> <span class="badge badge-success"><?php echo ucfirst($empleado['estado']); ?></span>
                    </div>
                </div>
                <div class="card-footer-mobile">
                    <div class="d-flex justify-content-center gap-sm">
                        <button class="btn btn-sm btn-secondary" onclick="verEmpleado(<?php echo $empleado['id']; ?>)" title="Ver Detalles">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="editarEmpleado(<?php echo $empleado['id']; ?>)" title="Editar">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <?php if (SessionManager::tienePermiso('administrador')): ?>
                        <button class="btn btn-sm btn-danger" onclick="eliminarEmpleado(<?php echo $empleado['id']; ?>)" title="Eliminar">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
             <?php if (empty($empleados)): ?>
             <div class="text-center p-3 text-muted">No hay empleados registrados en el sistema.</div>
            <?php endif; ?>
        </div>
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
                    <button type="button" class="tab-button active" onclick="mostrarSeccionModal('personal')">INFORMACIÓN PERSONAL</button>
                    <button type="button" class="tab-button" onclick="mostrarSeccionModal('laboral')">INFORMACIÓN LABORAL</button>
                    <button type="button" class="tab-button" onclick="mostrarSeccionModal('pagos')">INFORMACIÓN PAGOS</button>
                    <button type="button" class="tab-button" onclick="mostrarSeccionModal('aportes')">INFORMACIÓN APORTES</button>
                    <button type="button" class="tab-button" onclick="mostrarSeccionModal('cursos')">VIGENCIA DE CURSOS</button>
                </div>

                <div id="personal" class="tab-content-modal active grid-2" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    
                    <div class="form-group">
                        <label for="tipo_contrato">CONTRATO *</label>
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
                        <label for="cedula">CÉDULA (DOCUMENTO DE IDENTIDAD) *</label>
                        <input type="text" id="cedula" name="cedula" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="nombre_completo">NOMBRE *</label>
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
                        <label for="email">CORREO (CORREO ELECTRÓNICO) *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">CELULAR</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control">
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
                        <label for="cargo">CARGO *</label>
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
                        <label for="departamento">DEPARTAMENTO *</label>
                        <select id="departamento" name="departamento" class="form-control" required>
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getDepartamentoOptions()); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="municipio">MUNICIPIO *</label>
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
                    <div class="form-group" style="display: none;">
                        <label for="smlv">SMLV BASE</label>
                        <select id="smlv" name="smlv" class="form-control">
                            <?php echo generar_opciones(getSmlvOptions()); ?>
                        </select>
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
    function mostrarSeccionModal(seccionId) {
        // Oculta todas las secciones
        document.querySelectorAll('.tab-content-modal').forEach(content => {
            content.style.display = 'none';
            content.classList.remove('active');
        });
        // Desactiva todos los botones
        document.querySelectorAll('.tab-buttons-modal .tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Muestra la sección seleccionada y la activa (comprobación defensiva)
        const selSection = document.getElementById(seccionId);
        if (selSection) {
            selSection.style.display = 'grid';
            selSection.classList.add('active');
        }

        // Activa el botón de la pestaña correspondiente (buscar en .tab-header o en cualquier .tab-button)
        let tabBtn = document.querySelector(`.tab-header button[onclick="mostrarSeccionModal('${seccionId}')"]`);
        if (!tabBtn) {
            tabBtn = document.querySelector(`.tab-button[onclick="mostrarSeccionModal('${seccionId}')"]`);
        }
        if (tabBtn && tabBtn.classList) {
            tabBtn.classList.add('active');
        }
    }
    
    // Inicializar el modal para mostrar la sección 'personal' al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('modal-empleado');
        if (modal) {
             mostrarSeccionModal('personal'); // Mostrar 'INFORMACIÓN PERSONAL' al inicio
        }
    });

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
</script>


<?php require_once 'includes/footer.php'; ?>