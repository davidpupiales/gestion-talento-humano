<?php
require_once 'includes/header.php';

// Verificar permisos
if (!SessionManager::tienePermiso('gerente')) {
    header('Location: dashboard.php');
    exit();
}

// Datos mock de empleados
// En futuras etapas, esta función se conectará a la base de datos real.
function getMockEmpleados() {
    return [
        [
            'id' => 1,
            'codigo' => 'EMP001',
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'cedula' => '12345678',
            'email' => 'juan.perez@empresa.com',
            'telefono' => '555-0123',
            'departamento' => 'Desarrollo',
            'cargo' => 'Desarrollador Senior',
            'fecha_ingreso' => '2023-01-15',
            'salario_base' => 50000,
            'estado' => 'activo'
        ],
        [
            'id' => 2,
            'codigo' => 'EMP002',
            'nombre' => 'María',
            'apellido' => 'González',
            'cedula' => '87654321',
            'email' => 'maria.gonzalez@empresa.com',
            'telefono' => '555-0124',
            'departamento' => 'Recursos Humanos',
            'cargo' => 'Gerente de RRHH',
            'fecha_ingreso' => '2022-05-10',
            'salario_base' => 60000,
            'estado' => 'activo'
        ],
        [
            'id' => 3,
            'codigo' => 'EMP003',
            'nombre' => 'Carlos',
            'apellido' => 'Rodríguez',
            'cedula' => '11223344',
            'email' => 'carlos.rodriguez@empresa.com',
            'telefono' => '555-0125',
            'departamento' => 'Ventas',
            'cargo' => 'Ejecutivo de Ventas',
            'fecha_ingreso' => '2023-06-20',
            'salario_base' => 35000,
            'estado' => 'activo'
        ],
        [
            'id' => 4,
            'codigo' => 'EMP004',
            'nombre' => 'Ana',
            'apellido' => 'Martínez',
            'cedula' => '55667788',
            'email' => 'ana.martinez@empresa.com',
            'telefono' => '555-0126',
            'departamento' => 'Marketing',
            'cargo' => 'Especialista en Marketing',
            'fecha_ingreso' => '2023-09-10',
            'salario_base' => 40000,
            'estado' => 'activo'
        ]
    ];
}
$empleados = getMockEmpleados();
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
            <div class="metric-value">4</div>
            <div class="metric-label">Departamentos</div>
            <div class="metric-icon"><i class="fas fa-building"></i></div>
        </div>
        <div class="metric-card warning">
            <div class="metric-value">100%</div>
            <div class="metric-label">Empleados Activos</div>
            <div class="metric-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="metric-card danger">
            <div class="metric-value">2</div>
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
                    <?php foreach ($empleados as $empleado): ?>
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
        </div>
    </div>
</div>

<div id="modal-empleado" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-titulo">Nuevo Empleado</h3>
            <button class="modal-close" onclick="cerrarModalEmpleado()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="form-empleado">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="empleado-nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido</label>
                        <input type="text" id="empleado-apellido" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cédula</label>
                        <input type="text" id="empleado-cedula" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" id="empleado-email" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="text" id="empleado-telefono" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departamento</label>
                        <select id="empleado-departamento" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <option value="Desarrollo">Desarrollo</option>
                            <option value="Recursos Humanos">Recursos Humanos</option>
                            <option value="Ventas">Ventas</option>
                            <option value="Marketing">Marketing</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cargo</label>
                        <input type="text" id="empleado-cargo" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Ingreso</label>
                        <input type="date" id="empleado-fecha-ingreso" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Salario Base</label>
                        <input type="number" id="empleado-salario" class="form-control" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select id="empleado-estado" class="form-control">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="suspendido">Suspendido</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalEmpleado()">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary" form="form-empleado">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<script src="assets/js/empleados.js"></script>

<?php require_once 'includes/footer.php'; ?>