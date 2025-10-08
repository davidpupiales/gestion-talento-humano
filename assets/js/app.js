// ===== SISTEMA HRMS - JAVASCRIPT PRINCIPAL =====

// Variables globales
let sidebarOpen = false

// Inicialización cuando el DOM está listo
document.addEventListener("DOMContentLoaded", () => {
  initializeApp()
})

// Función principal de inicialización
function initializeApp() {
  setupMobileMenu()
  setupTooltips()
  setupFormValidation()
  console.log("[HRMS] Sistema inicializado correctamente")
}

// ===== MENÚ MÓVIL =====
function setupMobileMenu() {
  // Crear botón de menú móvil si no existe
  if (!document.querySelector(".mobile-menu-toggle")) {
    const headerActions = document.querySelector(".header-actions")
    if (headerActions) {
      const mobileToggle = document.createElement("button")
      mobileToggle.className = "mobile-menu-toggle"
      mobileToggle.innerHTML = '<i class="fas fa-bars"></i>'
      mobileToggle.onclick = toggleMobileMenu
      headerActions.insertBefore(mobileToggle, headerActions.firstChild)
    }
  }

  // Crear overlay si no existe
  if (!document.querySelector(".sidebar-overlay")) {
    const overlay = document.createElement("div")
    overlay.className = "sidebar-overlay"
    overlay.onclick = closeMobileMenu
    document.body.appendChild(overlay)
  }

  // Asegurar que el sidebar esté visible en pantallas de escritorio
  function ensureDesktopSidebar() {
    const sidebar = document.querySelector(".sidebar")
    if (sidebar && window.innerWidth > 768) {
      sidebar.classList.remove("mobile-open")
      sidebar.style.transform = ""
      sidebarOpen = false
    }
  }

  // Ejecutar inmediatamente
  ensureDesktopSidebar()

  // Cerrar menú al cambiar tamaño de ventana
  window.addEventListener("resize", () => {
    if (window.innerWidth > 768 && sidebarOpen) {
      closeMobileMenu()
    }
    ensureDesktopSidebar()
  })
}

function toggleMobileMenu() {
  const sidebar = document.querySelector(".sidebar")
  const overlay = document.querySelector(".sidebar-overlay")

  if (sidebarOpen) {
    closeMobileMenu()
  } else {
    openMobileMenu()
  }
}

function openMobileMenu() {
  const sidebar = document.querySelector(".sidebar")
  const overlay = document.querySelector(".sidebar-overlay")

  sidebar.classList.add("mobile-open")
  overlay.classList.add("show")
  sidebarOpen = true
  document.body.style.overflow = "hidden"
}

function closeMobileMenu() {
  const sidebar = document.querySelector(".sidebar")
  const overlay = document.querySelector(".sidebar-overlay")

  sidebar.classList.remove("mobile-open")
  overlay.classList.remove("show")
  sidebarOpen = false
  document.body.style.overflow = ""
}

// ===== SISTEMA DE PESTAÑAS =====
function cambiarPestana(pestana, boton) {
  // Ocultar todas las pestañas
  document.querySelectorAll(".tab-content").forEach((tab) => {
    tab.classList.remove("active")
  })

  // Desactivar todos los botones
  document.querySelectorAll(".tab-button").forEach((btn) => {
    btn.classList.remove("active")
  })

  // Mostrar pestaña seleccionada
  const tabContent = document.getElementById("tab-" + pestana)
  if (tabContent) {
    tabContent.classList.add("active")
  }

  // Activar botón
  if (boton) {
    boton.classList.add("active")
  }
}

// Función para forzar estilos correctos en notificaciones
function forceNotificationStyles() {
  // Observer para detectar nuevos elementos toast
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'childList') {
        mutation.addedNodes.forEach(function(node) {
          if (node.nodeType === 1) { // Element node
            // Buscar toasts en el nuevo nodo
            const toasts = node.querySelectorAll ? node.querySelectorAll('[class*="toast"], [class*="notification"], [class*="alert"]') : [];
            const isToast = node.className && (
              node.className.includes('toast') || 
              node.className.includes('notification') || 
              node.className.includes('alert')
            );
            
            if (isToast) {
              forceToastStyle(node);
            }
            
            toasts.forEach(forceToastStyle);
          }
        });
      }
    });
  });
  
  // Observar cambios en el body
  observer.observe(document.body, { childList: true, subtree: true });
  
  // Aplicar estilos a toasts existentes
  document.querySelectorAll('[class*="toast"], [class*="notification"], [class*="alert"]').forEach(forceToastStyle);
}

