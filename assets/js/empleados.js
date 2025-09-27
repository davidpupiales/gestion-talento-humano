/**
 * Lógica de Interacción (UI) para la Gestión de Empleados
 * V2.0 - Implementación de Tabs y Cálculos Dinámicos
 */

document.addEventListener('DOMContentLoaded', () => {
    // Definiciones de Constantes (Deben coincidir con utils.php)
    const SMLV_JS = 1423500; 
    const TASAS_ARL_JS = {
        'I': 0.00522,   
        'II': 0.01044, 
        'III': 0.02436, 
        'IV': 0.04350,  
        'V': 0.06960,   
    };
    
    // Referencias a elementos comunes
    const modal = document.getElementById('modal-empleado');
    const form = document.getElementById('form-nuevo-empleado');
    
    // ===============================================
    // 1. GESTIÓN DE MODAL Y ACCIONES CRUD
    // ===============================================
    
    // El modal ahora usa 'modal-empleado' para el overlay y 'form-nuevo-empleado' para el formulario
    
    window.abrirModalEmpleado = function(empleadoId = null) {
        if (!modal) return;
        modal.style.display = 'flex'; 
        
        if (empleadoId) {
            document.getElementById('modal-titulo').textContent = 'Editar Empleado';
            // Lógica para cargar datos (AJAX)
            mostrarToast('Cargando datos para edición...', 'info');
        } else {
            document.getElementById('modal-titulo').textContent = 'Ingreso Nuevo Empleado';
            form.reset();
            // Asegurar que la primera pestaña esté activa al abrir
            activarPestana('laboral');
            // Recalcular valores por defecto
            inicializarCalculos(); 
        }
    };

    window.cerrarModalEmpleado = function() {
        if (modal) {
            modal.style.display = 'none';
        }
    };
    
    // ... [Funciones verEmpleado, editarEmpleado, eliminarEmpleado, exportarEmpleados se mantienen iguales] ...
    
    // Cerrar modal al hacer click fuera (Usando e.target.id para mayor compatibilidad)
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target.id === 'modal-empleado') {
                cerrarModalEmpleado();
            }
        });
    }

    // Manejar envío del formulario
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (form.checkValidity()) {
                mostrarToast('Datos de empleado enviados para procesamiento...', 'success');
                // Aquí se realizaría el envío real (AJAX o POST a PHP)
            } else {
                mostrarToast('Por favor complete todos los campos obligatorios.', 'error');
            }
        });
    }

    // ===============================================
    // 2. LÓGICA DE PESTAÑAS (TABS)
    // ===============================================

    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    function activarPestana(targetTab) {
        tabButtons.forEach(btn => {
            if (btn.getAttribute('data-tab') === targetTab) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        tabContents.forEach(content => {
            if (content.id === `tab-${targetTab}`) {
                content.style.display = 'grid'; 
            } else {
                content.style.display = 'none';
            }
        });
    }

    tabButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const targetTab = event.target.getAttribute('data-tab');
            activarPestana(targetTab);
        });
    });

    // ===============================================
    // 3. CÁLCULOS DINÁMICOS DEL FORMULARIO (FRONTEND)
    // ===============================================
    
    // Secciones Laboral
    const inputFechaInicio = document.querySelector('input[name="fecha_inicio"]');
    const inputFechaFin = document.querySelector('input[name="fecha_fin"]');
    const outputDiasTrabajados = document.querySelector('input[name="dias_trabajados"]');
    const inputNivelRiesgo = document.querySelector('select[name="nivel_riesgo"]');
    
    // Secciones Pagos
    const inputTipoContrato = document.querySelector('select[name="contrato"]');
    const inputMesada = document.querySelector('input[name="mesada"]');
    const outputAuxTransporte = document.querySelector('input[name="aux_transporte"]');
    
    // Secciones Aportes
    const outputTasaArl = document.querySelector('input[name="tasa_arl"]');
    
    /**
     * 3.1. Calcula los días trabajados entre dos fechas.
     */
    function calcularDiasTrabajados() {
        const inicio = inputFechaInicio ? inputFechaInicio.value : null;
        const fin = inputFechaFin ? inputFechaFin.value : null;

        if (inicio && fin) {
            const date1 = new Date(inicio);
            const date2 = new Date(fin);
            
            // Verifica que las fechas sean válidas y la fecha de fin sea posterior o igual a la de inicio
            if (isNaN(date1) || isNaN(date2) || date1 > date2) {
                outputDiasTrabajados.value = 0;
                return;
            }
            
            const diffTime = Math.abs(date2 - date1);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 

            outputDiasTrabajados.value = diffDays;
        } else {
            outputDiasTrabajados.value = 0;
        }
    }

    /**
     * 3.2. Calcula el auxilio de transporte.
     */
    function calcularAuxilioTransporte() {
        const contrato = inputTipoContrato ? inputTipoContrato.value : '';
        const mesada = parseFloat(inputMesada ? inputMesada.value : 0);
        
        // Lógica: Contrato LAB AND Mesada < (2 * SMLV) AND Mesada > 0
        if (contrato === 'LAB' && mesada < (2 * SMLV_JS) && mesada > 0) {
            outputAuxTransporte.value = 200000.00; // Valor fijo
        } else {
            outputAuxTransporte.value = 0.00;
        }
        // Nota: Las funciones calcularPresMensual y PresAnual deben ir aquí
    }

    /**
     * 3.3. Obtiene la TASA ARL.
     */
    function calcularTasaArl() {
        const nivel = inputNivelRiesgo ? inputNivelRiesgo.value.toUpperCase() : '';
        const tasa = TASAS_ARL_JS[nivel] !== undefined ? TASAS_ARL_JS[nivel] : 0.00000;
        
        // Formatear como decimal con precisión
        outputTasaArl.value = tasa.toFixed(5); 
    }
    
    /**
     * Inicializa los listeners y cálculos.
     */
    function inicializarCalculos() {
        // Listeners para DÍAS TRABAJADOS
        if (inputFechaInicio && inputFechaFin) {
            inputFechaInicio.addEventListener('change', calcularDiasTrabajados);
            inputFechaFin.addEventListener('change', calcularDiasTrabajados);
        }
        
        // Listeners para AUXILIO DE TRANSPORTE
        if (inputTipoContrato && inputMesada) {
            inputTipoContrato.addEventListener('change', calcularAuxilioTransporte);
            inputMesada.addEventListener('input', calcularAuxilioTransporte);
        }
        
        // Listeners para TASA ARL
        if (inputNivelRiesgo) {
            inputNivelRiesgo.addEventListener('change', calcularTasaArl);
        }

        // Ejecución inicial
        calcularDiasTrabajados();
        calcularAuxilioTransporte();
        calcularTasaArl();
    }
    
    // 4. LÓGICA DE BÚSQUEDA Y FILTRO
    // ... [Se mantiene la lógica actual, pero envuelta en el DOMContentLoaded] ...
    
    // Lógica de búsqueda y filtro
    const buscarEmpleadoInput = document.getElementById('buscar-empleado');
    const filtroDepartamentoSelect = document.getElementById('filtro-departamento');
    
    if (buscarEmpleadoInput) {
        buscarEmpleadoInput.addEventListener('input', function(e) {
            const termino = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr, .employee-list .employee-card');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(termino) ? '' : 'none';
            });
        });
    }

    if (filtroDepartamentoSelect) {
        filtroDepartamentoSelect.addEventListener('change', function(e) {
            const departamento = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr, .employee-list .employee-card');
            rows.forEach(row => {
                if (!departamento) {
                    row.style.display = '';
                } else {
                    // Busca el texto del badge de departamento
                    const deptElement = row.querySelector('.badge-secondary');
                    const deptText = deptElement ? deptElement.textContent.toLowerCase() : '';
                    
                    row.style.display = deptText.includes(departamento) ? '' : 'none';
                }
            });
        });
    }
    
    // Inicializar todo al cargar el DOM
    activarPestana('laboral'); // Asegurar la pestaña inicial
    inicializarCalculos();
});