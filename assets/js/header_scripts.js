document.addEventListener('DOMContentLoaded', function() {
    const notificationPanel = document.querySelector('.notifications-panel');
    const notificationTrigger = document.querySelector('.notification-trigger');

    // Función para mostrar/ocultar el panel de notificaciones
    function toggleNotificationsPanel() {
        notificationPanel.classList.toggle('active');
    }

    // Ocultar el panel de notificaciones si se hace clic fuera de él
    function closeNotificationsPanel(event) {
        if (!notificationPanel.contains(event.target) && notificationPanel.classList.contains('active')) {
            notificationPanel.classList.remove('active');
        }
    }

    // Agregar eventos de clic
    notificationTrigger.addEventListener('click', function(event) {
        event.stopPropagation(); // Evita que el clic se propague al documento
        toggleNotificationsPanel();
    });

    // Agregar el evento de clic al documento para cerrar el panel
    document.addEventListener('click', closeNotificationsPanel);
});