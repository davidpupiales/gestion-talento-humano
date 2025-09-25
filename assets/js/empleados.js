// Funciones para gestión de empleados
function abrirModalEmpleado(empleadoId = null) {
    const modal = document.getElementById('modal-empleado');
    modal.classList.add('show');
    if (empleadoId) {
        document.getElementById('modal-titulo').textContent = 'Editar Empleado';
        // Aquí se cargan los datos del empleado para edición
    } else {
        document.getElementById('modal-titulo').textContent = 'Nuevo Empleado';
        document.getElementById('form-empleado').reset();
    }
}

function cerrarModalEmpleado() {
    const modal = document.getElementById('modal-empleado');
    modal.classList.remove('show');
}

function verEmpleado(empleadoId) {
    mostrarToast('Abriendo perfil del empleado...', 'info');
    // Lógica para mostrar el perfil completo del empleado
}

function editarEmpleado(empleadoId) {
    abrirModalEmpleado(empleadoId);
}

function eliminarEmpleado(empleadoId) {
    if (confirm('¿Está seguro de que desea eliminar este empleado?')) {
        mostrarToast('Empleado eliminado correctamente', 'success');
        // Lógica para eliminar el empleado
    }
}

function exportarEmpleados() {
    mostrarToast('Exportando lista de empleados...', 'info');
    // Lógica para exportar los datos
}

// Lógica de búsqueda y filtro
document.getElementById('buscar-empleado').addEventListener('input', function(e) {
    const termino = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.table tbody tr, .employee-list .employee-card');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(termino) ? '' : 'none';
    });
});

document.getElementById('filtro-departamento').addEventListener('change', function(e) {
    const departamento = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.table tbody tr, .employee-list .employee-card');
    rows.forEach(row => {
        if (!departamento) {
            row.style.display = '';
        } else {
            const deptText = row.querySelector('.badge-secondary').textContent.toLowerCase();
            row.style.display = deptText.includes(departamento) ? '' : 'none';
        }
    });
});

// Manejar envío del formulario
document.getElementById('form-empleado').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    if (form.checkValidity()) {
        mostrarToast('Empleado guardado correctamente', 'success');
        cerrarModalEmpleado();
    } else {
        mostrarToast('Por favor complete todos los campos obligatorios', 'error');
    }
});

// Cerrar modal al hacer click fuera
document.getElementById('modal-empleado').addEventListener('click', function(e) {
    if (e.target.id === 'modal-empleado') {
        cerrarModalEmpleado();
    }
});