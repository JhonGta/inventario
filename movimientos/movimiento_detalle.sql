
CREATE DATABASE IF NOT EXISTS inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventario;

-- Tabla de administrador
CREATE TABLE administrador (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);

-- Tabla de productos
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    id_categoria INT NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);

-- Tabla de ubicaciones
CREATE TABLE ubicaciones (
    id_ubicacion INT AUTO_INCREMENT PRIMARY KEY,
    nombre_ubicacion VARCHAR(100) NOT NULL,
    tipo ENUM('PC','OFICINA','BODEGA') NOT NULL,
    descripcion TEXT
);

-- Tabla de movimientos
CREATE TABLE movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    tipo_movimiento ENUM('ENTRADA','SALIDA') NOT NULL,
    cantidad INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responsable VARCHAR(150) NOT NULL,
    id_ubicacion INT NULL,
    observacion TEXT,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_ubicacion) REFERENCES ubicaciones(id_ubicacion)
);
ALTER TABLE movimientos MODIFY tipo_movimiento ENUM('ENTRADA','SALIDA','DEVOLUCION','BAJA') NOT NULL;
-- ALTER TABLE movimientos MODIFY tipo_movimiento ENUM('ENTRADA','SALIDA','DEVOLUCION') NOT NULL;

ALTER TABLE categorias ADD estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO';
ALTER TABLE ubicaciones ADD estado ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO';




CREATE TABLE productos_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    nombre VARCHAR(100) NULL,
    marca VARCHAR(100) NULL,
    serie VARCHAR(100) NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'ACTIVO',
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);


CREATE TABLE movimiento_detalle (
    id_movimiento_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_movimiento INT NOT NULL,
    id_detalle INT NOT NULL,
    FOREIGN KEY (id_movimiento) REFERENCES movimientos(id_movimiento),
    FOREIGN KEY (id_detalle) REFERENCES productos_detalle(id_detalle)
);














select * from administrador
select * from categorias
select * from productos
select * from movimientos
select * from ubicaciones
select * from productos_detalle




SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE movimientos;
TRUNCATE TABLE productos_detalle;
TRUNCATE TABLE productos;
TRUNCATE TABLE categorias;
TRUNCATE TABLE ubicaciones;

SET FOREIGN_KEY_CHECKS = 1;

