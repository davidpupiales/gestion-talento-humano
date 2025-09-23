/**
 * JavaScript Principal del Sistema HRMS
 * Funciones globales y utilidades
 */

// Configuración global
const HRMS = {
  baseUrl: window.location.origin,
  usuario: null,
  notificaciones: [],

  // Inicializar aplicación
  init: function () {
    this.cargarUsuario()
    this.configurarEventos()
    this.verificarNotificaciones()
  },

  // Cargar información del usuario actual
  cargarUsuario: function () {
    // En una implementación real, esto vendría del servidor
    this.usuario = {
      id: 1,
      nombre: "Usuario Actual",
      rol: "empleado",
    }
  },

  // Configurar eventos globales
  configurarEventos: () => {
    // Manejar clicks en notificaciones
    document.addEventListener("click", (e) => {
      if (e.target.closest(".notifications")) {
        HRMS.mostrarNotificaciones()
      }
    })

    // Auto-guardar formularios cada 30 segundos
    setInterval(() => {
      HRMS.autoGuardarFormularios()
    }, 30000)
  },

  // Verificar notificaciones pendientes
  verificarNotificaciones: function () {
    // Simulación de notificaciones
    const notificaciones = [
      {
        id: 1,
        tipo: "documento",
        titulo: "Documento pendiente de firma",
        mensaje: "Tienes 2 documentos pendientes de firma",
        fecha: new Date(),
        leida: false,
      },
      {
        id: 2,
        tipo: "permiso",
        titulo: "Solicitud aprobada",
        mensaje: "Tu solicitud de permiso ha sido aprobada",
        fecha: new Date(Date.now() - 86400000),
        leida: false,
      },
      {
        id: 3,
        tipo: "capacitacion",
        titulo: "Nueva capacitación disponible",
        mensaje: "Capacitación en Seguridad Laboral disponible",
        fecha: new Date(Date.now() - 172800000),
        leida: true,
      },
    ]

    this.notificaciones = notificaciones
    this.actualizarContadorNotificaciones()
  },

  // Actualizar contador de notificaciones
  actualizarContadorNotificaciones: function () {
    const noLeidas = this.notificaciones.filter((n) => !n.leida).length
    const badge = document.querySelector(".notification-badge")
    if (badge) {
      badge.textContent = noLeidas
      badge.style.display = noLeidas > 0 ? "flex" : "none"
    }
  },

  // Mostrar panel de notificaciones
  mostrarNotificaciones: function () {
    // Crear panel de notificaciones si no existe
    let panel = document.getElementById("panel-notificaciones")
    if (!panel) {
      panel = this.crearPanelNotificaciones()
    }

    // Toggle visibility
    panel.style.display = panel.style.display === "block" ? "none" : "block"
  },

  // Crear panel de notificaciones
  crearPanelNotificaciones: function () {
    const panel = document.createElement("div")
    panel.id = "panel-notificaciones"
    panel.style.cssText = `
            position: fixed;
            top: 70px;
            right: 20px;
            width: 350px;
            max-height: 500px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            z-index: 1000;
            display: none;
            overflow-y: auto;
        `

    // Header del panel
    const header = document.createElement("div")
    header.style.cssText = `
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        `
    header.innerHTML = `
            <h4 style="margin: 0; color: var(--text-primary);">Notificaciones</h4>
            <button onclick="HRMS.marcarTodasLeidas()" style="background: none; border: none; color: var(--accent-green); cursor: pointer; font-size: 0.875rem;">
                Marcar todas como leídas
            </button>
        `

    // Lista de notificaciones
    const lista = document.createElement("div")
    lista.id = "lista-notificaciones"

    this.notificaciones.forEach((notif) => {
      const item = document.createElement("div")
      item.style.cssText = `
                padding: 1rem;
                border-bottom: 1px solid var(--border-color);
                cursor: pointer;
                transition: background 0.2s;
                ${!notif.leida ? "background: rgba(0, 212, 170, 0.1);" : ""}
            `

      item.innerHTML = `
                <div style="display: flex; align-items: start; gap: 0.75rem;">
                    <div style="width: 8px; height: 8px; background: ${!notif.leida ? "var(--accent-green)" : "transparent"}; border-radius: 50%; margin-top: 0.5rem;"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">
                            ${notif.titulo}
                        </div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">
                            ${notif.mensaje}
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.75rem;">
                            ${this.formatearFecha(notif.fecha)}
                        </div>
                    </div>
                </div>
            `

      item.addEventListener("click", () => {
        this.marcarNotificacionLeida(notif.id)
        this.abrirNotificacion(notif)
      })

      lista.appendChild(item)
    })

    panel.appendChild(header)
    panel.appendChild(lista)
    document.body.appendChild(panel)

    // Cerrar al hacer click fuera
    document.addEventListener("click", (e) => {
      if (!panel.contains(e.target) && !e.target.closest(".notifications")) {
        panel.style.display = "none"
      }
    })

    return panel
  },

  // Marcar notificación como leída
  marcarNotificacionLeida: function (notifId) {
    const notif = this.notificaciones.find((n) => n.id === notifId)
    if (notif) {
      notif.leida = true
      this.actualizarContadorNotificaciones()
    }
  },

  // Marcar todas las notificaciones como leídas
  marcarTodasLeidas: function () {
    this.notificaciones.forEach((n) => (n.leida = true))
    this.actualizarContadorNotificaciones()

    // Actualizar UI del panel
    const panel = document.getElementById("panel-notificaciones")
    if (panel) {
      panel.remove()
    }

    window.mostrarToast("Todas las notificaciones marcadas como leídas", "success")
  },

  // Abrir notificación específica
  abrirNotificacion: (notif) => {
    const rutas = {
      documento: "documentos.php",
      permiso: "permisos.php",
      capacitacion: "capacitacion.php",
    }

    const ruta = rutas[notif.tipo]
    if (ruta) {
      window.location.href = ruta
    }
  },

  // Formatear fecha para mostrar
  formatearFecha: (fecha) => {
    const ahora = new Date()
    const diff = ahora - fecha
    const minutos = Math.floor(diff / 60000)
    const horas = Math.floor(diff / 3600000)
    const dias = Math.floor(diff / 86400000)

    if (minutos < 1) return "Ahora"
    if (minutos < 60) return `Hace ${minutos} min`
    if (horas < 24) return `Hace ${horas} h`
    if (dias < 7) return `Hace ${dias} días`

    return fecha.toLocaleDateString("es-ES")
  },

  // Auto-guardar formularios
  autoGuardarFormularios: () => {
    const formularios = document.querySelectorAll('form[data-autosave="true"]')
    formularios.forEach((form) => {
      const formData = new FormData(form)
      const data = Object.fromEntries(formData)

      // Guardar en localStorage como backup
      localStorage.setItem(`autosave_${form.id}`, JSON.stringify(data))
    })
  },

  // Restaurar datos de formulario
  restaurarFormulario: (formId) => {
    const data = localStorage.getItem(`autosave_${formId}`)
    if (data) {
      const formData = JSON.parse(data)
      const form = document.getElementById(formId)

      Object.keys(formData).forEach((key) => {
        const input = form.querySelector(`[name="${key}"]`)
        if (input) {
          input.value = formData[key]
        }
      })
    }
  },
}

