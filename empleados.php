<?php
require_once 'includes/header.php';
require_once 'functions/utils.php';
// Verificar permisos
if (!SessionManager::tienePermiso('gerente')) {
    header('Location: dashboard.php');
    exit();
}

// =========================================================================
// INICIO: Bloque de Conexión a Base de Datos (a ser implementado)
// =========================================================================

/**
 * En esta etapa, el desarrollador deberá:
 * 1. Incluir la conexión a la base de datos (e.g., 'includes/db_connection.php').
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
// se inicializa la variable como un array vacío.
// *************************************************************************
$empleados = []; 

// =========================================================================
// FIN: Bloque de Conexión a Base de Datos
// =========================================================================

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


    




<!-- formulario de nuevo usuario -->
<div id="modal-empleado" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-titulo">Nuevo Empleado</h3>
            <button class="modal-close" onclick="cerrarModalEmpleado()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
                <form id="form-nuevo-empleado" method="POST" action="functions/empleados.php">
            
                <div class="tab-header mb-lg">
                    <button type="button" class="tab-button active" data-tab="laboral">INFORMACIÓN LABORAL</button>
                    <button type="button" class="tab-button" data-tab="pagos">INFORMACIÓN PAGOS</button>
                    <button type="button" class="tab-button" data-tab="aportes">INFORMACIÓN APORTES</button>
                    <button type="button" class="tab-button" data-tab="cursos">VIGENCIA DE CURSOS</button>
                </div>

            <div id="tab-laboral" class="tab-content active grid-3">
                <div class="form-group">
                    <label>SEDE</label>
                    <input type="text" name="sede" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>CARGO</label>
                    <select name="cargo" class="form-control" required>
                        <option value="">Seleccione un cargo...</option>
                        <?php foreach (getCargoOptions() as $c): ?>
                            <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>NIVEL</label>
                    <select name="nivel" class="form-control">
                        <option value="">Seleccione un nivel...</option>
                        <?php foreach (getNivelOptions() as $n): ?>
                            <option value="<?php echo htmlspecialchars($n); ?>"><?php echo htmlspecialchars($n); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>CALIDAD</label>
                    <select name="calidad" class="form-control">
                        <option value="">Seleccione la calidad...</option>
                        <?php foreach (getCalidadOptions() as $c): ?>
                            <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>PROGRAMA</label>
                    <select name="programa" class="form-control">
                        <option value="">Seleccione un programa...</option>
                        <?php foreach (getProgramaOptions() as $p): ?>
                            <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>ÁREA</label>
                    <select name="area" class="form-control">
                        <option value="">Seleccione un área...</option>
                        <?php foreach (getAreaOptions() as $a): ?>
                            <option value="<?php echo htmlspecialchars($a); ?>"><?php echo htmlspecialchars($a); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>INTRAMURAL</label>
                    <input type="text" name="intramural" class="form-control">
                </div>
                <div class="form-group">
                    <label>DEPARTAMENTO</label>
                    <select name="departamento" class="form-control">
                        <option value="">Seleccione un departamento...</option>
                        <?php foreach (getDepartamentoOptions() as $d): ?>
                            <option value="<?php echo htmlspecialchars($d); ?>"><?php echo htmlspecialchars($d); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>MUNICIPIO</label>
                    <select name="municipio" class="form-control">
                        <option value="">Seleccione un municipio...</option>
                        <?php foreach (getMunicipioOptions() as $m): ?>
                            <option value="<?php echo htmlspecialchars($m); ?>"><?php echo htmlspecialchars($m); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>SERVICIO</label>
                    <select name="servicio" class="form-control">
                        <option value="">Seleccione un servicio...</option>
                        <?php foreach (getServicioOptions() as $s): ?>
                            <option value="<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>FECHA DE INICIO</label>
                    <input type="date" name="fecha_inicio" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>FECHA FIN</label>
                    <input type="date" name="fecha_fin" class="form-control">
                </div>
                <div class="form-group">
                    <label>FECHA FIN DE CONTRATO</label>
                    <input type="date" name="fecha_fin_contrato" class="form-control">
                </div>
                <div class="form-group">
                    <label>NIVEL DE RIESGO</label>
                    <select name="nivel_riesgo" class="form-control">
                        <option value="">Seleccione el nivel de riesgo...</option>
                        <?php foreach (getNivelRiesgoOptions() as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>EPS</label>
                    <input type="text" name="eps" class="form-control">
                </div>
                <div class="form-group">
                    <label>ARL</label>
                    <input type="text" name="arl" class="form-control">
                </div>
                <div class="form-group">
                    <label>AFP</label>
                    <input type="text" name="afp" class="form-control">
                </div>
                <div class="form-group">
                    <label>FECHA VENCIMIENTO DE REGISTRO</label>
                    <input type="date" name="fecha_vencimiento_registro" class="form-control">
                </div>
                <div class="form-group">
                    <label>DÍAS TRABAJADOS (Cálculo Automático)</label>
                    <input type="text" name="dias_trabajados" class="form-control" readonly value="0">
                </div>
            </div>

            <div id="tab-pagos" class="tab-content grid-2" style="display: none;">
                <div class="form-group">
                    <label>VALOR POR EVENTO</label>
                    <input type="number" name="valor_evento" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>MESADA</label>
                    <input type="number" name="mesada" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>PRES MENSUAL (Cálculo Automático)</label>
                    <input type="number" name="pres_mensual" class="form-control" readonly value="0">
                </div>
                <div class="form-group">
                    <label>PRES ANUAL (Cálculo Automático)</label>
                    <input type="number" name="pres_anual" class="form-control" readonly value="0">
                </div>
                <div class="form-group">
                    <label>EXTRAS LEGALES</label>
                    <input type="number" name="extras_legales" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>AUX. TRANSPORTE (Cálculo Automático)</label>
                    <input type="number" name="aux_transporte" class="form-control" readonly value="0">
                </div>
                <div class="form-group">
                    <label>NUM. CUENTA</label>
                    <input type="text" name="num_cuenta" class="form-control">
                </div>
                <div class="form-group">
                    <label>ENTIDAD BANCARIA</label>
                    <input type="text" name="entidad_bancaria" class="form-control">
                </div>
            </div>

            <div id="tab-aportes" class="tab-content grid-3" style="display: none;">
                <div class="form-group">
                    <label>TASA ARL (Cálculo Automático)</label>
                    <input type="text" name="tasa_arl" class="form-control" readonly value="Cálculo Pendiente">
                </div>
                <div class="form-group">
                    <label>AP. SALUD MES</label>
                    <input type="number" name="ap_salud_mes" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>AP. PENSIÓN MES</label>
                    <input type="number" name="ap_pension_mes" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>AP. ARL MES</label>
                    <input type="number" name="ap_arl_mes" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>AP. CAJA MES</label>
                    <input type="number" name="ap_caja_mes" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>AP. SENA MES</label>
                    <input type="number" name="ap_sena_mes" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>AP. ICBF MES</label>
                    <input type="number" name="ap_icbf_mes" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>AP. CESANTÍA ANUAL</label>
                    <input type="number" name="ap_cesantia_anual" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>AP. INTERÉS CESANTÍAS ANUAL</label>
                    <input type="number" name="ap_interes_cesantias_anual" class="form-control" step="0.01">
                </div>
                <div class="form-group">
                    <label>AP. PRIMA ANUAL</label>
                    <input type="number" name="ap_prima_anual" class="form-control" step="0.01">
                </div>
            </div>

            <div id="tab-cursos" class="tab-content grid-3" style="display: none;">
                <div class="form-group">
                    <label>SOPORTE VITAL AVANZADO (Vigencia)</label>
                    <input type="date" name="vigencia_sva" class="form-control">
                </div>
                <div class="form-group">
                    <label>ATENCIÓN VÍCTIMAS DE VIOLENCIA SEXUAL (Vigencia)</label>
                    <input type="date" name="vigencia_violencia_sexual" class="form-control">
                </div>
                <div class="form-group">
                    <label>CURSO SOPORTE VITAL BÁSICO (Vigencia)</label>
                    <input type="date" name="vigencia_svb" class="form-control">
                </div>
                <div class="form-group">
                    <label>CURSO MANEJO DEL DOLOR Y CUIDADOS PALIATIVOS (Vigencia)</label>
                    <input type="date" name="vigencia_paliativos" class="form-control">
                </div>
                <div class="form-group">
                    <label>CURSO HUMANIZACIÓN TOMA DE MUESTRAS DE LABORATORIO (Vigencia)</label>
                    <input type="date" name="vigencia_humanizacion_muestras" class="form-control">
                </div>
                <div class="form-group">
                    <label>MANEJO DEL DUELO (Vigencia)</label>
                    <input type="date" name="vigencia_duelo" class="form-control">
                </div>
                <div class="form-group">
                    <label>MANEJO RESIDUOS (Vigencia)</label>
                    <input type="date" name="vigencia_residuos" class="form-control">
                </div>
                <div class="form-group">
                    <label>SEGURIDAD VIAL (Vigencia)</label>
                    <input type="date" name="vigencia_seguridad_vial" class="form-control">
                </div>
                <div class="form-group">
                    <label>VIGIFLOW (Vigencia)</label>
                    <input type="date" name="vigencia_vigiflow" class="form-control">
                </div>
            </div>
        </form>

            
        </div>



        
    
<!-- fin del formulario -->
       



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

<script src="assets/js/empleados.js"></script>

<?php require_once 'includes/footer.php'; ?>