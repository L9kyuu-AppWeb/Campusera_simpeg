-- Schema untuk tabel PEGAWAI
-- Berdasarkan skema dari pegawai.md
-- Hanya admin yang bisa mengakses
-- Mengikuti struktur modul games

CREATE TABLE IF NOT EXISTS pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_induk VARCHAR(50) NULL COMMENT 'NULL for dosen luar',
    nik VARCHAR(50) NULL COMMENT 'NULL for dosen luar',
    nama_lengkap VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    tempat_lahir VARCHAR(100) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL COMMENT 'L/P',
    alamat TEXT NOT NULL,
    foto VARCHAR(255) NULL,
    status_aktif ENUM('aktif', 'non-aktif', 'pensiun') NOT NULL,
    tipe_pegawai ENUM('dosen_tetap', 'dosen_luar', 'tendik_tetap', 'tendik_kontrak') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_nomor_induk ON pegawai(nomor_induk);
CREATE INDEX idx_nik ON pegawai(nik);
CREATE INDEX idx_nama_lengkap ON pegawai(nama_lengkap);
CREATE INDEX idx_email ON pegawai(email);
CREATE INDEX idx_status_aktif ON pegawai(status_aktif);
CREATE INDEX idx_tipe_pegawai ON pegawai(tipe_pegawai);

-- Menambahkan constraint untuk memastikan email unik
ALTER TABLE pegawai ADD CONSTRAINT uk_email UNIQUE (email);