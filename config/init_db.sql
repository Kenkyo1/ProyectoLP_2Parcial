CREATE DATABASE IF NOT EXISTS denuncias_db;
USE denuncias_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    rol VARCHAR(50) NOT NULL DEFAULT 'usuario'
);

CREATE TABLE IF NOT EXISTS denuncias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    ubicacion VARCHAR(255) NOT NULL,
    imagen VARCHAR(255),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    estado VARCHAR(20) NOT NULL DEFAULT 'Pendiente'
);

INSERT INTO usuarios (nombre, email, password, fecha_registro, is_admin, rol)
VALUES ('Administrador Principal', 'admin@ecoalert.com', '$2y$10$ZBKkP3dSIQFFtQAk1f9F6eX25rtM7zyzRiLBPAHzPv7nb668fU2My', NOW(), 1, 'administrador');
