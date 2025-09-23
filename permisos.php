<?php
require_once 'includes/header.php';

// Verificar permisos
if (!SessionManager::tienePermiso('empleado')) {
    header('Location: dashboard.php');
    exit();
}

// Datos mock de permisos
$mis_permisos = [
    [
        'id' => 1,
        'tipo' => 'vacaciones',
        'fecha_inicio' => '2024-02-20',
        'fecha_fin' => '2024-02-25',
        'dias' => 5,
        'motivo' => 'Vacaciones familiares',
        'estado' => 'aprobado',
        'fecha_solicitud' => '2024-01-15',
        'aprobado_por' => 'María González'
    ],
    [
        'id' => 2,
        'tipo' => 'medico',
        'fecha_inicio' => '2024-02-10',
        'fecha_fin' => '2024-02-10',
        'dias' => 1,
        'motivo' => 'Cita médica especialista',
        'estado' => 'pendiente',
        'fecha_solicitud' => '2024-02-08',
        'aprobado_por' => null
    ]
];

$tipos_permiso = [
    'vacaciones' => 'Vacaciones',
    'medico' => 'Médico',
    'personal' => 'Personal',
    'maternidad' => 'Maternidad/Paternidad',
    'calamidad' => 'Calamidad Doméstica',
    'capacitacion' => 'Capacitación'
];
?>

