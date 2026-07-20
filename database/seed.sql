CREATE DATABASE IF NOT EXISTS siakad_mini;
USE siakad_mini;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','operator') NOT NULL DEFAULT 'operator',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dosen (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nidn CHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    program_studi ENUM('Teknik Informatika','Sistem Informasi','Teknik Elektro') NOT NULL,
    foto VARCHAR(255) NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mata_kuliah (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(12) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    sks TINYINT UNSIGNED NOT NULL CHECK (sks BETWEEN 1 AND 6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dosen_matakuliah (
    dosen_id INT UNSIGNED NOT NULL,
    matakuliah_id INT UNSIGNED NOT NULL,
    semester ENUM('Ganjil','Genap') NOT NULL,
    PRIMARY KEY (dosen_id, matakuliah_id, semester),
    FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE,
    FOREIGN KEY (matakuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$0B3Qf3Ab4oA1r6hSJcM7r.9vQbmH0P0CfRHz6nh0XheWg0NcbMwa6', 'admin'),
('operator', '$2y$10$z3dHo1fJeU.xJ2Qh0M3p1O6u3M8t3DbHcW3x8f3YqFqFQ7r8JvFBu', 'operator');

INSERT INTO mata_kuliah (kode, nama, sks) VALUES
('MK001', 'Pemrograman Web', 3),
('MK002', 'Basis Data', 3),
('MK003', 'Struktur Data', 3),
('MK004', 'Jaringan Komputer', 3);
