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
            // Recalcular valores por defecto
            inicializarCalculos();
            
            // Resetear municipios al crear nuevo empleado
            if (typeof resetearMunicipios === 'function') {
                setTimeout(() => resetearMunicipios(), 50);
            }
        }
        
        // Inicializar las pestañas correctamente
        if (typeof inicializarModalEmpleado === 'function') {
            setTimeout(() => inicializarModalEmpleado(), 100);
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

    // Manejar envío del formulario: dejar que el formulario haga POST al servidor
    // excepto cuando la validación falla.
    if (form) {
        // Helpers para mostrar errores inline en el modal
        function clearFieldErrors() {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        }
        function showFieldError(fieldName, message) {
            const el = form.querySelector(`[name="${fieldName}"]`);
            if (!el) return;
            el.classList.add('is-invalid');
            // Añadir mensaje si no existe
            let feed = el.parentElement.querySelector('.invalid-feedback');
            if (!feed) {
                feed = document.createElement('div');
                feed.className = 'invalid-feedback';
                el.parentElement.appendChild(feed);
            }
            feed.textContent = message;
        }

        function validateRequiredFields() {
            const requiredFields = {
                'cedula': 'Cédula',
                'nombre_completo': 'Nombre completo', 
                'email': 'Correo electrónico',
                'telefono': 'Teléfono',
                'cargo': 'Cargo',
                'tipo_contrato': 'Tipo de contrato',
                'departamento': 'Departamento',
                'municipio': 'Municipio'
            };
            
            const missingFields = [];
            const missingFieldsBySection = {};
            let hasErrors = false;
            
            Object.keys(requiredFields).forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (field && (!field.value || field.value.trim() === '')) {
                    missingFields.push(requiredFields[fieldName]);
                    showFieldError(fieldName, `${requiredFields[fieldName]} es obligatorio`);
                    hasErrors = true;
                    
                    // Identificar en qué sección está el campo
                    const tabContent = field.closest('.tab-content-modal');
                    if (tabContent) {
                        const sectionId = tabContent.id;
                        const sectionName = getSectionName(sectionId);
                        if (!missingFieldsBySection[sectionName]) {
                            missingFieldsBySection[sectionName] = [];
                        }
                        missingFieldsBySection[sectionName].push(requiredFields[fieldName]);
                    }
                }
            });
            
            // Validación específica por tipo de contrato
            const tipoContrato = form.querySelector('[name="tipo_contrato"]')?.value;
            if (tipoContrato === 'OPS') {
                const valorEvento = form.querySelector('[name="valor_por_evento"]');
                if (valorEvento && (!valorEvento.value || valorEvento.value.trim() === '')) {
                    missingFields.push('Valor por evento (requerido para contratos OPS)');
                    showFieldError('valor_por_evento', 'Valor por evento es obligatorio para contratos OPS');
                    hasErrors = true;
                    
                    const tabContent = valorEvento.closest('.tab-content-modal');
                    if (tabContent) {
                        const sectionId = tabContent.id;
                        const sectionName = getSectionName(sectionId);
                        if (!missingFieldsBySection[sectionName]) {
                            missingFieldsBySection[sectionName] = [];
                        }
                        missingFieldsBySection[sectionName].push('Valor por evento (OPS)');
                    }
                }
            } else if (tipoContrato === 'LAB') {
                const mesada = form.querySelector('[name="mesada"]');
                if (mesada && (!mesada.value || mesada.value.trim() === '')) {
                    missingFields.push('Mesada (requerida para contratos LAB)');
                    showFieldError('mesada', 'Mesada es obligatoria para contratos LAB');
                    hasErrors = true;
                    
                    const tabContent = mesada.closest('.tab-content-modal');
                    if (tabContent) {
                        const sectionId = tabContent.id;
                        const sectionName = getSectionName(sectionId);
                        if (!missingFieldsBySection[sectionName]) {
                            missingFieldsBySection[sectionName] = [];
                        }
                        missingFieldsBySection[sectionName].push('Mesada (LAB)');
                    }
                }
            }
            
            if (hasErrors) {
                let message = 'Faltan campos obligatorios:\n\n';
                
                // Agrupar por sección
                Object.keys(missingFieldsBySection).forEach(section => {
                    message += `📋 ${section}:\n`;
                    missingFieldsBySection[section].forEach(field => {
                        message += `  • ${field}\n`;
                    });
                    message += '\n';
                });
                
                // Si no se pudieron agrupar por sección, mostrar lista simple
                if (Object.keys(missingFieldsBySection).length === 0) {
                    message = 'Faltan los siguientes campos obligatorios:\n• ';
                    message += missingFields.join('\n• ');
                }
                
                return { valid: false, message: message.trim() };
            }
            
            return { valid: true, message: '' };
        }

        function getSectionName(sectionId) {
            const sectionNames = {
                'informacion-personal': 'Información Personal',
                'informacion-laboral': 'Información Laboral', 
                'ubicacion': 'Ubicación',
                'pagos': 'Información de Pagos',
                'bancaria': 'Información Bancaria',
                'aportes': 'Aportes y Seguridad Social',
                'capacitaciones': 'Capacitaciones'
            };
        return sectionNames[sectionId] || 'Información General';
    }

    // Función para manejar dropdown de acciones en tarjetas de empleados
    window.toggleDropdown = function(button) {
        const dropdown = button.nextElementSibling;
        const isVisible = dropdown.classList.contains('show');
        
        // Cerrar todos los dropdowns abiertos
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
        
        // Toggle del dropdown actual
        if (!isVisible) {
            dropdown.classList.add('show');
        }
    };

    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });        form.addEventListener('submit', function(e) {
            // Limpieza previa
            clearFieldErrors();

            // Validación específica de campos obligatorios
            const validation = validateRequiredFields();
            if (!validation.valid) {
                e.preventDefault();
                mostrarToast(validation.message, 'error');
                
                // Navegar a la primera sección con errores y hacer scroll al primer campo
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    // Encontrar la sección del primer campo con error
                    const tabContent = firstError.closest('.tab-content-modal');
                    if (tabContent) {
                        // Hacer clic en el tab correspondiente
                        const tabId = tabContent.id;
                        const correspondingTab = document.querySelector(`[data-tab="${tabId}"]`);
                        if (correspondingTab) {
                            correspondingTab.click();
                            // Esperar un poco para que se complete la transición del tab
                            setTimeout(() => {
                                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                firstError.focus();
                            }, 100);
                        }
                    } else {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
                return;
            }

            // Validación HTML5 adicional
            if (!form.checkValidity()) {
                e.preventDefault();
                mostrarToast('Por favor revise los campos marcados en rojo.', 'error');
                return;
            }

            // Si el formulario tiene _action=update, lo manejamos por AJAX
            const actionInput = form.querySelector('input[name="_action"]');
            const isUpdate = actionInput && actionInput.value === 'update';
            if (isUpdate) {
                e.preventDefault();
                mostrarToast('Enviando datos al servidor...', 'info');

                // Construir body x-www-form-urlencoded desde FormData
                const fd = new FormData(form);
                const params = new URLSearchParams();
                for (const pair of fd.entries()) { params.append(pair[0], pair[1]); }

                fetch(form.action || 'empleados.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: params.toString(),
                    credentials: 'same-origin'
                }).then(async res => {
                    const txt = await res.text();
                    let json = null;
                    try { 
                        json = JSON.parse(txt); 
                    } catch (e) { 
                        console.error('Respuesta UPDATE no es JSON válido:', txt);
                        console.error('Error de parsing UPDATE:', e);
                    }
                    
                    if (!res.ok) {
                        // Status 400 esperado para errores de validación
                        if (json && json.error) {
                            mostrarToast(json.error, 'error');
                            // Marcar campos si el mensaje lo indica
                            if (json.error.toLowerCase().includes('cédula') || json.error.toLowerCase().includes('cedula')) {
                                showFieldError('cedula', json.error);
                            }
                            if (json.error.toLowerCase().includes('correo') || json.error.toLowerCase().includes('email')) {
                                showFieldError('email', json.error);
                            }
                        } else {
                            mostrarToast('Error del servidor. Intente de nuevo.', 'error');
                        }
                        return;
                    }

                    // res.ok
                    if (json && json.success) {
                        mostrarToast('Empleado actualizado correctamente.', 'success');
                        // Cerrar modal y recargar para reflejar cambios
                        if (typeof cerrarModalEmpleado === 'function') cerrarModalEmpleado();
                        setTimeout(() => window.location.reload(), 700);
                    } else if (json && json.error) {
                        mostrarToast(json.error, 'error');
                        if (json.error.toLowerCase().includes('cédula') || json.error.toLowerCase().includes('cedula')) showFieldError('cedula', json.error);
                        if (json.error.toLowerCase().includes('correo') || json.error.toLowerCase().includes('email')) showFieldError('email', json.error);
                    } else {
                        // Fallback: mostrar texto plano si no es JSON
                        console.error('Respuesta UPDATE inesperada del servidor:', txt);
                        const bodyText = txt || 'Respuesta inesperada del servidor';
                        mostrarToast(bodyText, 'error');
                    }
                }).catch(err => {
                    console.error(err);
                    mostrarToast('Error al comunicarse con el servidor', 'error');
                });
            } else {
                // Crear por AJAX para mantener la misma UX sin recarga
                e.preventDefault();
                mostrarToast('Creando empleado...', 'info');

                const fd = new FormData(form);
                const params = new URLSearchParams();
                for (const pair of fd.entries()) { params.append(pair[0], pair[1]); }

                fetch(form.action || 'empleados.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/x-www-form-urlencoded', 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: params.toString(),
                    credentials: 'same-origin'
                }).then(async res => {
                    const txt = await res.text();
                    let json = null;
                    try { 
                        json = JSON.parse(txt); 
                    } catch (e) { 
                        console.error('Respuesta no es JSON válido:', txt);
                        console.error('Error de parsing:', e);
                    }
                    
                    if (!res.ok) {
                        if (json && json.error) {
                            mostrarToast(json.error, 'error');
                            if (json.error.toLowerCase().includes('cédula') || json.error.toLowerCase().includes('cedula')) showFieldError('cedula', json.error);
                            if (json.error.toLowerCase().includes('correo') || json.error.toLowerCase().includes('email')) showFieldError('email', json.error);
                        } else {
                            mostrarToast('Error al crear el empleado', 'error');
                        }
                        return;
                    }
                    
                    if (json && json.success) {
                        mostrarToast('Empleado creado correctamente', 'success');
                        if (typeof cerrarModalEmpleado === 'function') cerrarModalEmpleado();
                        setTimeout(() => window.location.reload(), 700);
                    } else if (json && json.error) {
                        mostrarToast(json.error, 'error');
                    } else {
                        console.error('Respuesta inesperada del servidor:', txt);
                        mostrarToast('Respuesta inesperada del servidor', 'error');
                    }
                }).catch(err => {
                    console.error(err);
                    mostrarToast('Error al comunicarse con el servidor', 'error');
                });
            }
        });
    }

    // ===============================================
    // 2. LÓGICA DE PESTAÑAS (TABS) - Solo para pestañas NO del modal
    // ===============================================

    const tabButtons = document.querySelectorAll('.tab-button:not(#modal-empleado .tab-button)');
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

    // Solo añadir event listeners a pestañas que NO están en el modal
    tabButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const targetTab = event.target.getAttribute('data-tab');
            if (targetTab) { // Solo si tiene data-tab attribute
                activarPestana(targetTab);
            }
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
    // selector corregido: el formulario usa name="tipo_contrato"
    const inputTipoContrato = document.querySelector('select[name="tipo_contrato"]');
    const inputMesada = document.querySelector('input[name="mesada"]');
    const outputAuxTransporte = document.querySelector('input[name="auxilio_transporte"]');
    
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
            if (outputAuxTransporte) outputAuxTransporte.value = 200000.00; // Valor fijo
        } else {
            if (outputAuxTransporte) outputAuxTransporte.value = 0.00;
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
        // ===============================================
        // Funciones CRUD para botones (ver, editar, eliminar)
        // ===============================================
        function verEmpleado(id) {
            fetch(`empleados.php?get=${id}`)
                .then(r => { if (!r.ok) throw new Error('Empleado no encontrado'); return r.json(); })
                .then(data => {
                    // Rellenar el modal en modo solo lectura
                    document.getElementById('modal-titulo').textContent = 'Ver Empleado';
                    const form = document.getElementById('form-nuevo-empleado');
                    if (!form) return;
                    // Llenar campos básicos; proteger campos que no existan
                    for (const k in data) {
                        const el = form.querySelector(`[name="${k}"]`);
                        if (el) el.value = data[k];
                    }
                    // Abrir modal
                    if (window.abrirModalEmpleado) window.abrirModalEmpleado(id);
                    // Desactivar inputs para solo lectura
                    form.querySelectorAll('input,select,textarea').forEach(i => i.setAttribute('disabled','disabled'));
                }).catch(err => {
                    console.error(err);
                    alert('No se pudo cargar la información del empleado.');
                });
        }

        function editarEmpleado(id) {
            fetch(`empleados.php?get=${id}`)
                .then(r => { if (!r.ok) throw new Error('Empleado no encontrado'); return r.json(); })
                .then(data => {
                    document.getElementById('modal-titulo').textContent = 'Editar Empleado';
                    const form = document.getElementById('form-nuevo-empleado');
                    if (!form) return;
                    for (const k in data) {
                        const el = form.querySelector(`[name="${k}"]`);
                        if (el) {
                            el.removeAttribute('disabled');
                            el.value = data[k];
                        }
                    }
                    // Añadir un campo hidden id para el update
                    let hidden = form.querySelector('input[name="id"]');
                    if (!hidden) {
                        hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = 'id'; hidden.value = id; form.appendChild(hidden);
                    } else { hidden.value = id; }
                    // Asegurar que al enviar el formulario incluya _action=update
                    let actionInput = form.querySelector('input[name="_action"]');
                    if (!actionInput) { actionInput = document.createElement('input'); actionInput.type='hidden'; actionInput.name='_action'; actionInput.value='update'; form.appendChild(actionInput); }
                    if (window.abrirModalEmpleado) window.abrirModalEmpleado(id);
                }).catch(err => { console.error(err); alert('No se pudo cargar la información para editar.'); });
        }

        function eliminarEmpleado(id) {
            if (!confirm('¿Seguro que desea eliminar este empleado? Esta acción no se puede deshacer.')) return;
            // Enviar POST con _action=delete
            fetch('empleados.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `_action=delete&id=${encodeURIComponent(id)}`
            }).then(r => r.json()).then(resp => {
                if (resp && resp.success) {
                    alert('Empleado eliminado');
                    // Recargar la página para actualizar la lista
                    window.location.reload();
                } else {
                    alert('No se pudo eliminar el empleado');
                    console.error(resp);
                }
            }).catch(err => { console.error(err); alert('Error al comunicarse con el servidor'); });
        }