<div class="page-content fade-in">
     Reemplazando sistema de botones por pestañas profesionales 
     Navegación por pestañas mejorada 
    <div class="tab-navigation">
        <button class="tab-button active" onclick="cambiarPestana('solicitar', this)">
            <i class="fas fa-plus"></i> Solicitar Permiso
        </button>
        <button class="tab-button" onclick="cambiarPestana('mis-permisos', this)">
            <i class="fas fa-calendar-check"></i> Mis Permisos
        </button>
        <button class="tab-button" onclick="cambiarPestana('calendario', this)">
            <i class="fas fa-calendar"></i> Calendario
        </button>
        <?php if (SessionManager::tienePermiso('gerente')): ?>
        <button class="tab-button" onclick="cambiarPestana('aprobaciones', this)">
            <i class="fas fa-check-circle"></i> Aprobaciones
        </button>
        <button class="tab-button" onclick="cambiarPestana('reportes', this)">
            <i class="fas fa-chart-bar"></i> Reportes
        </button>
        <?php endif; ?>
    </div>

     Pestaña: Solicitar Permiso 
    <div id="tab-solicitar" class="tab-content active">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus"></i>
                    Solicitar Nuevo Permiso
                </h3>
                <div class="card-subtitle">
                    Completa el formulario para solicitar un permiso o ausencia
                </div>
            </div>
            <div class="card-body">
                 Balance de días disponibles 
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-value">15</div>
                        <div class="metric-label">Días Vacaciones</div>
                        <div class="metric-icon"><i class="fas fa-umbrella-beach"></i></div>
                    </div>
                    <div class="metric-card success">
                        <div class="metric-value">5</div>
                        <div class="metric-label">Días Personales</div>
                        <div class="metric-icon"><i class="fas fa-user-clock"></i></div>
                    </div>
                    <div class="metric-card warning">
                        <div class="metric-value">3</div>
                        <div class="metric-label">Días Médicos</div>
                        <div class="metric-icon"><i class="fas fa-user-md"></i></div>
                    </div>
                    <div class="metric-card danger">
                        <div class="metric-value">8</div>
                        <div class="metric-label">Días Usados</div>
                        <div class="metric-icon"><i class="fas fa-calendar-times"></i></div>
                    </div>
                </div>

                 Formulario de solicitud 
                <div class="permission-form">
                    <div class="form-title">
                        <i class="fas fa-edit"></i>
                        Datos de la Solicitud
                    </div>
                    
                    <form id="form-permiso">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Tipo de Permiso</label>
                                <select class="form-control" id="tipo-permiso" required>
                                    <option value="">Seleccionar tipo...</option>
                                    <?php foreach ($tipos_permiso as $key => $valor): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $valor; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="fecha-inicio" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control" id="fecha-fin" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Días Solicitados</label>
                                <input type="number" class="form-control" id="dias-solicitados" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Motivo/Justificación</label>
                            <textarea class="form-control" id="motivo" rows="4" placeholder="Describe el motivo de tu solicitud..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Archivo Adjunto (Opcional)</label>
                            <input type="file" class="form-control" id="archivo-adjunto" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Formatos permitidos: PDF, JPG, PNG. Máximo 5MB</small>
                        </div>
                        
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Enviar Solicitud
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                                <i class="fas fa-undo"></i> Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

     Pestaña: Mis Permisos 
    <div id="tab-mis-permisos" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check"></i>
                    Mis Permisos y Ausencias
                </h3>
                <div class="card-subtitle">
                    Historial y estado de tus solicitudes
                </div>
            </div>
            <div class="card-body">
                 Filtros 
                <div class="grid grid-3 mb-lg">
                    <div class="form-group">
                        <label class="form-label">Tipo</label>
                        <select class="form-control">
                            <option value="">Todos los tipos</option>
                            <?php foreach ($tipos_permiso as $key => $valor): ?>
                            <option value="<?php echo $key; ?>"><?php echo $valor; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select class="form-control">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Año</label>
                        <select class="form-control">
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                </div>

                 Tabla de permisos 
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Fechas</th>
                                <th>Días</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Fecha Solicitud</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mis_permisos as $permiso): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo $tipos_permiso[$permiso['tipo']]; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($permiso['fecha_inicio'])); ?>
                                    <?php if ($permiso['fecha_inicio'] != $permiso['fecha_fin']): ?>
                                        - <?php echo date('d/m/Y', strtotime($permiso['fecha_fin'])); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $permiso['dias']; ?></td>
                                <td><?php echo htmlspecialchars($permiso['motivo']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $permiso['estado'] == 'aprobado' ? 'success' : ($permiso['estado'] == 'pendiente' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($permiso['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($permiso['fecha_solicitud'])); ?></td>
                                <td>
                                    <div class="d-flex gap-sm">
                                        <button class="btn btn-sm btn-secondary" onclick="verDetallePermiso(<?php echo $permiso['id']; ?>)" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($permiso['estado'] == 'pendiente'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="cancelarPermiso(<?php echo $permiso['id']; ?>)" title="Cancelar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
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

     Pestaña: Calendario 
    <div id="tab-calendario" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar"></i>
                    Calendario de Ausencias
                </h3>
                <div class="card-subtitle">
                    Vista calendario de tus permisos y ausencias
                </div>
            </div>
            <div class="card-body">
                 Calendario placeholder 
                <div style="height: 500px; background: var(--secondary-bg); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                    <div class="text-center">
                        <i class="fas fa-calendar-alt" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                        <h4>Calendario de Permisos</h4>
                        <p>Vista calendario interactiva</p>
                        <small>Función pendiente de implementación</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

     Pestaña: Aprobaciones (Solo Gerentes) 
    <?php if (SessionManager::tienePermiso('gerente')): ?>
    <div id="tab-aprobaciones" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-check-circle"></i>
                    Aprobaciones Pendientes
                </h3>
                <div class="card-subtitle">
                    Revisar y aprobar solicitudes de permisos
                </div>
            </div>
            <div class="card-body">
                 Estadísticas de aprobaciones 
                <div class="grid grid-4 mb-lg">
                    <div class="metric-card warning">
                        <div class="metric-value">8</div>
                        <div class="metric-label">Pendientes</div>
                    </div>
                    <div class="metric-card success">
                        <div class="metric-value">25</div>
                        <div class="metric-label">Aprobadas</div>
                    </div>
                    <div class="metric-card danger">
                        <div class="metric-value">3</div>
                        <div class="metric-label">Rechazadas</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">2.5</div>
                        <div class="metric-label">Días Promedio</div>
                    </div>
                </div>

                 Lista de solicitudes pendientes 
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Tipo</th>
                                <th>Fechas</th>
                                <th>Días</th>
                                <th>Motivo</th>
                                <th>Fecha Solicitud</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-center gap-md">
                                        <div style="width: 32px; height: 32px; background: var(--accent-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: white;">
                                            J
                                        </div>
                                        <div>
                                            <div class="text-primary">Juan Pérez</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">EMP001</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-info">Médico</span></td>
                                <td>10/02/2024</td>
                                <td>1</td>
                                <td>Cita médica especialista</td>
                                <td>08/02/2024</td>
                                <td>
                                    <div class="d-flex gap-sm">
                                        <button class="btn btn-sm btn-success" onclick="aprobarPermiso(2)" title="Aprobar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rechazarPermiso(2)" title="Rechazar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button class="btn btn-sm btn-secondary" onclick="verDetallePermiso(2)" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

     Pestaña: Reportes (Solo Gerentes) 
    <div id="tab-reportes" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i>
                    Reportes de Permisos
                </h3>
                <div class="card-subtitle">
                    Análisis y estadísticas de ausencias
                </div>
            </div>
            <div class="card-body">
                 Tipos de reportes 
                <div class="grid grid-3 mb-lg">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-pie" style="font-size: 2rem; color: var(--accent-primary); margin-bottom: 1rem;"></i>
                            <h4>Reporte por Tipo</h4>
                            <p class="text-muted">Distribución por tipo de permiso</p>
                            <button class="btn btn-primary">Generar</button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line" style="font-size: 2rem; color: var(--accent-secondary); margin-bottom: 1rem;"></i>
                            <h4>Tendencias</h4>
                            <p class="text-muted">Tendencias mensuales</p>
                            <button class="btn btn-success">Generar</button>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-users" style="font-size: 2rem; color: var(--accent-warning); margin-bottom: 1rem;"></i>
                            <h4>Por Empleado</h4>
                            <p class="text-muted">Reporte individual</p>
                            <button class="btn btn-warning">Generar</button>
                        </div>
                    </div>
                </div>

                 Gráfico placeholder 
                <div class="card">
                    <div class="card-header">
                        <h4>Permisos por Mes - Año 2024</h4>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px; background: var(--secondary-bg); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                            <div class="text-center">
                                <i class="fas fa-chart-bar" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p>Gráfico de permisos por mes</p>
                                <small>Función pendiente de implementación</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function cambiarPestana(pestana, boton) {
    // Ocultar todas las pestañas
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Desactivar todos los botones
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Mostrar pestaña seleccionada
    document.getElementById('tab-' + pestana).classList.add('active');
    
    // Activar botón
    boton.classList.add('active');
}

// Calcular días automáticamente
document.addEventListener('DOMContentLoaded', function() {
    const fechaInicio = document.getElementById('fecha-inicio');
    const fechaFin = document.getElementById('fecha-fin');
    const diasSolicitados = document.getElementById('dias-solicitados');
    
    function calcularDias() {
        if (fechaInicio.value && fechaFin.value) {
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);
            const diferencia = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;
            diasSolicitados.value = diferencia > 0 ? diferencia : 0;
        }
    }
    
    fechaInicio.addEventListener('change', calcularDias);
    fechaFin.addEventListener('change', calcularDias);
});

// Funciones de permisos
function limpiarFormulario() {
    document.getElementById('form-permiso').reset();
}

function verDetallePermiso(permisoId) {
    mostrarToast('Abriendo detalle del permiso...', 'info');
}

function cancelarPermiso(permisoId) {
    if (confirm('¿Está seguro de cancelar esta solicitud?')) {
        mostrarToast('Solicitud cancelada', 'success');
    }
}

function aprobarPermiso(permisoId) {
    if (confirm('¿Aprobar esta solicitud de permiso?')) {
        mostrarToast('Permiso aprobado correctamente', 'success');
    }
}

function rechazarPermiso(permisoId) {
    const motivo = prompt('Motivo del rechazo (opcional):');
    if (motivo !== null) {
        mostrarToast('Permiso rechazado', 'warning');
    }
}

// Manejar envío del formulario
document.getElementById('form-permiso').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const tipo = document.getElementById('tipo-permiso').value;
    const fechaInicio = document.getElementById('fecha-inicio').value;
    const motivo = document.getElementById('motivo').value;
    
    if (!tipo || !fechaInicio || !motivo) {
        mostrarToast('Por favor complete todos los campos obligatorios', 'error');
        return;
    }
    
    mostrarToast('Solicitud enviada correctamente', 'success');
    limpiarFormulario();
});
</script>

<?php require_once 'includes/footer.php'; ?>
