<?php
require_once 'includes/header.php';

// Datos mock para el dashboard
$metricas = [
    'empleados_activos' => 156,
    'contratos_vencer' => 8,
    'solicitudes_pendientes' => 12,
    'capacitaciones_mes' => 24
];

$actividad_reciente = [
    ['tipo' => 'nuevo_empleado', 'mensaje' => 'Nuevo empleado registrado: Ana Martínez', 'tiempo' => '2 horas'],
    ['tipo' => 'permiso', 'mensaje' => 'Solicitud de permiso aprobada para Carlos López', 'tiempo' => '4 horas'],
    ['tipo' => 'documento', 'mensaje' => 'Documento firmado: Política de Seguridad', 'tiempo' => '6 horas'],
    ['tipo' => 'capacitacion', 'mensaje' => 'Capacitación completada: Seguridad Laboral', 'tiempo' => '1 día']
];
?>

<div class="page-content fade-in">
     Mejorando estructura de métricas con nuevo CSS 
     Métricas Principales 
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-value"><?php echo $metricas['empleados_activos']; ?></div>
            <div class="metric-label">Empleados Activos</div>
            <div class="metric-change">
                <i class="fas fa-arrow-up"></i> +5% este mes
            </div>
            <div class="metric-icon"><i class="fas fa-users"></i></div>
        </div>
        
        <div class="metric-card warning">
            <div class="metric-value"><?php echo $metricas['contratos_vencer']; ?></div>
            <div class="metric-label">Contratos por Vencer</div>
            <div class="metric-change">
                <i class="fas fa-clock"></i> Próximos 30 días
            </div>
            <div class="metric-icon"><i class="fas fa-file-contract"></i></div>
        </div>
        
        <div class="metric-card success">
            <div class="metric-value"><?php echo $metricas['solicitudes_pendientes']; ?></div>
            <div class="metric-label">Solicitudes Pendientes</div>
            <div class="metric-change">
                <i class="fas fa-hourglass-half"></i> Requieren atención
            </div>
            <div class="metric-icon"><i class="fas fa-clipboard-list"></i></div>
        </div>
        
        <div class="metric-card danger">
            <div class="metric-value"><?php echo $metricas['capacitaciones_mes']; ?></div>
            <div class="metric-label">Capacitaciones</div>
            <div class="metric-change">
                <i class="fas fa-arrow-up"></i> Este mes
            </div>
            <div class="metric-icon"><i class="fas fa-graduation-cap"></i></div>
        </div>
    </div>
    
     Gráficos y Actividad 
    <div class="grid grid-2">
         Gráfico de Empleados por Departamento 
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Empleados por Departamento</h3>
            </div>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: var(--secondary-bg); border-radius: var(--radius);">
                <div style="text-align: center; color: var(--text-secondary);">
                    <i class="fas fa-chart-pie" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>Gráfico de distribución por departamentos</p>
                    <small>Función pendiente de implementación</small>
                </div>
            </div>
        </div>
        
         Actividad Reciente 
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actividad Reciente</h3>
            </div>
            <div>
                <?php foreach ($actividad_reciente as $actividad): ?>
                    <div style="display: flex; align-items: center; padding: 1rem 0; border-bottom: 1px solid var(--border-color);">
                        <div style="width: 40px; height: 40px; background: var(--accent-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                            <i class="fas fa-<?php 
                                echo $actividad['tipo'] === 'nuevo_empleado' ? 'user-plus' : 
                                    ($actividad['tipo'] === 'permiso' ? 'calendar-check' : 
                                    ($actividad['tipo'] === 'documento' ? 'file-signature' : 'graduation-cap')); 
                            ?>"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="color: var(--text-primary); margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($actividad['mensaje']); ?>
                            </div>
                            <div style="color: var(--text-muted); font-size: 0.875rem;">
                                Hace <?php echo $actividad['tiempo']; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
     Alertas y Recordatorios 
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bell"></i>
                Alertas y Recordatorios
            </h3>
        </div>
        <div class="card-body">
            <div class="grid grid-3">
                <div class="metric-card warning">
                    <div class="metric-value">8</div>
                    <div class="metric-label">Contratos por Vencer</div>
                    <div class="metric-icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-value">3</div>
                    <div class="metric-label">Cumpleaños</div>
                    <div class="metric-icon"><i class="fas fa-birthday-cake"></i></div>
                </div>
                
                <div class="metric-card success">
                    <div class="metric-value">5</div>
                    <div class="metric-label">Documentos Pendientes</div>
                    <div class="metric-icon"><i class="fas fa-file-signature"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
