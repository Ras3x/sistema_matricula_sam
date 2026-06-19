-- Creación de la base de datos e inicialización del Sistema de Matrícula SAM

-- 1. Tabla de Programas de Estudio (Carreras)
CREATE TABLE IF NOT EXISTS programas_estudio (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- 2. Tabla de Usuarios (OP, Admin, Estudiante, Postulante)
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Almacenado en texto plano según requerimiento
    role VARCHAR(20) NOT NULL CHECK (role IN ('OP', 'Admin', 'Estudiante', 'Postulante')),
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    celular VARCHAR(20),
    direccion VARCHAR(255),
    dni VARCHAR(20) UNIQUE,
    programa_id INT REFERENCES programas_estudio(id) ON DELETE SET NULL,
    codigo_matricula VARCHAR(50) UNIQUE,
    estado VARCHAR(50) DEFAULT 'Registrado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabla de Vouchers (Admisión o Matrícula)
CREATE TABLE IF NOT EXISTS vouchers (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('Admision', 'Matricula')),
    monto NUMERIC(10, 2) NOT NULL,
    numero_operacion VARCHAR(50) NOT NULL,
    fecha_pago DATE NOT NULL,
    archivo_path VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Pendiente' CHECK (estado IN ('Pendiente', 'Aprobado', 'Rechazado')),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabla de Requisitos (Documentos de Matrícula)
CREATE TABLE IF NOT EXISTS requisitos (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    certificado_estudios_path VARCHAR(255) NOT NULL,
    partida_nacimiento_path VARCHAR(255) NOT NULL,
    dni_copia_path VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Pendiente' CHECK (estado IN ('Pendiente', 'Aprobado', 'Rechazado')),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabla de Notas (Calificaciones por Curso)
CREATE TABLE IF NOT EXISTS notas (
    id SERIAL PRIMARY KEY,
    estudiante_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    curso VARCHAR(100) NOT NULL,
    nota1 NUMERIC(4, 2) DEFAULT 0.00 CHECK (nota1 >= 0 AND nota1 <= 20),
    nota2 NUMERIC(4, 2) DEFAULT 0.00 CHECK (nota2 >= 0 AND nota2 <= 20),
    nota3 NUMERIC(4, 2) DEFAULT 0.00 CHECK (nota3 >= 0 AND nota3 <= 20),
    promedio NUMERIC(4, 2) DEFAULT 0.00 CHECK (promedio >= 0 AND promedio <= 20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Tabla de Logs de Auditoría
CREATE TABLE IF NOT EXISTS logs (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL,
    accion VARCHAR(255) NOT NULL,
    detalles TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- INSERT DE LAS 8 CARRERAS REQUERIDAS
INSERT INTO programas_estudio (nombre) VALUES
('Asistencia Administrativa'),
('Contabilidad'),
('Diseño y Programación Web'),
('Enfermería Técnica'),
('Farmacia Técnica'),
('Industrias Alimentarias'),
('Mecánica de Producción Industrial'),
('Producción Agropecuaria')
ON CONFLICT (nombre) DO NOTHING;

-- INSERT DE USUARIOS INICIALES
-- 1. SuperAdmin (OP)
INSERT INTO usuarios (username, password, role, nombres, apellidos, email, dni, estado) VALUES
('superadmin', 'admin123', 'OP', 'Administrador', 'General SAM', 'soporte@sam.edu.pe', '00000000', 'Activo')
ON CONFLICT (username) DO NOTHING;

-- 2. Administrador estándar (Admin)
INSERT INTO usuarios (username, password, role, nombres, apellidos, email, dni, estado) VALUES
('admin', 'admin123', 'Admin', 'Pedro', 'Gomez (Admin)', 'pgomez@sam.edu.pe', '11111111', 'Activo')
ON CONFLICT (username) DO NOTHING;

-- 3. Postulante demo 1 (Registrado - Sin voucher)
INSERT INTO usuarios (username, password, role, nombres, apellidos, email, celular, direccion, dni, programa_id, estado) VALUES
('77777777', 'postulante123', 'Postulante', 'Juan Carlos', 'Perez Rojas', 'juan.perez@gmail.com', '987654321', 'Av. Centenario 123, Huaraz', '77777777', 3, 'Registrado')
ON CONFLICT (username) DO NOTHING;

-- 4. Postulante demo 2 (Pago Pendiente - Con voucher simulado)
INSERT INTO usuarios (username, password, role, nombres, apellidos, email, celular, direccion, dni, programa_id, estado) VALUES
('88888888', 'postulante123', 'Postulante', 'Maria Isabel', 'Luna Flores', 'maria.luna@gmail.com', '912345678', 'Jr. Quillcay 456, Caraz', '88888888', 4, 'Pago Pendiente')
ON CONFLICT (username) DO NOTHING;

-- 5. Estudiante demo 1 (Matriculado - Con notas)
INSERT INTO usuarios (username, password, role, nombres, apellidos, email, celular, direccion, dni, programa_id, codigo_matricula, estado) VALUES
('99999999', 'estudiante123', 'Estudiante', 'Carlos Alberto', 'Torres Mendez', 'ctorres@sam.edu.pe', '954781236', 'Jr. Guzman Barrón 789, Huaraz', '99999999', 3, 'SAM-2026-0001', 'Matriculado')
ON CONFLICT (username) DO NOTHING;

-- ASOCIAR UN VOUCHER SIMULADO PARA MARIA ISABEL (POSTULANTE EN PAGO PENDIENTE)
-- Nota: La ruta de archivo apunta a una imagen de voucher ficticia para la simulación
INSERT INTO vouchers (usuario_id, tipo, monto, numero_operacion, fecha_pago, archivo_path, estado)
SELECT id, 'Admision', 150.00, 'OP-987654', '2026-06-14', 'uploads/vouchers/voucher_demo_maria.jpg', 'Pendiente'
FROM usuarios WHERE username = '88888888'
ON CONFLICT DO NOTHING;

-- ASOCIAR VOUCHER APROBADO PARA EL ESTUDIANTE CARLOS TORRES
INSERT INTO vouchers (usuario_id, tipo, monto, numero_operacion, fecha_pago, archivo_path, estado)
SELECT id, 'Matricula', 200.00, 'OP-112233', '2026-06-10', 'uploads/vouchers/voucher_demo_carlos.jpg', 'Aprobado'
FROM usuarios WHERE username = '99999999'
ON CONFLICT DO NOTHING;

-- INSERTAR NOTAS PARA EL ESTUDIANTE CARLOS TORRES
INSERT INTO notas (estudiante_id, curso, nota1, nota2, nota3, promedio)
SELECT id, 'Desarrollo de Aplicaciones Web', 16.00, 15.00, 18.00, 16.33 FROM usuarios WHERE username = '99999999' UNION ALL
SELECT id, 'Modelamiento de Base de Datos', 18.00, 19.00, 17.00, 18.00 FROM usuarios WHERE username = '99999999' UNION ALL
SELECT id, 'Análisis y Diseño de Sistemas', 14.00, 15.00, 16.00, 15.00 FROM usuarios WHERE username = '99999999'
ON CONFLICT DO NOTHING;

-- AGREGAR ALGUNOS LOGS INICIALES
INSERT INTO logs (usuario_id, accion, detalles)
SELECT id, 'Inicio de Sesión', 'El usuario superadmin ha iniciado sesión en el sistema.' FROM usuarios WHERE username = 'superadmin' UNION ALL
SELECT id, 'Registro de Postulante', 'Nuevo registro de postulante Juan Carlos Perez Rojas.' FROM usuarios WHERE username = '77777777' UNION ALL
SELECT id, 'Subida de Voucher', 'Postulante Maria Luna ha subido un voucher de Admisión.' FROM usuarios WHERE username = '88888888'
ON CONFLICT DO NOTHING;
