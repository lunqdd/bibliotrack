-- Importar siempre con --default-character-set=utf8mb4 o los acentos quedan mal guardados.
CREATE DATABASE bibliotrack

CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE bibliotrack;

-- Generos para clasificar los libros.
CREATE TABLE generos (
    genero_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Autores que escriben los libros.
CREATE TABLE autores (
    autor_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Editoriales que publican los libros.
CREATE TABLE editoriales (
    editorial_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Ubicaciones físicas de los ejemplares en la biblioteca.
CREATE TABLE ubicaciones (
    ubicacion_id INT AUTO_INCREMENT PRIMARY KEY,
    pasillo VARCHAR(10) NOT NULL,
    estante VARCHAR(10) NOT NULL,
    UNIQUE (pasillo, estante)
) ENGINE=InnoDB;

-- Usuarios del sistema con credenciales y estado.
CREATE TABLE usuarios (
    usuario_id      INT AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(20)  NOT NULL UNIQUE,
    nombre_completo VARCHAR(150) NOT NULL,
    identificacion  VARCHAR(30)  NOT NULL UNIQUE,
    correo          VARCHAR(150) NOT NULL UNIQUE,
    telefono        VARCHAR(20),
    direccion       VARCHAR(255),
    password_hash   VARCHAR(255) NOT NULL,
    rol             ENUM('admin', 'lector') NOT NULL DEFAULT 'lector',
    estado          ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_registro  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso   DATETIME NULL
) ENGINE=InnoDB;

-- Cada libro representa el registro bibliográfico.
CREATE TABLE libros (
    libro_id         INT AUTO_INCREMENT PRIMARY KEY,
    codigo           VARCHAR(20)  NOT NULL UNIQUE,
    titulo           VARCHAR(200) NOT NULL,
    isbn             VARCHAR(20)  NOT NULL UNIQUE,
    autor_id         INT NOT NULL,
    editorial_id     INT NULL,
    genero_id        INT NULL,
    anio_publicacion SMALLINT,
    portada_url      VARCHAR(255),
    fecha_registro   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_libro_autor     FOREIGN KEY (autor_id)     REFERENCES autores(autor_id),
    CONSTRAINT fk_libro_editorial FOREIGN KEY (editorial_id) REFERENCES editoriales(editorial_id),
    CONSTRAINT fk_libro_genero    FOREIGN KEY (genero_id)    REFERENCES generos(genero_id)
) ENGINE=InnoDB;

-- Cada fila es una copia física del libro.
CREATE TABLE ejemplares (
    ejemplar_id   INT AUTO_INCREMENT PRIMARY KEY,
    libro_id      INT NOT NULL,
    ubicacion_id  INT NOT NULL,
    estado       ENUM('disponible', 'prestado', 'en_reparacion') NOT NULL DEFAULT 'disponible',
    CONSTRAINT fk_ejemplar_libro     FOREIGN KEY (libro_id)     REFERENCES libros(libro_id),
    CONSTRAINT fk_ejemplar_ubicacion FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(ubicacion_id)
) ENGINE=InnoDB;

-- Registro de salida temporal de un ejemplar a un usuario.
CREATE TABLE prestamos (
    prestamo_id                 INT AUTO_INCREMENT PRIMARY KEY,
    codigo                      VARCHAR(20) NOT NULL UNIQUE,
    ejemplar_id                 INT NOT NULL,
    usuario_id                  INT NOT NULL,
    fecha_prestamo              DATE NOT NULL DEFAULT (CURRENT_DATE),
    fecha_devolucion_programada DATE NOT NULL,
    fecha_creacion              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_prestamo_ejemplar FOREIGN KEY (ejemplar_id) REFERENCES ejemplares(ejemplar_id),
    CONSTRAINT fk_prestamo_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios(usuario_id)
) ENGINE=InnoDB;

-- Un préstamo queda cerrado cuando se crea su devolución.
CREATE TABLE devoluciones (
    devolucion_id    INT AUTO_INCREMENT PRIMARY KEY,
    prestamo_id      INT NOT NULL UNIQUE,
    fecha_devolucion DATE NOT NULL DEFAULT (CURRENT_DATE),
    estado_ejemplar  ENUM('buen_estado', 'dano_menor', 'requiere_reparacion') NOT NULL DEFAULT 'buen_estado',
    notas            VARCHAR(255),
    CONSTRAINT fk_devolucion_prestamo FOREIGN KEY (prestamo_id) REFERENCES prestamos(prestamo_id)
) ENGINE=InnoDB;

-- Notificaciones del sistema para vencimientos o avisos informativos.
CREATE TABLE notificaciones (
    notificacion_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id     INT NOT NULL,
    prestamo_id    INT NULL,
    mensaje        VARCHAR(255) NOT NULL,
    tipo           ENUM('proximo_vencimiento', 'vencido', 'informativa') NOT NULL,
    leida          BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificacion_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios(usuario_id),
    CONSTRAINT fk_notificacion_prestamo FOREIGN KEY (prestamo_id) REFERENCES prestamos(prestamo_id)
) ENGINE=InnoDB;

-- Solicitudes realizadas por lectores antes de que el personal asigne un ejemplar.
CREATE TABLE solicitudes_prestamo (
    solicitud_id    INT AUTO_INCREMENT PRIMARY KEY,
    codigo          VARCHAR(20) NOT NULL UNIQUE,
    usuario_id      INT NOT NULL,
    libro_id        INT NOT NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado          ENUM('pendiente', 'aprobada', 'rechazada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    notas           VARCHAR(255),
    CONSTRAINT fk_solicitud_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id),
    CONSTRAINT fk_solicitud_libro   FOREIGN KEY (libro_id)   REFERENCES libros(libro_id)
) ENGINE=InnoDB;

-- Datos de ejemplo para probar el sistema.
INSERT INTO generos (nombre) VALUES
('Novela'), ('Ciencia'), ('Historia'), ('Arte');

-- Autores del catálogo de ejemplo.
INSERT INTO autores (nombre) VALUES
('Gabriel García Márquez'),
('Stephen Hawking'),
('Indro Montanelli'),
('Alice Rawsthorn'),
('Antoine de Saint-Exupéry'),
('Carl Sagan'),
('Miguel de Cervantes'),
('Yuval Noah Harari');

-- Editoriales del catálogo de ejemplo.
INSERT INTO editoriales (nombre) VALUES
('Sudamericana'),
('Bantam Books'),
('Plaza & Janés'),
('Penguin'),
('Reynal & Hitchcock'),
('Random House'),
('Francisco de Robles'),
('Debate');

-- Pasillo + estante donde se ubican físicamente los ejemplares.
INSERT INTO ubicaciones (pasillo, estante) VALUES
('A', '4'), ('B', '2'), ('C', '1'), ('D', '2');

-- Los 4 usuarios de ejemplo comparten clave: Bibliotrack2026! (el hash de abajo es esa clave).
INSERT INTO usuarios (codigo, nombre_completo, identificacion, correo, telefono, direccion, password_hash, rol, estado, fecha_registro, ultimo_acceso) VALUES
('ID-100034', 'Luna Delgado Durango', '1-1111-1111', 'ldelgado00034@ufide.ac.cr', '8812-3456', 'Heredia, Costa Rica', '$2b$10$96.upCJaOw0bUn.f9TAoeOJYctHBWYkZQ.cORvfucWUOOwlzLtcIC', 'admin', 'activo', '2026-01-15 08:00:00', '2026-06-29 16:30:00'),
('ID-700596', 'Abby Chavarría Bolaños', '2-2222-2222', 'achavarria70596@ufide.ac.cr', '8834-5678', 'Heredia, Costa Rica', '$2b$10$96.upCJaOw0bUn.f9TAoeOJYctHBWYkZQ.cORvfucWUOOwlzLtcIC', 'lector', 'activo', '2026-01-15 09:00:00', NULL),
('ID-300226', 'María Peña García', '3-3333-3333', 'mpena30226@ufide.ac.cr', '8856-7890', 'Alajuela, Costa Rica', '$2b$10$96.upCJaOw0bUn.f9TAoeOJYctHBWYkZQ.cORvfucWUOOwlzLtcIC', 'lector', 'activo', '2026-01-18 10:00:00', NULL),
('ID-200153', 'Derek Jensen Arguedas', '4-4444-4444', 'jjensen20153@ufide.ac.cr', '8878-1234', 'San José, Costa Rica', '$2b$10$96.upCJaOw0bUn.f9TAoeOJYctHBWYkZQ.cORvfucWUOOwlzLtcIC', 'lector', 'activo', '2026-01-20 11:00:00', NULL);

-- Libros del catálogo de ejemplo (portada_url apunta a archivos en img/).
INSERT INTO libros (libro_id, codigo, titulo, isbn, autor_id, editorial_id, genero_id, anio_publicacion, portada_url, fecha_registro) VALUES
(1, 'LIB-001', 'Cien años de soledad', '978-0307350433', 1, 1, 1, 1967, 'cien-años-de-soledad.jpg', '2026-07-22 21:05:47'),
(2, 'LIB-002', 'Breve historia del tiempo', '978-0553109580', 2, 2, 2, 1988, 'breve-historia-del-tiempo.jpg', '2026-07-22 21:05:47'),
(3, 'LIB-003', 'Historia de Roma', '978-8497593151', 3, 3, 3, 1957, 'historia-de-roma.jpg', '2026-07-22 21:05:47'),
(4, 'LIB-004', 'El diseño como actitud', '978-0141984254', 4, 4, 4, 2013, 'diseño-como-actitud.webp', '2026-07-22 21:05:47'),
(5, 'LIB-005', 'El Principito', '978-0156012195', 5, 5, 1, 1943, 'principito.jpg', '2026-07-22 21:05:47'),
(6, 'LIB-006', 'Cosmos', '978-0345539434', 6, 6, 2, 1980, 'cosmos.jpg', '2026-07-22 21:05:47'),
(7, 'LIB-007', 'Don Quijote de la Mancha', '978-8420412146', 7, 7, 1, 1605, 'don-quijote-de-la-mancha.jpg', '2026-07-22 21:05:47'),
(8, 'LIB-008', 'Sapiens', '978-0062316097', 8, 8, 3, 2011, 'sapiens.jpg', '2026-07-22 21:05:47');

-- Copias físicas de cada libro, con su estado inicial.
INSERT INTO ejemplares (libro_id, ubicacion_id, estado) VALUES
(1, 1, 'disponible'), (1, 1, 'disponible'), (1, 1, 'disponible'), (1, 1, 'disponible'), (1, 1, 'disponible'),
(2, 2, 'prestado'), (2, 2, 'prestado'), (2, 2, 'prestado'),
(3, 3, 'disponible'), (3, 3, 'prestado'),
(4, 4, 'disponible'), (4, 4, 'disponible'), (4, 4, 'disponible'), (4, 4, 'disponible'),
(5, 1, 'prestado'), (5, 1, 'disponible'), (5, 1, 'disponible'), (5, 1, 'disponible'), (5, 1, 'disponible'), (5, 1, 'disponible'), (5, 1, 'disponible'), (5, 1, 'disponible'),
(6, 2, 'en_reparacion'), (6, 2, 'en_reparacion'), (6, 2, 'en_reparacion'),
(7, 3, 'prestado'), (7, 3, 'disponible'), (7, 3, 'disponible'), (7, 3, 'disponible'), (7, 3, 'disponible'), (7, 3, 'disponible'),
(8, 4, 'disponible'), (8, 4, 'disponible'), (8, 4, 'disponible'), (8, 4, 'disponible');

-- Préstamos de ejemplo (activos hasta que aparezcan en devoluciones más abajo).
INSERT INTO prestamos (codigo, ejemplar_id, usuario_id, fecha_prestamo, fecha_devolucion_programada) VALUES
('PRE-001', 1, 4, '2026-06-12', '2026-06-26'),
('PRE-002', 6, 2, '2026-06-18', '2026-07-02'),
('PRE-003', 10, 3, '2026-06-05', '2026-06-19'),
('PRE-004', 26, 4, '2026-06-24', '2026-07-08'),
('PRE-005', 15, 4, '2026-06-20', '2026-07-04'),
('PRE-006', 7, 2, '2026-06-22', '2026-07-06'),
('PRE-007', 8, 2, '2026-06-26', '2026-07-10');

-- Solo el préstamo PRE-001 (prestamo_id=1) ya se devolvió; el resto sigue activo.
INSERT INTO devoluciones (prestamo_id, fecha_devolucion, estado_ejemplar, notas) VALUES
(1, '2026-06-25', 'buen_estado', '');

-- Solicitud de ejemplo pendiente de aprobación (hecha desde el panel del lector).
INSERT INTO solicitudes_prestamo (codigo, usuario_id, libro_id, fecha_solicitud, estado, notas) VALUES
('SOL-001', 4, 8, '2026-06-30 09:15:00', 'pendiente', 'Solicitud desde el portal del lector.');

-- Avisos de ejemplo, sin leer, para el préstamo vencido y el próximo a vencer.
INSERT INTO notificaciones (usuario_id, prestamo_id, mensaje, tipo, leida, fecha_creacion) VALUES
(3, 3, 'El préstamo PRE-003 se encuentra vencido.', 'vencido', FALSE, '2026-06-30 08:00:00'),
(2, 2, 'El préstamo PRE-002 vence pronto.', 'proximo_vencimiento', FALSE, '2026-06-30 08:10:00');
