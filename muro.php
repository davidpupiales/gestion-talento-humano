<?php
require_once 'includes/header.php';

// Datos mock para el muro empresarial
$publicaciones = [
    [
        'id' => 1,
        'autor' => 'Administrador Sistema',
        'rol' => 'administrador',
        'avatar' => 'A',
        'fecha' => '2024-01-15 10:30:00',
        'tipo' => 'anuncio',
        'titulo' => 'Nueva Política de Trabajo Remoto',
        'contenido' => 'Se ha actualizado la política de trabajo remoto. Todos los empleados pueden trabajar desde casa hasta 3 días por semana previa coordinación con su supervisor.',
        'archivo' => 'politica_trabajo_remoto.pdf',
        'likes' => 24,
        'comentarios' => 8
    ],
    [
        'id' => 2,
        'autor' => 'María González',
        'rol' => 'gerente',
        'avatar' => 'M',
        'fecha' => '2024-01-14 15:45:00',
        'tipo' => 'evento',
        'titulo' => 'Capacitación en Seguridad Laboral',
        'contenido' => 'Recordatorio: La capacitación obligatoria en seguridad laboral será el próximo viernes 19 de enero a las 2:00 PM en el auditorio principal.',
        'archivo' => null,
        'likes' => 18,
        'comentarios' => 5
    ],
    [
        'id' => 3,
        'autor' => 'Juan Pérez',
        'rol' => 'empleado',
        'avatar' => 'J',
        'fecha' => '2024-01-13 09:15:00',
        'tipo' => 'felicitacion',
        'titulo' => 'Felicitaciones al Equipo de Desarrollo',
        'contenido' => '¡Excelente trabajo en el lanzamiento del nuevo sistema! El esfuerzo y dedicación de todo el equipo ha sido excepcional.',
        'archivo' => null,
        'likes' => 32,
        'comentarios' => 12
    ]
];

$comentarios_mock = [
    1 => [
        ['autor' => 'Carlos López', 'comentario' => 'Excelente noticia, esto mejorará mucho nuestro balance trabajo-vida.', 'fecha' => '2024-01-15 11:00:00'],
        ['autor' => 'Ana Martínez', 'comentario' => '¿Necesitamos algún software específico para el trabajo remoto?', 'fecha' => '2024-01-15 11:30:00']
    ],
    2 => [
        ['autor' => 'Pedro Sánchez', 'comentario' => 'Perfecto, ya tengo agendado en mi calendario.', 'fecha' => '2024-01-14 16:00:00']
    ]
];
?>

