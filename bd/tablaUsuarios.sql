--
-- Script SQL: Creación de la Tabla `users` para el Login
-- Base de Datos: rrhh_personal
--
CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL, -- Usado para login (puede ser email o un nombre de usuario)
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, -- Almacena la contraseña hasheada
    rol ENUM('empleado', 'gerente', 'administrador') NOT NULL DEFAULT 'empleado',
    activo BOOLEAN NOT NULL DEFAULT TRUE, -- Para inhabilitar cuentas
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_username (username)
);

-- INSERCIÓN DE UN USUARIO DE PRUEBA (CONTRASENA: 'Admin123')
-- Nota: En un sistema real, el hash se generaría al crear el usuario.
-- Puedes usar un script PHP simple (echo password_hash('Admin123', PASSWORD_BCRYPT);) para generar el hash real.
-- Ejemplo de Hash (reemplazar con el generado):
INSERT INTO users (username, email, password_hash, rol) VALUES 
('admin', 'admin@sistema.com', '$2y$10$GDGc0OkDtXV1Xu29c9P2ruW5TOmKxn8OAA63E./OkZKASuP2L3mQG', 'administrador');