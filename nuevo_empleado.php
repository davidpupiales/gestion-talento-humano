<?php
// Incluir archivos de configuración y seguridad
require_once 'config/session.php';
require_once 'config/database.php';

// Verificar si el usuario está autenticado y tiene permisos de administrador/gerente
if (!SessionManager::verificarSesion() || !SessionManager::tienePermiso('administrador')) {
    header('Location: login.php');
    exit();
}

$db = Database::getInstance();
$mensaje = '';
$error = '';

// Definición de las listas desplegables (SELECTs)
$opciones_select = [
    'tipo_contrato' => ['OPS', 'LAB'],
    'estado' => ['ACTIVO', 'ENVIADO PARA FIRMA', 'TERMINADO', 'PROYECTAR CONTRATO', 'ACTIVO SIN FIRMA', 'LIQUIDADO'],
    'poliza' => ['TIENE', 'NO TIENE', 'NO APLICA'], // Asumiendo que este campo se añade a la tabla
    'municipio' => ['PASTO', 'IPIALES', 'ALBÁN', 'ALDANA', /* ... todos los demás municipios ... */ 'YACUANQUER', 'ALTO PUTUMAYO'],
    'genero' => ['MASCULINO', 'FEMENINO'],
    'grupo_sanguineo' => ['A+', 'A−', 'B+', 'B−', 'AB+', 'AB−', 'O+', 'O−'],
    'nivel' => ['OPERATIVO ESPECIALIZADO', 'SOPORTE', 'COORDINADOR/MANDO MEDIO', 'OPERATIVO TÉCNICO/ADMINISTRATIVO', 'DIRECTIVO'],
    'calidad' => ['ASISTENCIAL (EN SALUD)', 'ADMINISTRATIVO (EN SALUD)'],
    'area' => ['PROGRAMA ARTRITIS', 'PROGRAMA B24X', 'HOME CARE', /* ... todas las demás áreas ... */ 'ADMINISTRATIVO', 'POST-CONSULTA'],
    'nivel_riesgo' => ['I', 'II', 'III', 'IV', 'V'],
    'programa' => ['PROGRAMA ARTRITIS', 'PROGRAMA B24X', 'HOME CARE', 'OPTOMETRÍA', 'PALIATIVOS', 'PROGRAMA EPOC', 'PROGRAMA NEFROPROTECCIÓN', 'LABORATORIO', 'ADMINISTRATIVO'],
    'servicio' => ['ATENCIÓN A PACIENTE', 'HORA DE SERVICIO', 'NO APLICA'],
    'cargo' => ['FISIOTERAPEUTA', 'TERAPEUTA OCUPACIONAL', 'FONOAUDIOLOGO/A', /* ... todos los demás cargos ... */ 'BACTERIOLOGO', 'APRENDIZ SENA', 'PRACTICANTE UNIVERSITARIO'],
    'departamento' => ['NARIÑO', 'CAUCA', 'VALLE'],
    // Agregamos SMLV para el cálculo de auxilio de transporte
    'smlv' => ['1423500']
];

// Lógica de procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitización y Validación de Datos (CRÍTICO para seguridad)
    $datos_limpios = [];
    $errores_validacion = [];
    
    // Ejemplo de sanitización simple (debe ser más robusta)
    foreach ($_POST as $clave => $valor) {
        $datos_limpios[$clave] = htmlspecialchars(trim($valor));
    }

    // 2. Mapear datos a la estructura de la DB (se usa el mismo nombre para simplificar)
    // El método crearNuevoPersonal requiere de un array con todas las claves.
    
    if (empty($errores_validacion)) {
        // Asignar valores por defecto para evitar errores en el INSERT de muchos campos
        // Idealmente, se debería validar que los campos requeridos no estén vacíos.
        
        $insert_id = $db->crearNuevoPersonal($datos_limpios);

        if ($insert_id) {
            $mensaje = "✅ Empleado '{$datos_limpios['nombre_completo']}' creado exitosamente con ID: {$insert_id}.";
            // Redirigir para evitar reenvío del formulario
            // header('Location: empleados.php?success=' . urlencode($mensaje));
            // exit();
        } else {
            $error = "❌ Error al crear el empleado. Consulte el log de la base de datos.";
        }
    } else {
        $error = "❌ Por favor, corrija los siguientes errores: " . implode(', ', $errores_validacion);
    }
}

