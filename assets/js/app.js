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
  setupNotifications()
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

  // Cerrar menú al cambiar tamaño de ventana
  window.addEventListener("resize", () => {
    if (window.innerWidth > 768 && sidebarOpen) {
      closeMobileMenu()
    }
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

// ===== SISTEMA DE NOTIFICACIONES =====
function setupNotifications() {
  const notificationTrigger = document.querySelector(".notification-trigger")
  if (notificationTrigger) {
    notificationTrigger.addEventListener("click", toggleNotifications)
  }

  // Cerrar notificaciones al hacer clic fuera
  document.addEventListener("click", (e) => {
    const dropdown = document.querySelector(".notifications-dropdown")
    const trigger = document.querySelector(".notification-trigger")

    if (
      dropdown &&
      dropdown.classList.contains("show") &&
      !dropdown.contains(e.target) &&
      !trigger.contains(e.target)
    ) {
      dropdown.classList.remove("show")
    }
  })
}

function toggleNotifications() {
  const dropdown = document.querySelector(".notifications-dropdown")
  if (dropdown) {
    dropdown.classList.toggle("show")
  }
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

// ===== SISTEMA DE TOAST/ALERTAS =====
function mostrarToast(mensaje, tipo = "info", duracion = 3000) {
  // Crear contenedor de toasts si no existe
  let container = document.querySelector(".toast-container")
  if (!container) {
    container = document.createElement("div")
    container.className = "toast-container"
    container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `
    document.body.appendChild(container)
  }

  // Crear toast
  const toast = document.createElement("div")
  toast.className = `toast toast-${tipo}`
  toast.style.cssText = `
        background: var(--bg-secondary);
        border: 1px solid var(--border-primary);
        border-left: 4px solid var(--accent-${tipo === "success" ? "green" : tipo === "error" ? "red" : tipo === "warning" ? "orange" : "blue"});
        color: var(--text-primary);
        padding: 16px 20px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        min-width: 300px;
        max-width: 400px;
        transform: translateX(100%);
        transition: all var(--transition-normal);
        display: flex;
        align-items: center;
        gap: 12px;
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
        <i class="fas fa-${icon}" style="color: var(--accent-${tipo === "success" ? "green" : tipo === "error" ? "red" : tipo === "warning" ? "orange" : "blue"});"></i>
        <span style="flex: 1;">${mensaje}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
            <i class="fas fa-times"></i>
        </button>
    `

  container.appendChild(toast)

  // Animar entrada
  setTimeout(() => {
    toast.style.transform = "translateX(0)"
  }, 10)

  // Auto-remover
  setTimeout(() => {
    toast.style.transform = "translateX(100%)"
    setTimeout(() => {
      if (toast.parentElement) {
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
