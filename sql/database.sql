-- sql/database.sql

-- Tabla para usuarios (para el login)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE, -- Nombre de usuario para login
    password VARCHAR(255) NOT NULL,       -- Contraseña hasheada (¡NUNCA texto plano!)
    role VARCHAR(50) DEFAULT 'user',      -- Rol del usuario (ej. 'admin', 'user')
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para Consignatarios (si se repiten o para lookup)
-- Se asume que un consignatario puede estar en múltiples registros.
CREATE TABLE consignatarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE, -- Nombre de la empresa consignataria
    domicilio TEXT,
    rfc VARCHAR(20),                     -- Asumiendo RFC en México
    email VARCHAR(100),
    telefono VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para Remitentes (si se repiten o para lookup)
-- Se asume que un remitente puede estar en múltiples registros.
CREATE TABLE remitentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE, -- Nombre de la empresa remitente
    domicilio TEXT,
    pais_origen VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla principal de Bitácora de Movimientos (Registros de Entrada)
CREATE TABLE bitacora_registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_ingreso DATETIME NOT NULL,
    tipo_operacion ENUM('Entrada', 'Salida') NOT NULL, -- Por ahora solo 'Entrada' según el mockup de registro.
    num_conocimiento_embarque VARCHAR(100) NOT NULL UNIQUE, -- El #BL es un identificador único.
    num_registro_buque_vuelo_contenedor VARCHAR(100) NOT NULL,
    dimension_tipo_sellos_candados TEXT,
    primer_puerto_terminal VARCHAR(255) NOT NULL,
    descripcion_mercancia TEXT NOT NULL,
    peso_unidad_medida DECIMAL(10, 2) NOT NULL, -- Asumiendo hasta dos decimales
    num_bultos INT NOT NULL,
    valor_comercial DECIMAL(15, 2) NOT NULL, -- Asumiendo valores monetarios
    fecha_conclusion_descarga DATETIME,
    
    -- Foreign keys para Consignatario y Remitente
    consignatario_id INT,
    remitente_id INT,

    -- Campos de auditoría
    registrado_por_user_id INT NOT NULL, -- ID del usuario que registra
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Restricciones de Clave Foránea
    FOREIGN KEY (consignatario_id) REFERENCES consignatarios(id) ON DELETE SET NULL, -- Si se borra consignatario, el registro no se borra.
    FOREIGN KEY (remitente_id) REFERENCES remitentes(id) ON DELETE SET NULL,
    FOREIGN KEY (registrado_por_user_id) REFERENCES users(id)
);

-- (Opcional para pruebas) Datos de usuario inicial (¡Cambiar contraseña después!)
INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$i/R1o7L8O9C5B2A6Z.1234567890abcdefghijklmnopqrstuvwxyz', 'admin');
-- NOTA: '$2y$10$i/R1o7L8O9C5B2A6Z.1234567890abcdefghijklmnopqrstuvwxyz' es un hash de ejemplo para 'password123'.
-- DEBES generar un hash real en PHP usando password_hash() para tu contraseña.