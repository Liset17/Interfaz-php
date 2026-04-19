
CREATE DATABASE IF NOT EXISTS academia_teatro
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE academia_teatro;


CREATE TABLE IF NOT EXISTS profesores (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre         VARCHAR(120) NOT NULL,
  email          VARCHAR(180) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  rol            ENUM('profesor','admin') NOT NULL DEFAULT 'profesor',
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS grupos (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(120) NOT NULL,
  horario      VARCHAR(120) DEFAULT NULL,
  cupo         INT UNSIGNED NOT NULL DEFAULT 20,
  profesor_id  INT UNSIGNED DEFAULT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_grupos_profesor
    FOREIGN KEY (profesor_id) REFERENCES profesores(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS alumnos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(120) NOT NULL,
  email       VARCHAR(180) DEFAULT NULL UNIQUE,
  telefono    VARCHAR(40)  DEFAULT NULL,
  grupo_id    INT UNSIGNED DEFAULT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_alumnos_grupo
    FOREIGN KEY (grupo_id) REFERENCES grupos(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clases (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(120) NOT NULL,
  horario      VARCHAR(120) DEFAULT NULL,
  cupo         INT UNSIGNED NOT NULL DEFAULT 20,
  profesor_id  INT UNSIGNED DEFAULT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_clases_profesor
    FOREIGN KEY (profesor_id) REFERENCES profesores(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS asistencia (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  alumno_id    INT UNSIGNED NOT NULL,
  grupo_id     INT UNSIGNED DEFAULT NULL,
  fecha        DATE NOT NULL,
  presente     TINYINT(1) NOT NULL DEFAULT 0,
  observacion  VARCHAR(255) DEFAULT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_alumno_fecha (alumno_id, fecha),
  CONSTRAINT fk_asistencia_alumno
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE,
  CONSTRAINT fk_asistencia_grupo
    FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

