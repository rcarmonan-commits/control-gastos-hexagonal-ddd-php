-- ============================================================================
-- SISTEMA DE GESTION DE USUARIOS Y CONTROL DE GASTOS (EJERCICIO #6)
-- Arquitectura Hexagonal + DDD + CQRS en PHP 8.3
-- Script corregido y normalizado (sin errores de FK ni comillas tipograficas)
-- ============================================================================

-- 1. CREAR USUARIO DE BASE DE DATOS LOCAL
CREATE USER IF NOT EXISTS 'Estudiante'@'%' IDENTIFIED BY 'Developer';

-- 2. CREAR BASE DE DATOS
CREATE DATABASE IF NOT EXISTS crud_usuarios
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- 3. OTORGAR PERMISOS AL USUARIO
GRANT ALL PRIVILEGES ON crud_usuarios.* TO 'Estudiante'@'%';
FLUSH PRIVILEGES;

-- 4. USAR BASE DE DATOS
USE crud_usuarios;

-- 5. TABLA DE ROLES
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABLA DE USUARIOS (Corregida: incluye role, status y role_id con FK valida)
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(64) NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'MEMBER',
    status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
    role_id INT UNSIGNED NULL DEFAULT 2,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_role
        FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABLA DE GASTOS (Entidad Asignada #6)
-- Atributos de negocio: fecha, valorTotalSinIVA, ivaTotal, valorTotalConIVA,
--                       nombreUsuario, lugar, descripcion (+ id de entidad)
CREATE TABLE IF NOT EXISTS gastos (
    id VARCHAR(64) NOT NULL PRIMARY KEY,
    fecha DATE NOT NULL,
    valor_total_sin_iva DECIMAL(15, 2) NOT NULL,
    iva_total DECIMAL(15, 2) NOT NULL,
    valor_total_con_iva DECIMAL(15, 2) NOT NULL,
    nombre_usuario VARCHAR(100) NOT NULL,
    lugar VARCHAR(150) NOT NULL,
    descripcion VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gastos_fecha (fecha),
    INDEX idx_gastos_usuario (nombre_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. INSERTAR ROLES BASE
INSERT IGNORE INTO roles (id, name) VALUES
    (1, 'ADMIN'),
    (2, 'MEMBER'),
    (3, 'REVIEWER');

-- 9. INSERTAR USUARIOS SEMILLA (Migracion usuario admin base + usuario estudiante)
INSERT IGNORE INTO users (id, name, email, password, role, status, role_id) VALUES
    ('USR-001', 'Administrador', 'admin@crud.com', 'Developer123', 'ADMIN', 'ACTIVE', 1),
    ('USR-002', 'Rosary Carmona', 'rcarmonan@unicartagena.edu.co', 'Carmona2026*', 'MEMBER', 'ACTIVE', 2);

-- 10. INSERTAR GASTOS SEMILLA DE VERIFICACION
INSERT IGNORE INTO gastos (
    id, fecha, valor_total_sin_iva, iva_total, valor_total_con_iva, nombre_usuario, lugar, descripcion
) VALUES
    ('GAS-001', '2026-08-20', 150000.00, 28500.00, 178500.00, 'Administrador', 'Papeleria Panamericana Cartagena', 'Compra de suministros de oficina y papeleria institucional'),
    ('GAS-002', '2026-09-14', 320000.00, 60800.00, 380800.00, 'Rosary Carmona', 'Centro de Convenciones Cartagena', 'Inscripcion a jornada academica de ingenieria de software');