<div class="fade-in">
     Formulario para Nueva Publicación (Solo Admin) 
    <?php if (SessionManager::tienePermiso('administrador')): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nueva Publicación</h3>
        </div>
        <form id="nueva-publicacion">
            <div class="form-group">
                <label for="tipo-publicacion" class="form-label">Tipo de Publicación</label>
                <select id="tipo-publicacion" class="form-control">
                    <option value="anuncio">Anuncio General</option>
                    <option value="evento">Evento</option>
                    <option value="politica">Política/Procedimiento</option>
                    <option value="felicitacion">Felicitación</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="titulo-publicacion" class="form-label">Título</label>
                <input type="text" id="titulo-publicacion" class="form-control" placeholder="Título de la publicación">
            </div>
            
            <div class="form-group">
                <label for="contenido-publicacion" class="form-label">Contenido</label>
                <textarea id="contenido-publicacion" class="form-control" rows="4" placeholder="Escribe el contenido de la publicación..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="archivo-publicacion" class="form-label">Archivo Adjunto (Opcional)</label>
                <input type="file" id="archivo-publicacion" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Publicar
                </button>
                <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
     Filtros 
    <div class="card">
        <div style="display: flex; gap: 1rem; align-items: center;">
            <label style="color: var(--text-secondary);">Filtrar por:</label>
            <select class="form-control" style="width: auto;">
                <option value="todos">Todas las publicaciones</option>
                <option value="anuncio">Anuncios</option>
                <option value="evento">Eventos</option>
                <option value="politica">Políticas</option>
                <option value="felicitacion">Felicitaciones</option>
            </select>
            
            <select class="form-control" style="width: auto;">
                <option value="recientes">Más recientes</option>
                <option value="antiguos">Más antiguos</option>
                <option value="populares">Más populares</option>
            </select>
        </div>
    </div>
    
     Lista de Publicaciones 
    <div id="lista-publicaciones">
        <?php foreach ($publicaciones as $pub): ?>
        <div class="wall-post">
            <div class="post-header">
                <div class="post-avatar" style="background: <?php 
                    echo $pub['rol'] === 'administrador' ? 'var(--danger)' : 
                        ($pub['rol'] === 'gerente' ? 'var(--accent-blue)' : 'var(--accent-green)'); 
                ?>">
                    <?php echo $pub['avatar']; ?>
                </div>
                <div class="post-meta">
                    <div class="post-author">
                        <?php echo htmlspecialchars($pub['autor']); ?>
                        <span class="badge badge-<?php 
                            echo $pub['rol'] === 'administrador' ? 'danger' : 
                                ($pub['rol'] === 'gerente' ? 'info' : 'success'); 
                        ?>" style="margin-left: 0.5rem; font-size: 0.7rem;">
                            <?php echo ucfirst($pub['rol']); ?>
                        </span>
                    </div>
                    <div class="post-time">
                        <?php echo date('d/m/Y H:i', strtotime($pub['fecha'])); ?>
                    </div>
                </div>
                <div style="margin-left: auto;">
                    <span class="badge badge-<?php 
                        echo $pub['tipo'] === 'anuncio' ? 'info' : 
                            ($pub['tipo'] === 'evento' ? 'warning' : 
                            ($pub['tipo'] === 'politica' ? 'danger' : 'success')); 
                    ?>">
                        <?php echo ucfirst($pub['tipo']); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($pub['titulo']): ?>
            <h4 style="color: var(--text-primary); margin-bottom: 1rem; font-size: 1.1rem;">
                <?php echo htmlspecialchars($pub['titulo']); ?>
            </h4>
            <?php endif; ?>
            
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($pub['contenido'])); ?>
            </div>
            
            <?php if ($pub['archivo']): ?>
            <div style="background: var(--secondary-bg); padding: 1rem; border-radius: var(--radius); margin: 1rem 0;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-file-pdf" style="color: var(--danger);"></i>
                    <span><?php echo htmlspecialchars($pub['archivo']); ?></span>
                    <a href="#" class="btn btn-secondary" style="margin-left: auto; padding: 0.5rem 1rem;">
                        <i class="fas fa-download"></i> Descargar
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="post-actions">
                <button class="post-action" onclick="darLike(<?php echo $pub['id']; ?>)">
                    <i class="fas fa-heart"></i>
                    <span id="likes-<?php echo $pub['id']; ?>"><?php echo $pub['likes']; ?></span>
                </button>
                
                <button class="post-action" onclick="toggleComentarios(<?php echo $pub['id']; ?>)">
                    <i class="fas fa-comment"></i>
                    <?php echo $pub['comentarios']; ?> comentarios
                </button>
                
                <button class="post-action">
                    <i class="fas fa-share"></i>
                    Compartir
                </button>
            </div>
            
             Sección de Comentarios 
            <div id="comentarios-<?php echo $pub['id']; ?>" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                 Comentarios existentes 
                <?php if (isset($comentarios_mock[$pub['id']])): ?>
                    <?php foreach ($comentarios_mock[$pub['id']] as $comentario): ?>
                    <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
                        <div style="width: 32px; height: 32px; background: var(--accent-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; font-weight: 600;">
                            <?php echo strtoupper(substr($comentario['autor'], 0, 1)); ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="background: var(--secondary-bg); padding: 0.75rem; border-radius: var(--radius);">
                                <div style="font-weight: 600; margin-bottom: 0.25rem; font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($comentario['autor']); ?>
                                </div>
                                <div style="font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($comentario['comentario']); ?>
                                </div>
                            </div>
                            <div style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">
                                <?php echo date('d/m/Y H:i', strtotime($comentario['fecha'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                 Formulario para nuevo comentario 
                <div style="display: flex; gap: 0.75rem;">
                    <div style="width: 32px; height: 32px; background: var(--accent-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                        <?php echo strtoupper(substr($usuario_actual['nombre'], 0, 1)); ?>
                    </div>
                    <div style="flex: 1;">
                        <textarea class="form-control" rows="2" placeholder="Escribe un comentario..." style="margin-bottom: 0.5rem;"></textarea>
                        <button class="btn btn-primary" style="padding: 0.5rem 1rem;">
                            <i class="fas fa-paper-plane"></i> Comentar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Funciones para el muro empresarial
function darLike(publicacionId) {
    const likesElement = document.getElementById(`likes-${publicacionId}`);
    const currentLikes = parseInt(likesElement.textContent);
    likesElement.textContent = currentLikes + 1;
    
    // Aquí iría la llamada AJAX para guardar el like
    mostrarToast('¡Like agregado!', 'success');
}

function toggleComentarios(publicacionId) {
    const comentariosDiv = document.getElementById(`comentarios-${publicacionId}`);
    comentariosDiv.style.display = comentariosDiv.style.display === 'none' ? 'block' : 'none';
}

function limpiarFormulario() {
    document.getElementById('nueva-publicacion').reset();
}

// Manejar envío de nueva publicación
document.getElementById('nueva-publicacion')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const titulo = document.getElementById('titulo-publicacion').value;
    const contenido = document.getElementById('contenido-publicacion').value;
    
    if (!titulo || !contenido) {
        mostrarToast('Por favor complete todos los campos obligatorios', 'error');
        return;
    }
    
    // Aquí iría la lógica para guardar la publicación
    mostrarToast('Publicación creada exitosamente', 'success');
    limpiarFormulario();
});
</script>

<?php require_once 'includes/footer.php'; ?>
