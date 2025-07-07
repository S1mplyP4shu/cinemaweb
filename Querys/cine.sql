DROP DATABASE IF EXISTS cine;
CREATE DATABASE cine;
USE cine;
show tables;


CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'empleado', 'admin') DEFAULT 'cliente',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Guarda historial de cuentas eliminadas
CREATE TABLE cancelaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    correo VARCHAR(100),
    fecha_cancelacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo TEXT
);


CREATE TABLE peliculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    sinopsis TEXT,
    duracion_min INT NOT NULL,
    clasificacion VARCHAR(10),
    genero VARCHAR(50),
    imagen_url VARCHAR(255)
);


CREATE TABLE funciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelicula_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    sala VARCHAR(10) NOT NULL,
    total_asientos INT NOT NULL,
    FOREIGN KEY (pelicula_id) REFERENCES peliculas(id) ON DELETE CASCADE
);


CREATE TABLE asientos_reservados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    funcion_id INT NOT NULL,
    asiento VARCHAR(5) NOT NULL,
    fecha_reserva DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (funcion_id, asiento),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (funcion_id) REFERENCES funciones(id) ON DELETE CASCADE
);

select * from usuarios;