function forceToastStyle(element) {
  const className = element.className.toLowerCase();
  
  let config = {
    bg: 'linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%)',
    color: '#0c4a6e',
    borderLeft: '#06b6d4'
  };
  
  if (className.includes('success')) {
    config = {
      bg: 'linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%)',
      color: '#065f46',
      borderLeft: '#10b981'
    };
  } else if (className.includes('error') || className.includes('danger')) {
    config = {
      bg: 'linear-gradient(135deg, #fecaca 0%, #fca5a5 100%)',
      color: '#7f1d1d',
      borderLeft: '#ef4444'
    };
  } else if (className.includes('warning')) {
    config = {
      bg: 'linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)',
      color: '#92400e',
      borderLeft: '#f59e0b'
    };
  }
  
  // Aplicar estilos forzadamente
  element.style.setProperty('background', config.bg, 'important');
  element.style.setProperty('color', config.color, 'important');
  element.style.setProperty('border-left', `4px solid ${config.borderLeft}`, 'important');
  element.style.setProperty('border-radius', '8px', 'important');
  element.style.setProperty('box-shadow', '0 10px 25px rgba(0, 0, 0, 0.15)', 'important');
  element.style.setProperty('font-weight', '500', 'important');
  element.style.setProperty('font-size', '0.9rem', 'important');
  element.style.setProperty('line-height', '1.5', 'important');
  element.style.setProperty('padding', '12px 16px', 'important');
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', forceNotificationStyles);

// ===== SISTEMA DE TOAST/ALERTAS =====
function mostrarToast(mensaje, tipo = "info", duracion = 3000) {
  // Crear toast directamente sin contenedor
  const toast = document.createElement("div")
  toast.className = `toast toast-${tipo}`
  
  // Definir colores según el tipo
  const colorConfig = {
    success: {
      bg: 'linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%)',
      border: '#a7f3d0',
      borderLeft: '#10b981',
      color: '#065f46',
      iconColor: '#10b981'
    },
    error: {
      bg: 'linear-gradient(135deg, #fecaca 0%, #fca5a5 100%)',
      border: '#fca5a5',
      borderLeft: '#ef4444',
      color: '#7f1d1d',
      iconColor: '#ef4444'
    },
    warning: {
      bg: 'linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)',
      border: '#fde68a',
      borderLeft: '#f59e0b',
      color: '#92400e',
      iconColor: '#f59e0b'
    },
    info: {
      bg: 'linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%)',
      border: '#a5f3fc',
      borderLeft: '#06b6d4',
      color: '#0c4a6e',
      iconColor: '#06b6d4'
    }
  }
  
  const config = colorConfig[tipo] || colorConfig.info
  
  // Posición fija directa en el toast
  toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: ${config.bg};
        border: 1px solid ${config.border};
        border-left: 4px solid ${config.borderLeft};
        color: ${config.color};
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        min-width: 300px;
        max-width: 400px;
        transform: translateX(100%);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        font-size: 0.9rem;
        line-height: 1.5;
    `

  const icon =
    tipo === "success"
      ? "check-circle"
      : tipo === "error"
        ? "exclamation-circle"
        : tipo === "warning"
          ? "exclamation-triangle"
          : "info-circle"

  toast.innerHTML = `
        <i class="fas fa-${icon}" style="color: ${config.iconColor}; flex-shrink: 0;"></i>
        <span style="flex: 1; color: ${config.color};">${mensaje}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: ${config.color}; cursor: pointer; padding: 4px; opacity: 0.7; border-radius: 4px;">
            <i class="fas fa-times"></i>
        </button>
    `

  // Añadir directamente al body
  document.body.appendChild(toast)

  // Animar entrada
  setTimeout(() => {
    toast.style.transform = "translateX(0)"
  }, 10)

  // Auto-remover
  setTimeout(() => {
    toast.style.transform = "translateX(100%)"
    setTimeout(() => {
      if (toast.parentNode) {
        toast.remove()
      }
    }, 300)
  }, duracion)
}

// ===== VALIDACIÓN DE FORMULARIOS =====
function setupFormValidation() {
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!validateForm(this)) {
        e.preventDefault()
      }
    })
  })
}

function validateForm(form) {
  const requiredFields = form.querySelectorAll("[required]")
  let isValid = true

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      field.style.borderColor = "var(--accent-red)"
      isValid = false
    } else {
      field.style.borderColor = "var(--border-primary)"
    }
  })

  if (!isValid) {
    mostrarToast("Por favor complete todos los campos obligatorios", "error")
  }

  return isValid
}

// ===== TOOLTIPS =====
function setupTooltips() {
  const elementsWithTitle = document.querySelectorAll("[title]")
  elementsWithTitle.forEach((element) => {
    element.addEventListener("mouseenter", showTooltip)
    element.addEventListener("mouseleave", hideTooltip)
  })
}

function showTooltip(e) {
  const tooltip = document.createElement("div")
  tooltip.className = "tooltip"
  tooltip.textContent = e.target.getAttribute("title")
  tooltip.style.cssText = `
        position: absolute;
        background: var(--bg-tertiary);
        color: var(--text-primary);
        padding: 8px 12px;
        border-radius: var(--radius-md);
        font-size: var(--font-size-xs);
        box-shadow: var(--shadow-lg);
        z-index: 10000;
        pointer-events: none;
        white-space: nowrap;
    `

  document.body.appendChild(tooltip)

  const rect = e.target.getBoundingClientRect()
  tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + "px"
  tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + "px"

  e.target.removeAttribute("title")
  e.target.setAttribute("data-original-title", tooltip.textContent)
}

function hideTooltip(e) {
  const tooltip = document.querySelector(".tooltip")
  if (tooltip) {
    tooltip.remove()
  }

  const originalTitle = e.target.getAttribute("data-original-title")
  if (originalTitle) {
    e.target.setAttribute("title", originalTitle)
    e.target.removeAttribute("data-original-title")
  }
}

// ===== UTILIDADES =====
function formatearFecha(fecha) {
  return new Date(fecha).toLocaleDateString("es-ES", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  })
}

function formatearMoneda(cantidad) {
  return new Intl.NumberFormat("es-ES", {
    style: "currency",
    currency: "EUR",
  }).format(cantidad)
}

// Exportar funciones globales
window.cambiarPestana = cambiarPestana
window.mostrarToast = mostrarToast
window.toggleMobileMenu = toggleMobileMenu
window.formatearFecha = formatearFecha
window.formatearMoneda = formatearMoneda
