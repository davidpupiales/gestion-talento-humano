<?php
require_once 'includes/header.php';
require_once 'functions/nomina.php';

// Verificar permisos
if (!SessionManager::tienePermiso('empleado')) {
    header('Location: dashboard.php');
    exit();
}

// Obtener datos mock de nómina
$nominas = NominaManager::obtenerNominas();
$empleados = Database::getMockEmpleados();
$periodo_actual = date('Y-m');
?>

<div class="page-content fade-in">
     Reemplazando sistema de botones por pestañas profesionales 
     Navegación por pestañas mejorada 
    <div class="tab-navigation">
        <button class="tab-button active" onclick="cambiarPestana('consulta', this)">
            <i class="fas fa-search"></i> Consulta de Nómina
        </button>
        <button class="tab-button" onclick="cambiarPestana('calculo', this)">
            <i class="fas fa-calculator"></i> Cálculo de Nómina
        </button>
        <button class="tab-button" onclick="cambiarPestana('reportes', this)">
            <i class="fas fa-chart-line"></i> Reportes
        </button>
        <?php if (SessionManager::tienePermiso('gerente')): ?>
        <button class="tab-button" onclick="cambiarPestana('configuracion', this)">
            <i class="fas fa-cog"></i> Configuración
        </button>
        <?php endif; ?>
    </div>

     Pestaña: Consulta de Nómina 
    <div id="tab-consulta" class="tab-content active">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-search"></i>
                    Consulta de Nómina
                </h3>
                <div class="card-subtitle">
                    Consulta y descarga tus recibos de pago
                </div>
            </div>
            <div class="card-body">
                 Filtros 
                <div class="grid grid-3 mb-lg">
                    <div class="form-group">
                        <label class="form-label">Empleado</label>
                        <select class="form-control" id="filtro-empleado">
                            <option value="">Todos los empleados</option>
                            <?php foreach ($empleados as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>">
                                <?php echo htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Período</label>
                        <input type="month" class="form-control" id="filtro-periodo" value="<?php echo $periodo_actual; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select class="form-control" id="filtro-estado">
                            <option value="">Todos</option>
                            <option value="pagado">Pagado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="procesando">Procesando</option>
                        </select>
                    </div>
                </div>

                 Tabla de nóminas 
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Período</th>
                                <th>Salario Base</th>
                                <th>Deducciones</th>
                                <th>Neto a Pagar</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nominas as $nomina): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-center gap-md">
                                        <div style="width: 32px; height: 32px; background: var(--accent-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: white;">
                                            <?php echo strtoupper(substr($nomina['empleado_nombre'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="text-primary"><?php echo htmlspecialchars($nomina['empleado_nombre']); ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($nomina['codigo_empleado']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('m/Y', strtotime($nomina['periodo'])); ?></td>
                                <td>$<?php echo number_format($nomina['salario_base'], 2); ?></td>
                                <td>$<?php echo number_format($nomina['total_deducciones'], 2); ?></td>
                                <td class="text-primary">$<?php echo number_format($nomina['neto_pagar'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $nomina['estado'] == 'pagado' ? 'success' : ($nomina['estado'] == 'pendiente' ? 'warning' : 'info'); ?>">
                                        <?php echo ucfirst($nomina['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-sm">
                                        <button class="btn btn-sm btn-secondary" onclick="verDetalleNomina(<?php echo $nomina['id']; ?>)" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="descargarRecibo(<?php echo $nomina['id']; ?>)" title="Descargar Recibo">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

     Pestaña: Cálculo de Nómina 
    <div id="tab-calculo" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calculator"></i>
                    Cálculo de Nómina
                </h3>
                <div class="card-subtitle">
                    Procesar y calcular nóminas del período actual
                </div>
            </div>
            <div class="card-body">
                 Información del período 
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-value">156</div>
                        <div class="metric-label">Empleados Activos</div>
                        <div class="metric-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="metric-card success">
                        <div class="metric-value">$2,450,000</div>
                        <div class="metric-label">Total Nómina</div>
                        <div class="metric-icon"><i class="fas fa-dollar-sign"></i></div>
                    </div>
                    <div class="metric-card warning">
                        <div class="metric-value">12</div>
                        <div class="metric-label">Pendientes</div>
                        <div class="metric-icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>

                 Configuración del cálculo 
                <div class="permission-form">
                    <div class="form-title">
                        <i class="fas fa-cog"></i>
                        Configuración del Cálculo
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Período a Procesar</label>
                            <input type="month" class="form-control" value="<?php echo $periodo_actual; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo de Nómina</label>
                            <select class="form-control">
                                <option value="quincenal">Quincenal</option>
                                <option value="mensual">Mensual</option>
                                <option value="extraordinaria">Extraordinaria</option>
                            </select>
                        </div>
                    </div>

                     Acciones de cálculo 
                    <div class="d-flex gap-3">
                        <button class="btn btn-primary" onclick="calcularNomina()">
                            <i class="fas fa-play"></i> Calcular Nómina
                        </button>
                        <button class="btn btn-secondary" onclick="previsualizarCalculo()">
                            <i class="fas fa-eye"></i> Previsualizar
                        </button>
                        <button class="btn btn-success" onclick="aprobarNomina()">
                            <i class="fas fa-check"></i> Aprobar y Procesar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

     Pestaña: Reportes 
    <div id="tab-reportes" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Reportes de Nómina
                </h3>
                <div class="card-subtitle">
                    Análisis y reportes detallados de nómina
                </div>
            </div>
            <div class="card-body">
                 Tipos de reportes 
                <div class="grid grid-2 mb-lg">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-bar" style="font-size: 2rem; color: var(--accent-primary); margin-bottom: 1rem;"></i>
                            <h4>Reporte Mensual</h4>
                            <p class="text-muted">Resumen completo del mes</p>
                            <button class="btn btn-primary">Generar</button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-file-excel" style="font-size: 2rem; color: var(--accent-green); margin-bottom: 1rem;"></i>
                            <h4>Exportar Excel</h4>
                            <p class="text-muted">Datos detallados en Excel</p>
                            <button class="btn btn-success">Exportar</button>
                        </div>
                    </div>
                </div>

                 Gráfico de tendencias 
                <div class="card">
                    <div class="card-header">
                        <h4>Tendencia de Nómina - Últimos 6 Meses</h4>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px; background: var(--bg-secondary); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                            <div class="text-center">
                                <i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p>Gráfico de tendencias de nómina</p>
                                <small>Función pendiente de implementación</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     Pestaña: Configuración 
    <?php if (SessionManager::tienePermiso('gerente')): ?>
    <div id="tab-configuracion" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog"></i>
                    Configuración de Nómina
                </h3>
                <div class="card-subtitle">
                    Configurar parámetros y conceptos de nómina
                </div>
            </div>
            <div class="card-body">
                 Conceptos de nómina 
                <div class="grid grid-2">
                    <div class="card">
                        <div class="card-header">
                            <h4>Conceptos de Pago</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Salario Base</label>
                                <div class="d-flex align-center gap-md">
                                    <input type="checkbox" checked disabled>
                                    <span>Activo por defecto</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Horas Extra</label>
                                <div class="d-flex align-center gap-md">
                                    <input type="checkbox" checked>
                                    <span>Multiplicador: 1.5x</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Bonificaciones</label>
                                <div class="d-flex align-center gap-md">
                                    <input type="checkbox" checked>
                                    <span>Según desempeño</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>Deducciones</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Seguro Social (9.75%)</label>
                                <div class="d-flex align-center gap-md">
                                    <input type="checkbox" checked disabled>
                                    <span>Obligatorio</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Impuesto sobre la Renta</label>
                                <div class="d-flex align-center gap-md">
                                    <input type="checkbox" checked disabled>
                                    <span>Según escala</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Préstamos</label>
                                <div class="d-flex align-center gap-md">
                                    <input type="checkbox" checked>
                                    <span>Individual</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-lg">
                    <button class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Funciones de nómina
function verDetalleNomina(nominaId) {
    mostrarToast('Abriendo detalle de nómina...', 'info');
    // Aquí iría la lógica para mostrar el detalle
}

function descargarRecibo(nominaId) {
    mostrarToast('Descargando recibo de pago...', 'success');
    // Aquí iría la lógica para descargar el PDF
}

function calcularNomina() {
    mostrarToast('Iniciando cálculo de nómina...', 'info');
    // Aquí iría la lógica para calcular
}

function previsualizarCalculo() {
    mostrarToast('Generando previsualización...', 'info');
    // Aquí iría la lógica para previsualizar
}

function aprobarNomina() {
    if (confirm('¿Está seguro de aprobar y procesar la nómina?')) {
        mostrarToast('Nómina aprobada y procesada', 'success');
        // Aquí iría la lógica para aprobar
    }
}
</script>

 Agregando inclusión del JavaScript principal 
<script src="assets/js/app.js"></script>

<?php require_once 'includes/footer.php'; ?>
