-- Script de Creación de Base de Datos HRMS
-- Sistema de Gestión de Recursos Humanos
-- Versión: 1.0

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS hrms_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hrms_system;

-- Tabla de usuarios del sistema
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    rol ENUM('empleado', 'gerente', 'administrador') NOT NULL DEFAULT 'empleado',
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de empleados
CREATE TABLE empleados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    usuario_id INT,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    fecha_nacimiento DATE,
    genero ENUM('masculino', 'femenino', 'otro'),
    estado_civil ENUM('soltero', 'casado', 'divorciado', 'viudo'),
    departamento VARCHAR(50) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    fecha_salida DATE NULL,
    salario_base DECIMAL(10,2) NOT NULL,
    estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    foto VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla de firmas electrónicas
CREATE TABLE firmas_electronicas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    archivo_firma VARCHAR(255) NOT NULL,
    fecha_carga TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE
);

-- Tabla de documentos
CREATE TABLE documentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    archivo VARCHAR(255) NOT NULL,
    tipo ENUM('contrato', 'politica', 'circular', 'manual', 'otro') NOT NULL,
    creado_por INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
);

-- Tabla de envío de documentos masivos
CREATE TABLE documentos_envios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    documento_id INT NOT NULL,
    enviado_por INT NOT NULL,
    destinatarios JSON NOT NULL, -- IDs de empleados destinatarios
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mensaje TEXT,
    FOREIGN KEY (documento_id) REFERENCES documentos(id) ON DELETE CASCADE,
    FOREIGN KEY (enviado_por) REFERENCES usuarios(id)
);

-- Tabla de firmas de documentos
CREATE TABLE documentos_firmas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    documento_id INT NOT NULL,
    empleado_id INT NOT NULL,
    firma_electronica_id INT NOT NULL,
    fecha_firma TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'firmado', 'rechazado') DEFAULT 'pendiente',
    comentarios TEXT,
    FOREIGN KEY (documento_id) REFERENCES documentos(id) ON DELETE CASCADE,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE,
    FOREIGN KEY (firma_electronica_id) REFERENCES firmas_electronicas(id),
    UNIQUE KEY unique_documento_empleado (documento_id, empleado_id)
);

-- Tabla de nómina
CREATE TABLE nomina (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    periodo_inicio DATE NOT NULL,
    periodo_fin DATE NOT NULL,
    salario_base DECIMAL(10,2) NOT NULL,
    bonificaciones DECIMAL(10,2) DEFAULT 0,
    horas_extras DECIMAL(10,2) DEFAULT 0,
    deducciones DECIMAL(10,2) DEFAULT 0,
    prestamos DECIMAL(10,2) DEFAULT 0,
    total_devengado DECIMAL(10,2) NOT NULL,
    total_deducciones DECIMAL(10,2) NOT NULL,
    salario_neto DECIMAL(10,2) NOT NULL,
    estado ENUM('borrador', 'procesada', 'pagada') DEFAULT 'borrador',
    fecha_procesamiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    procesada_por INT,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE,
    FOREIGN KEY (procesada_por) REFERENCES usuarios(id)
);

-- Tabla de vacantes
CREATE TABLE vacantes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    departamento VARCHAR(50) NOT NULL,
    requisitos TEXT,
    salario_min DECIMAL(10,2),
    salario_max DECIMAL(10,2),
    fecha_publicacion DATE NOT NULL,
    fecha_cierre DATE,
    estado ENUM('abierta', 'en_proceso', 'cerrada') DEFAULT 'abierta',
    creada_por INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creada_por) REFERENCES usuarios(id)
);

-- Tabla de candidatos
CREATE TABLE candidatos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vacante_id INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    cv_archivo VARCHAR(255),
    estado ENUM('aplicado', 'en_revision', 'entrevista', 'rechazado', 'contratado') DEFAULT 'aplicado',
    notas TEXT,
    fecha_aplicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vacante_id) REFERENCES vacantes(id) ON DELETE CASCADE
);

-- Tabla de capacitaciones
CREATE TABLE capacitaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    instructor VARCHAR(100),
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    duracion_horas INT NOT NULL,
    modalidad ENUM('presencial', 'virtual', 'mixta') NOT NULL,
    cupos_disponibles INT DEFAULT 0,
    estado ENUM('programada', 'en_curso', 'completada', 'cancelada') DEFAULT 'programada',
    creada_por INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creada_por) REFERENCES usuarios(id)
);

-- Tabla de inscripciones a capacitaciones
CREATE TABLE capacitaciones_inscripciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    capacitacion_id INT NOT NULL,
    empleado_id INT NOT NULL,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('inscrito', 'completado', 'no_asistio') DEFAULT 'inscrito',
    calificacion DECIMAL(3,2),
    certificado_archivo VARCHAR(255),
    FOREIGN KEY (capacitacion_id) REFERENCES capacitaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE,
    UNIQUE KEY unique_capacitacion_empleado (capacitacion_id, empleado_id)
);

