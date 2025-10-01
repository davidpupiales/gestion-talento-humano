--
-- Script SQL: Creación de la Tabla `empleados`
-- Base de Datos: hrms_system2
--
CREATE TABLE IF NOT EXISTS empleados (
    -- Información Básica / Identificación
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE, -- Se puede autogenerar o registrar
    cedula VARCHAR(20) UNIQUE NOT NULL, 
    nombre_completo VARCHAR(255) NOT NULL,
    apellido_completo VARCHAR(255) NOT NULL, -- Se sugiere dividir Nombre y Apellido para búsquedas y organización
    email VARCHAR(255) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    fecha_nacimiento DATE,
    genero ENUM('MASCULINO', 'FEMENINO') NOT NULL,
    grupo_sanguineo ENUM('A+', 'A−', 'B+', 'B−', 'AB+', 'AB−', 'O+', 'O−'),
    
    -- Información Laboral / Contrato
    tipo_contrato ENUM('OPS', 'LAB') NOT NULL,
    estado ENUM('ACTIVO', 'ENVIADO PARA FIRMA', 'TERMINADO', 'PROYECTAR CONTRATO', 'ACTIVO SIN FIRMA', 'LIQUIDADO') NOT NULL,
    fecha_ingreso DATE NOT NULL,
    fecha_retiro DATE NULL,
    sede VARCHAR(100),
    cargo VARCHAR(100) NOT NULL,
    nivel VARCHAR(100),
    calidad VARCHAR(100),
    programa VARCHAR(100),
    area VARCHAR(100),
    departamento VARCHAR(50),
    municipio VARCHAR(100),
    servicio VARCHAR(100),
    nivel_riesgo ENUM('I', 'II', 'III', 'IV', 'V'),
    smlv DECIMAL(10, 2) NOT NULL DEFAULT 1423500.00, -- Asumiendo la constante SMLV de utils.php

    -- Información de Pagos y Seguridad Social
    salario DECIMAL(10, 2) NOT NULL,
    auxilio_transporte ENUM('SI', 'NO') DEFAULT 'NO',
    banco VARCHAR(100),
    tipo_cuenta VARCHAR(50),
    numero_cuenta VARCHAR(50),
    eps VARCHAR(100),
    afp VARCHAR(100),
    arl VARCHAR(100),
    caja_compensacion VARCHAR(100),
    poliza ENUM('TIENE', 'NO TIENE', 'NO APLICA') DEFAULT 'NO APLICA',

    -- Fechas de Vigencia (Cursos/Certificaciones)
    certificado_manipulacion_alimentos DATE NULL,
    certificado_rcp DATE NULL,
    certificado_altura DATE NULL,
    certificado_bioseguridad DATE NULL,
    
    -- Metadatos
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índice para búsquedas frecuentes
    INDEX idx_nombre (nombre_completo),
    INDEX idx_estado (estado)
);