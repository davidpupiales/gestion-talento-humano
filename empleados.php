<?php
require_once 'includes/header.php';
require_once 'functions/utils.php';
// Verificar permisos
// ASUME QUE EXISTE SessionManager en algun 'require_once'
if (!SessionManager::tienePermiso('gerente')) {
    header('Location: dashboard.php');
    exit();
}

// =========================================================================
// INICIO: LÓGICA DE PROCESAMIENTO (MIGRADA DE nuevo_empleado.php)
// =========================================================================

/**
 * NOTA IMPORTANTE: La lógica de procesamiento de formulario y de DB
 * se ha movido aquí o debe moverse a un archivo de controlador.
 * De lo contrario, el formulario dejará de funcionar al eliminar nuevo_empleado.php.
 * * Aquí va el contenido del bloque 'if ($_SERVER['REQUEST_METHOD'] === 'POST')' 
 * de nuevo_empleado.php (una vez que la DB esté lista).
 * Temporalmente se deja como placeholder para mantener la estructura.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitización y Validación de Datos
    // $datos_limpios = limpiar_entrada($_POST); // Usando la función de utils.php
    
    // 2. Procesamiento (e.g., $db->crearNuevoPersonal($datos_limpios);)
    
    // header('Location: empleados.php?success=...');
    // exit();
}

// =========================================================================
// INICIO: Bloque de Conexión a Base de Datos (a ser implementado)
// =========================================================================

/**
 * En esta etapa, el desarrollador deberá:
 * 1. Incluir la conexión a la base de datos (e.g., 'includes/Db.php' o 'includes/db_connection.php').
 * 2. Implementar una función o lógica para consultar la tabla de empleados
 * y almacenar el resultado en la variable $empleados.
 *
 * Ejemplo de lo que se podría reemplazar:
 * require_once 'includes/Db.php';
 * $db = new Db();
 * $empleados = $db->query("SELECT * FROM empleados WHERE estado != 'eliminado' ORDER BY apellido, nombre");
 */

// *************************************************************************
// NOTA: Temporalmente, para evitar errores mientras se implementa la DB,
// se inicializa la variable como un array vacío. Se quita la inicialización
// de SMLV del array eliminado.
// *************************************************************************
$empleados = []; 

// =========================================================================
// FIN: Bloque de Conexión a Base de Datos
// =========================================================================

// -------------------------------------------------------------------------
// Secciones del HTML se modifican para usar las funciones de utils.php
// -------------------------------------------------------------------------

// ... [Resto del código HTML] ...

?>
<link rel="stylesheet" href="assets/css/empleados.css">

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
                                    <?php echo strtoupper(substr($empleado['nombre'], 0, 1) . substr($empleado['apellido'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="text-primary">
                                        <?php echo htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']); ?>
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
                        <?php echo strtoupper(substr($empleado['nombre'], 0, 1) . substr($empleado['apellido'], 0, 1)); ?>
                    </div>
                    <div class="employee-info">
                        <h4 class="employee-name"><?php echo htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido']); ?></h4>
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
                        <label for="grupo_sanguineo">GRUPO SANGUÍNEO (RH) *</label>
                        <select id="grupo_sanguineo" name="grupo_sanguineo" class="form-control" required>
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getGrupoSanguineoOptions()); ?>
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
                        <label for="fecha_ingreso">FECHA INGRESO</label>
                        <input type="date" id="fecha_ingreso" name="fecha_ingreso" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin_zo_ingreso">FECHA FIN ZONA OCUPACIONAL INGRESO</label>
                        <input type="date" id="fecha_fin_zo_ingreso" name="fecha_fin_zo_ingreso" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin_zo_egreso">FECHA FIN ZONA OCUPACIONAL EGRESO</label>
                        <input type="date" id="fecha_fin_zo_egreso" name="fecha_fin_zo_egreso" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="contacto_emergencia">CONTACTO EMERGENCIA</label>
                        <input type="text" id="contacto_emergencia" name="contacto_emergencia" class="form-control" placeholder="Nombre y Teléfono">
                    </div>
                    <div class="form-group">
                        <label for="poliza">PÓLIZA</label>
                        <select id="poliza" name="poliza" class="form-control">
                            <option value="" disabled selected hidden>Seleccione...</option>
                            <?php echo generar_opciones(getPolizaOptions()); ?>
                        </select>
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
                    <div class="form-group">
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
                        <label for="aux_transporte">AUX. TRANSPORTE (Auto)</label>
                        <input type="number" step="0.01" id="aux_transporte" name="aux_transporte" class="form-control" readonly placeholder="Calculado por DB">
                    </div>
                    <div class="form-group">
                        <label for="num_cuenta">NUM. CUENTA</label>
                        <input type="text" id="num_cuenta" name="num_cuenta" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="entidad_bancaria">ENTIDAD BANCARIA</label>
                        <input type="text" id="entidad_bancaria" name="entidad_bancaria" class="form-control">
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
                        <label for="ap_arl_mes_ap_caja_mes">AP. ARL/CAJA MES</label>
                        <input type="number" step="0.01" id="ap_arl_mes_ap_caja_mes" name="ap_arl_mes_ap_caja_mes" class="form-control">
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
                        <label for="vigencia_humanizacion_toma_muestras">HUMANIZACIÓN TOMA DE MUESTRAS (Vigencia)</label>
                        <input type="date" id="vigencia_humanizacion_toma_muestras" name="vigencia_humanizacion_toma_muestras" class="form-control">
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

        // Muestra la sección seleccionada y la activa
        document.getElementById(seccionId).style.display = 'grid'; 
        document.getElementById(seccionId).classList.add('active');
        
        // Activa el botón de la pestaña correspondiente
        document.querySelector(`.tab-buttons-modal button[onclick="mostrarSeccionModal('${seccionId}')"]`).classList.add('active');
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