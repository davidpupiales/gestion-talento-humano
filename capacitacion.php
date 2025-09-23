<?php
require_once 'includes/header.php';

// Verificar permisos
if (!SessionManager::tienePermiso('empleado')) {
    header('Location: dashboard.php');
    exit();
}

// Datos mock de capacitaciones
$capacitaciones = [
    [
        'id' => 1,
        'titulo' => 'Seguridad Laboral',
        'descripcion' => 'Curso obligatorio sobre normas de seguridad en el trabajo',
        'instructor' => 'María González',
        'fecha_inicio' => '2024-02-15',
        'fecha_fin' => '2024-02-16',
        'duracion' => '16 horas',
        'modalidad' => 'Presencial',
        'estado' => 'disponible',
        'obligatorio' => true,
        'participantes' => 25,
        'max_participantes' => 30
    ],
    [
        'id' => 2,
        'titulo' => 'Liderazgo y Gestión de Equipos',
        'descripcion' => 'Desarrollo de habilidades de liderazgo para supervisores',
        'instructor' => 'Carlos Rodríguez',
        'fecha_inicio' => '2024-02-20',
        'fecha_fin' => '2024-02-22',
        'duracion' => '24 horas',
        'modalidad' => 'Virtual',
        'estado' => 'inscripcion_abierta',
        'obligatorio' => false,
        'participantes' => 12,
        'max_participantes' => 20
    ]
];

$mis_capacitaciones = [
    [
        'id' => 1,
        'titulo' => 'Seguridad Laboral',
        'fecha_inscripcion' => '2024-01-15',
        'progreso' => 75,
        'estado' => 'en_progreso',
        'calificacion' => null
    ]
];
?>