// Utilidades globales
const Utils = {
  // Validar email
  validarEmail: (email) => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return regex.test(email)
  },

  // Validar cédula (formato básico)
  validarCedula: (cedula) => /^\d{8,10}$/.test(cedula),

  // Formatear moneda
  formatearMoneda: (cantidad) =>
    new Intl.NumberFormat("es-ES", {
      style: "currency",
      currency: "USD",
    }).format(cantidad),

  // Formatear fecha
  formatearFecha: (fecha, formato = "dd/mm/yyyy") => {
    const date = new Date(fecha)
    const dia = String(date.getDate()).padStart(2, "0")
    const mes = String(date.getMonth() + 1).padStart(2, "0")
    const año = date.getFullYear()

    switch (formato) {
      case "dd/mm/yyyy":
        return `${dia}/${mes}/${año}`
      case "yyyy-mm-dd":
        return `${año}-${mes}-${dia}`
      default:
        return date.toLocaleDateString("es-ES")
    }
  },

  // Debounce para búsquedas
  debounce: (func, wait) => {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  },
}

// Función para mostrar toast
function mostrarToast(mensaje, tipo) {
  const toast = document.createElement("div")
  toast.className = `toast ${tipo}`
  toast.textContent = mensaje
  document.body.appendChild(toast)

  setTimeout(() => {
    toast.remove()
  }, 3000)
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  HRMS.init()
})

// Exportar para uso global
window.HRMS = HRMS
window.Utils = Utils