-- Tabla de tipos de permisos
CREATE TABLE tipos_permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    dias_maximos INT DEFAULT 0,
    requiere_aprobacion BOOLEAN DEFAULT TRUE,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de solicitudes de permisos
CREATE TABLE permisos_solicitudes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    tipo_permiso_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    dias_solicitados INT NOT NULL,
    motivo TEXT NOT NULL,
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    aprobado_por INT,
    fecha_aprobacion TIMESTAMP NULL,
    comentarios_aprobacion TEXT,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_permiso_id) REFERENCES tipos_permisos(id),
    FOREIGN KEY (aprobado_por) REFERENCES usuarios(id)
);

-- Tabla del muro empresarial
CREATE TABLE muro_publicaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    autor_id INT NOT NULL,
    tipo ENUM('anuncio', 'evento', 'politica', 'felicitacion') NOT NULL,
    titulo VARCHAR(200),
    contenido TEXT NOT NULL,
    archivo VARCHAR(255),
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id)
);

-- Tabla de comentarios del muro
CREATE TABLE muro_comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    publicacion_id INT NOT NULL,
    autor_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publicacion_id) REFERENCES muro_publicaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (autor_id) REFERENCES usuarios(id)
);

-- Tabla de likes del muro
CREATE TABLE muro_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    publicacion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_like TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publicacion_id) REFERENCES muro_publicaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY unique_like (publicacion_id, usuario_id)
);

-- Tabla de evaluaciones de desempeño
CREATE TABLE evaluaciones_desempeno (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empleado_id INT NOT NULL,
    evaluador_id INT NOT NULL,
    periodo_inicio DATE NOT NULL,
    periodo_fin DATE NOT NULL,
    puntuacion_total DECIMAL(3,2),
    comentarios TEXT,
    objetivos_cumplidos TEXT,
    areas_mejora TEXT,
    plan_desarrollo TEXT,
    estado ENUM('borrador', 'completada', 'aprobada') DEFAULT 'borrador',
    fecha_evaluacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluador_id) REFERENCES usuarios(id)
);

-- Insertar datos iniciales

-- Usuarios del sistema
INSERT INTO usuarios (usuario, password, nombre, email, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Sistema', 'admin@empresa.com', 'administrador'),
('gerente1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González', 'maria.gonzalez@empresa.com', 'gerente'),
('empleado1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'juan.perez@empresa.com', 'empleado');

-- Empleados
INSERT INTO empleados (codigo, usuario_id, nombre, apellido, cedula, email, telefono, departamento, cargo, fecha_ingreso, salario_base) VALUES
('EMP001', 3, 'Juan', 'Pérez', '12345678', 'juan.perez@empresa.com', '555-0123', 'Desarrollo', 'Desarrollador Senior', '2023-01-15', 50000.00),
('EMP002', 2, 'María', 'González', '87654321', 'maria.gonzalez@empresa.com', '555-0124', 'Recursos Humanos', 'Gerente RRHH', '2022-03-10', 75000.00);

-- Tipos de permisos
INSERT INTO tipos_permisos (nombre, descripcion, dias_maximos) VALUES
('Vacaciones', 'Vacaciones anuales', 30),
('Licencia Médica', 'Licencia por enfermedad', 90),
('Permiso Personal', 'Permiso por asuntos personales', 5),
('Maternidad/Paternidad', 'Licencia por maternidad o paternidad', 120);

-- Capacitaciones de ejemplo
INSERT INTO capacitaciones (titulo, descripcion, instructor, fecha_inicio, fecha_fin, duracion_horas, modalidad, cupos_disponibles, creada_por) VALUES
('Seguridad Laboral', 'Capacitación obligatoria en seguridad y salud ocupacional', 'Dr. Carlos Seguridad', '2024-02-01', '2024-02-01', 8, 'presencial', 50, 1),
('Liderazgo Efectivo', 'Desarrollo de habilidades de liderazgo para gerentes', 'Lic. Ana Liderazgo', '2024-02-15', '2024-02-16', 16, 'mixta', 20, 1);

-- Crear índices para optimización
CREATE INDEX idx_empleados_departamento ON empleados(departamento);
CREATE INDEX idx_empleados_estado ON empleados(estado);
CREATE INDEX idx_nomina_empleado_periodo ON nomina(empleado_id, periodo_inicio, periodo_fin);
CREATE INDEX idx_documentos_firmas_estado ON documentos_firmas(estado);
CREATE INDEX idx_permisos_estado ON permisos_solicitudes(estado);
CREATE INDEX idx_muro_fecha ON muro_publicaciones(fecha_publicacion);