<div class="page-content fade-in">
     Navegación de submódulos 
    <div class="card mb-lg">
        <div class="card-body">
            <div class="d-flex gap-md">
                <button class="btn btn-primary" onclick="mostrarSubmodulo('catalogo')">
                    <i class="fas fa-book"></i> Catálogo de Cursos
                </button>
                <button class="btn btn-secondary" onclick="mostrarSubmodulo('mis-cursos')">
                    <i class="fas fa-user-graduate"></i> Mis Capacitaciones
                </button>
                <button class="btn btn-secondary" onclick="mostrarSubmodulo('certificados')">
                    <i class="fas fa-certificate"></i> Certificados
                </button>
                <?php if (SessionManager::tienePermiso('gerente')): ?>
                <button class="btn btn-secondary" onclick="mostrarSubmodulo('gestion')">
                    <i class="fas fa-cogs"></i> Gestión de Cursos
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

     Submódulo: Catálogo de Cursos 
    <div id="submodulo-catalogo" class="submodulo active">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book"></i>
                    Catálogo de Capacitaciones
                </h3>
                <div class="card-subtitle">
                    Explora y regístrate en los cursos disponibles
                </div>
            </div>
            <div class="card-body">
                 Filtros 
                <div class="grid grid-3 mb-lg">
                    <div class="form-group">
                        <label class="form-label">Modalidad</label>
                        <select class="form-control">
                            <option value="">Todas</option>
                            <option value="presencial">Presencial</option>
                            <option value="virtual">Virtual</option>
                            <option value="mixta">Mixta</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Área</label>
                        <select class="form-control">
                            <option value="">Todas las áreas</option>
                            <option value="seguridad">Seguridad</option>
                            <option value="liderazgo">Liderazgo</option>
                            <option value="tecnica">Técnica</option>
                            <option value="soft-skills">Habilidades Blandas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select class="form-control">
                            <option value="">Todos</option>
                            <option value="disponible">Disponible</option>
                            <option value="inscripcion_abierta">Inscripción Abierta</option>
                            <option value="en_progreso">En Progreso</option>
                        </select>
                    </div>
                </div>

                 Lista de capacitaciones 
                <div class="grid grid-2">
                    <?php foreach ($capacitaciones as $capacitacion): ?>
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-between align-center">
                                <h4 class="card-title mb-0"><?php echo htmlspecialchars($capacitacion['titulo']); ?></h4>
                                <?php if ($capacitacion['obligatorio']): ?>
                                <span class="badge badge-danger">Obligatorio</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-md"><?php echo htmlspecialchars($capacitacion['descripcion']); ?></p>
                            
                            <div class="mb-md">
                                <div class="d-flex justify-between mb-sm">
                                    <span class="text-muted">Instructor:</span>
                                    <span><?php echo htmlspecialchars($capacitacion['instructor']); ?></span>
                                </div>
                                <div class="d-flex justify-between mb-sm">
                                    <span class="text-muted">Duración:</span>
                                    <span><?php echo htmlspecialchars($capacitacion['duracion']); ?></span>
                                </div>
                                <div class="d-flex justify-between mb-sm">
                                    <span class="text-muted">Modalidad:</span>
                                    <span class="badge badge-info"><?php echo ucfirst($capacitacion['modalidad']); ?></span>
                                </div>
                                <div class="d-flex justify-between mb-sm">
                                    <span class="text-muted">Fechas:</span>
                                    <span><?php echo date('d/m/Y', strtotime($capacitacion['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($capacitacion['fecha_fin'])); ?></span>
                                </div>
                                <div class="d-flex justify-between">
                                    <span class="text-muted">Participantes:</span>
                                    <span><?php echo $capacitacion['participantes']; ?>/<?php echo $capacitacion['max_participantes']; ?></span>
                                </div>
                            </div>

                            <div class="d-flex gap-sm">
                                <button class="btn btn-primary" onclick="inscribirseCapacitacion(<?php echo $capacitacion['id']; ?>)">
                                    <i class="fas fa-user-plus"></i> Inscribirse
                                </button>
                                <button class="btn btn-secondary" onclick="verDetalleCapacitacion(<?php echo $capacitacion['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Detalles
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

     Submódulo: Mis Capacitaciones 
    <div id="submodulo-mis-cursos" class="submodulo" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-graduate"></i>
                    Mis Capacitaciones
                </h3>
                <div class="card-subtitle">
                    Seguimiento de tus cursos y progreso
                </div>
            </div>
            <div class="card-body">
                 Estadísticas personales 
                <div class="grid grid-4 mb-lg">
                    <div class="metric-card">
                        <div class="metric-value">3</div>
                        <div class="metric-label">Cursos Completados</div>
                    </div>
                    <div class="metric-card secondary">
                        <div class="metric-value">1</div>
                        <div class="metric-label">En Progreso</div>
                    </div>
                    <div class="metric-card warning">
                        <div class="metric-value">48</div>
                        <div class="metric-label">Horas Totales</div>
                    </div>
                    <div class="metric-card danger">
                        <div class="metric-value">2</div>
                        <div class="metric-label">Pendientes</div>
                    </div>
                </div>

                 Lista de mis capacitaciones 
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th>Fecha Inscripción</th>
                                <th>Progreso</th>
                                <th>Estado</th>
                                <th>Calificación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mis_capacitaciones as $curso): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['titulo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($curso['fecha_inscripcion'])); ?></td>
                                <td>
                                    <div class="d-flex align-center gap-md">
                                        <div style="flex: 1; background: var(--secondary-bg); border-radius: var(--radius-full); height: 8px;">
                                            <div style="width: <?php echo $curso['progreso']; ?>%; background: var(--accent-primary); height: 100%; border-radius: var(--radius-full);"></div>
                                        </div>
                                        <span class="text-muted"><?php echo $curso['progreso']; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $curso['estado'] == 'completado' ? 'success' : ($curso['estado'] == 'en_progreso' ? 'info' : 'warning'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $curso['estado'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($curso['calificacion']): ?>
                                        <span class="text-primary"><?php echo $curso['calificacion']; ?>/100</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-sm">
                                        <button class="btn btn-sm btn-primary" onclick="continuarCurso(<?php echo $curso['id']; ?>)">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <button class="btn btn-sm btn-secondary" onclick="verProgreso(<?php echo $curso['id']; ?>)">
                                            <i class="fas fa-chart-line"></i>
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

     Submódulo: Certificados 
    <div id="submodulo-certificados" class="submodulo" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-certificate"></i>
                    Mis Certificados
                </h3>
                <div class="card-subtitle">
                    Descarga y gestiona tus certificados obtenidos
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-certificate" style="font-size: 3rem; color: var(--accent-warning); margin-bottom: 1rem;"></i>
                            <h4>Seguridad Laboral</h4>
                            <p class="text-muted">Completado el 15/01/2024</p>
                            <p class="text-muted">Calificación: 95/100</p>
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Descargar
                            </button>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-certificate" style="font-size: 3rem; color: var(--accent-secondary); margin-bottom: 1rem;"></i>
                            <h4>Primeros Auxilios</h4>
                            <p class="text-muted">Completado el 10/12/2023</p>
                            <p class="text-muted">Calificación: 88/100</p>
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Descargar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     Submódulo: Gestión de Cursos (Solo Gerentes) 
    <?php if (SessionManager::tienePermiso('gerente')): ?>
    <div id="submodulo-gestion" class="submodulo" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i>
                    Gestión de Capacitaciones
                </h3>
                <div class="card-subtitle">
                    Administrar cursos, instructores y reportes
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex gap-md mb-lg">
                    <button class="btn btn-primary" onclick="abrirModalNuevoCurso()">
                        <i class="fas fa-plus"></i> Nuevo Curso
                    </button>
                    <button class="btn btn-secondary" onclick="gestionarInstructores()">
                        <i class="fas fa-chalkboard-teacher"></i> Instructores
                    </button>
                    <button class="btn btn-secondary" onclick="reportesCapacitacion()">
                        <i class="fas fa-chart-bar"></i> Reportes
                    </button>
                </div>

                 Estadísticas de gestión 
                <div class="grid grid-4 mb-lg">
                    <div class="metric-card">
                        <div class="metric-value">12</div>
                        <div class="metric-label">Cursos Activos</div>
                    </div>
                    <div class="metric-card secondary">
                        <div class="metric-value">156</div>
                        <div class="metric-label">Participantes</div>
                    </div>
                    <div class="metric-card warning">
                        <div class="metric-value">8</div>
                        <div class="metric-label">Instructores</div>
                    </div>
                    <div class="metric-card danger">
                        <div class="metric-value">85%</div>
                        <div class="metric-label">Tasa Completación</div>
                    </div>
                </div>

                 Tabla de gestión 
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th>Instructor</th>
                                <th>Participantes</th>
                                <th>Estado</th>
                                <th>Fecha Inicio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($capacitaciones as $cap): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cap['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($cap['instructor']); ?></td>
                                <td><?php echo $cap['participantes']; ?>/<?php echo $cap['max_participantes']; ?></td>
                                <td>
                                    <span class="badge badge-info"><?php echo ucfirst(str_replace('_', ' ', $cap['estado'])); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($cap['fecha_inicio'])); ?></td>
                                <td>
                                    <div class="d-flex gap-sm">
                                        <button class="btn btn-sm btn-secondary" onclick="editarCurso(<?php echo $cap['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="verParticipantes(<?php echo $cap['id']; ?>)">
                                            <i class="fas fa-users"></i>
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
    <?php endif; ?>
</div>

<script>
// Gestión de submódulos
function mostrarSubmodulo(modulo) {
    // Ocultar todos los submódulos
    document.querySelectorAll('.submodulo').forEach(sub => {
        sub.style.display = 'none';
    });
    
    // Resetear botones
    document.querySelectorAll('.card-body .btn').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-secondary');
    });
    
    // Mostrar submódulo seleccionado
    document.getElementById('submodulo-' + modulo).style.display = 'block';
    
    // Activar botón
    event.target.classList.remove('btn-secondary');
    event.target.classList.add('btn-primary');
}

// Funciones de capacitación
function inscribirseCapacitacion(capacitacionId) {
    mostrarToast('Inscripción realizada correctamente', 'success');
}

function verDetalleCapacitacion(capacitacionId) {
    mostrarToast('Abriendo detalles del curso...', 'info');
}

function continuarCurso(cursoId) {
    mostrarToast('Continuando con el curso...', 'info');
}

function verProgreso(cursoId) {
    mostrarToast('Mostrando progreso detallado...', 'info');
}

function abrirModalNuevoCurso() {
    mostrarToast('Abriendo formulario de nuevo curso...', 'info');
}

function gestionarInstructores() {
    mostrarToast('Abriendo gestión de instructores...', 'info');
}

function reportesCapacitacion() {
    mostrarToast('Generando reportes de capacitación...', 'info');
}

function editarCurso(cursoId) {
    mostrarToast('Editando curso...', 'info');
}

function verParticipantes(cursoId) {
    mostrarToast('Mostrando lista de participantes...', 'info');
}
</script>

<?php require_once 'includes/footer.php'; ?>