// Función para generar las opciones de un select
function generar_opciones($opciones, $seleccionado = '') {
    $html = '';
    foreach ($opciones as $opcion) {
        $opcion_limpia = htmlspecialchars($opcion);
        $selected = ($opcion_limpia === $seleccionado) ? 'selected' : '';
        $html .= "<option value=\"{$opcion_limpia}\" {$selected}>{$opcion_limpia}</option>";
    }
    return $html;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Empleado - HRMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS BÁSICO PARA EL FORMULARIO DE SECCIONES */
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ccc;
        }
        .tab-button {
            padding: 10px 15px;
            border: none;
            background-color: #f0f0f0;
            cursor: pointer;
            border-radius: 5px 5px 0 0;
            transition: background-color 0.3s;
            font-weight: bold;
        }
        .tab-button.active {
            background-color: #007bff;
            color: white;
            border-bottom: 2px solid #007bff;
        }
        .tab-content {
            display: none;
            padding: 20px 0;
        }
        .tab-content.active {
            display: block;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .submit-button-container {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👤 Nuevo Registro de Personal</h1>
        <?php if ($mensaje): ?><div class="alert success"><?php echo $mensaje; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?php echo $error; ?></div><?php endif; ?>

        <form method="POST" action="nuevo_empleado.php">
            
            <div class="tab-buttons">
                <button type="button" class="tab-button active" onclick="mostrarSeccion('personal')">INFORMACIÓN PERSONAL</button>
                <button type="button" class="tab-button" onclick="mostrarSeccion('laboral')">INFORMACIÓN LABORAL</button>
                <button type="button" class="tab-button" onclick="mostrarSeccion('pagos')">INFORMACIÓN PAGOS</button>
                <button type="button" class="tab-button" onclick="mostrarSeccion('aportes')">INFORMACIÓN APORTES</button>
                <button type="button" class="tab-button" onclick="mostrarSeccion('cursos')">VIGENCIA DE CURSOS</button>
            </div>

            <div id="personal" class="tab-content active">
                <h2>Información Personal Básica</h2>
                <div class="form-grid">
                    <?php echo $db->getConnection()->error; // Muestra error de la última consulta ?>
                    <div class="form-group">
                        <label for="tipo_contrato">Contrato *</label>
                        <select id="tipo_contrato" name="tipo_contrato" required>
                            <?php echo generar_opciones($opciones_select['tipo_contrato']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" value="" placeholder="Se autogenerará si está vacío">
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado *</label>
                        <select id="estado" name="estado" required>
                            <?php echo generar_opciones($opciones_select['estado']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cedula">Cédula (Documento de Identidad) *</label>
                        <input type="text" id="cedula" name="cedula" required>
                    </div>
                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo *</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion">
                    </div>
                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Celular</label>
                        <input type="tel" id="telefono" name="telefono">
                    </div>
                    <div class="form-group">
                        <label for="grupo_sanguineo">Grupo Sanguíneo (RH) *</label>
                        <select id="grupo_sanguineo" name="grupo_sanguineo" required>
                            <?php echo generar_opciones($opciones_select['grupo_sanguineo']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="genero">Género *</label>
                        <select id="genero" name="genero" required>
                            <?php echo generar_opciones($opciones_select['genero']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha_ingreso">Fecha Ingreso</label>
                        <input type="date" id="fecha_ingreso" name="fecha_ingreso">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin_zo_ingreso">Fecha Fin Zona Ocupacional Ingreso</label>
                        <input type="date" id="fecha_fin_zo_ingreso" name="fecha_fin_zo_ingreso">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin_zo_egreso">Fecha Fin Zona Ocupacional Egreso</label>
                        <input type="date" id="fecha_fin_zo_egreso" name="fecha_fin_zo_egreso">
                    </div>
                    <div class="form-group">
                        <label for="contacto_emergencia">Contacto Emergencia</label>
                        <input type="text" id="contacto_emergencia" name="contacto_emergencia" placeholder="Nombre y Teléfono">
                    </div>
                    <div class="form-group">
                        <label for="poliza">Póliza</label>
                        <select id="poliza" name="poliza">
                            <?php echo generar_opciones($opciones_select['poliza']); ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="laboral" class="tab-content">
                <h2>Información de Contratación y Ubicación</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="sede">Sede</label>
                        <input type="text" id="sede" name="sede">
                    </div>
                    <div class="form-group">
                        <label for="cargo">Cargo *</label>
                        <select id="cargo" name="cargo" required>
                            <?php echo generar_opciones($opciones_select['cargo']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nivel">Nivel</label>
                        <select id="nivel" name="nivel">
                            <?php echo generar_opciones($opciones_select['nivel']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="calidad">Calidad</label>
                        <select id="calidad" name="calidad">
                            <?php echo generar_opciones($opciones_select['calidad']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="programa">Programa</label>
                        <select id="programa" name="programa">
                            <?php echo generar_opciones($opciones_select['programa']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="area">Área</label>
                        <select id="area" name="area">
                            <?php echo generar_opciones($opciones_select['area']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="intramural">Intramural</label>
                        <input type="text" id="intramural" name="intramural">
                    </div>
                    <div class="form-group">
                        <label for="departamento">Departamento *</label>
                        <select id="departamento" name="departamento" required>
                            <?php echo generar_opciones($opciones_select['departamento']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="municipio">Municipio *</label>
                        <select id="municipio" name="municipio" required>
                            <?php echo generar_opciones($opciones_select['municipio']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="servicio">Servicio</label>
                        <select id="servicio" name="servicio">
                            <?php echo generar_opciones($opciones_select['servicio']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio (Contrato)</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin (Contrato)</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" onchange="calcularDias()">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin_contrato">Fecha Fin de Contrato</label>
                        <input type="date" id="fecha_fin_contrato" name="fecha_fin_contrato">
                    </div>
                    <div class="form-group">
                        <label for="nivel_riesgo">Nivel de Riesgo</label>
                        <select id="nivel_riesgo" name="nivel_riesgo">
                            <?php echo generar_opciones($opciones_select['nivel_riesgo']); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="eps">EPS</label>
                        <input type="text" id="eps" name="eps">
                    </div>
                    <div class="form-group">
                        <label for="arl">ARL</label>
                        <input type="text" id="arl" name="arl">
                    </div>
                    <div class="form-group">
                        <label for="afp">AFP</label>
                        <input type="text" id="afp" name="afp">
                    </div>
                    <div class="form-group">
                        <label for="fecha_vencimiento_registro">Fecha Vencimiento de Registro</label>
                        <input type="date" id="fecha_vencimiento_registro" name="fecha_vencimiento_registro">
                    </div>
                    <div class="form-group">
                        <label for="dias_trabajados">Días Trabajados (Auto)</label>
                        <input type="number" id="dias_trabajados" name="dias_trabajados" readonly placeholder="Calculado por DB">
                    </div>
                    <div class="form-group">
                        <label for="smlv">SMLV</label>
                        <select id="smlv" name="smlv">
                            <?php echo generar_opciones($opciones_select['smlv']); ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="pagos" class="tab-content">
                <h2>Información de Remuneración y Cuenta</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="valor_por_evento">Valor Por Evento</label>
                        <input type="number" step="0.01" id="valor_por_evento" name="valor_por_evento">
                    </div>
                    <div class="form-group">
                        <label for="mesada">Mesada</label>
                        <input type="number" step="0.01" id="mesada" name="mesada">
                    </div>
                    <div class="form-group">
                        <label for="pres_mensual">PRES MENSUAL (Auto)</label>
                        <input type="number" step="0.01" id="pres_mensual" name="pres_mensual" readonly placeholder="Calculado por DB (Trigger/View)">
                    </div>
                    <div class="form-group">
                        <label for="pres_anual">PRES ANUAL (Auto)</label>
                        <input type="number" step="0.01" id="pres_anual" name="pres_anual" readonly placeholder="Calculado por DB (Trigger/View)">
                    </div>
                    <div class="form-group">
                        <label for="extras_legales">Extras Legales</label>
                        <input type="number" step="0.01" id="extras_legales" name="extras_legales">
                    </div>
                    <div class="form-group">
                        <label for="aux_transporte">Aux. Transporte (Auto)</label>
                        <input type="number" step="0.01" id="aux_transporte" name="aux_transporte" readonly placeholder="Calculado por DB">
                    </div>
                    <div class="form-group">
                        <label for="num_cuenta">Num. Cuenta</label>
                        <input type="text" id="num_cuenta" name="num_cuenta">
                    </div>
                    <div class="form-group">
                        <label for="entidad_bancaria">Entidad Bancaria</label>
                        <input type="text" id="entidad_bancaria" name="entidad_bancaria">
                    </div>
                </div>
            </div>

            <div id="aportes" class="tab-content">
                <h2>Aportes de Seguridad Social y Parafiscales</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="tasa_arl">Tasa ARL</label>
                        <input type="number" step="0.0001" id="tasa_arl" name="tasa_arl" readonly placeholder="Calculado por DB (Trigger/View)">
                    </div>
                    <div class="form-group">
                        <label for="ap_salud_mes">AP. Salud Mes</label>
                        <input type="number" step="0.01" id="ap_salud_mes" name="ap_salud_mes">
                    </div>
                    <div class="form-group">
                        <label for="ap_pension_mes">AP. Pensión Mes</label>
                        <input type="number" step="0.01" id="ap_pension_mes" name="ap_pension_mes">
                    </div>
                    <div class="form-group">
                        <label for="ap_arl_mes_ap_caja_mes">AP. ARL Mes AP. Caja Mes</label>
                        <input type="number" step="0.01" id="ap_arl_mes_ap_caja_mes" name="ap_arl_mes_ap_caja_mes">
                    </div>
                    <div class="form-group">
                        <label for="ap_sena_mes">AP. SENA Mes</label>
                        <input type="number" step="0.01" id="ap_sena_mes" name="ap_sena_mes">
                    </div>
                    <div class="form-group">
                        <label for="ap_icbf_mes">AP. ICBF Mes</label>
                        <input type="number" step="0.01" id="ap_icbf_mes" name="ap_icbf_mes">
                    </div>
                    <div class="form-group">
                        <label for="ap_cesantia_anual">AP. Cesantía Anual</label>
                        <input type="number" step="0.01" id="ap_cesantia_anual" name="ap_cesantia_anual">
                    </div>
                    <div class="form-group">
                        <label for="ap_interes_cesantias_anual">AP. Interés Cesantías Anual</label>
                        <input type="number" step="0.01" id="ap_interes_cesantias_anual" name="ap_interes_cesantias_anual">
                    </div>
                    <div class="form-group">
                        <label for="ap_prima_anual">AP. Prima Anual</label>
                        <input type="number" step="0.01" id="ap_prima_anual" name="ap_prima_anual">
                    </div>
                </div>
            </div>

            <div id="cursos" class="tab-content">
                <h2>Vigencia de Certificaciones Obligatorias</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="vigencia_soporte_vital_avanzado">Soporte Vital Avanzado</label>
                        <input type="date" id="vigencia_soporte_vital_avanzado" name="vigencia_soporte_vital_avanzado">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_victimas_violencia_sexual">Atención Víctimas de Violencia Sexual</label>
                        <input type="date" id="vigencia_victimas_violencia_sexual" name="vigencia_victimas_violencia_sexual">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_soporte_vital_basico">Curso Soporte Vital Básico</label>
                        <input type="date" id="vigencia_soporte_vital_basico" name="vigencia_soporte_vital_basico">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_manejo_dolor_cuidados_paliativos">Curso Manejo del Dolor y Cuidados Paliativos</label>
                        <input type="date" id="vigencia_manejo_dolor_cuidados_paliativos" name="vigencia_manejo_dolor_cuidados_paliativos">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_humanizacion_toma_muestras">Curso Humanización Toma de Muestras</label>
                        <input type="date" id="vigencia_humanizacion_toma_muestras" name="vigencia_humanizacion_toma_muestras">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_manejo_duelo">Manejo del Duelo</label>
                        <input type="date" id="vigencia_manejo_duelo" name="vigencia_manejo_duelo">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_manejo_residuos">Manejo Residuos</label>
                        <input type="date" id="vigencia_manejo_residuos" name="vigencia_manejo_residuos">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_seguridad_vial">Seguridad Vial</label>
                        <input type="date" id="vigencia_seguridad_vial" name="vigencia_seguridad_vial">
                    </div>
                    <div class="form-group">
                        <label for="vigencia_vigiflow">Vigiflow</label>
                        <input type="date" id="vigencia_vigiflow" name="vigencia_vigiflow">
                    </div>
                </div>
            </div>

            <div class="submit-button-container">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Nuevo Empleado</button>
            </div>
        </form>
    </div>

    <script>
        // Función de JavaScript para cambiar entre secciones (TABS)
        function mostrarSeccion(seccionId) {
            // Ocultar todos los contenidos
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            // Remover 'active' de todos los botones
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });

            // Mostrar la sección seleccionada
            document.getElementById(seccionId).classList.add('active');
            // Activar el botón correspondiente
            document.querySelector(`.tab-buttons button[onclick="mostrarSeccion('${seccionId}')"]`).classList.add('active');
        }

        // Mostrar la primera sección al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            mostrarSeccion('personal');
        });
        
        // Función opcional para el cálculo de Días Trabajados en el frontend (aunque el backend lo hace)
        function calcularDias() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            
            if (fechaInicio && fechaFin) {
                const date1 = new Date(fechaInicio);
                const date2 = new Date(fechaFin);
                
                // Cálculo de diferencia en días
                const diffTime = Math.abs(date2 - date1);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir el día de inicio
                
                document.getElementById('dias_trabajados').value = diffDays;
            } else {
                document.getElementById('dias_trabajados').value = 0;
            }
        }
    </script>
</body>
</html>