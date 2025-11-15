-- Schema untuk tabel DOSEN_PRODI
-- Berdasarkan skema dari dosen-prodi.md
-- Hanya admin yang bisa mengakses
-- Mengikuti struktur modul games

CREATE TABLE IF NOT EXISTS dosen_prodi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dosen_id INT NOT NULL,
    prodi_id INT NOT NULL,
    status_hubungan ENUM('homebase', 'pengampu', 'tamu') NOT NULL,
    is_kaprodi BOOLEAN DEFAULT FALSE,
    tanggal_mulai DATE,
    tanggal_selesai DATE NULL COMMENT 'NULL jika aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dosen_id) REFERENCES dosen(id),
    FOREIGN KEY (prodi_id) REFERENCES prodi(id),
    UNIQUE KEY unique_dosen_prodi (dosen_id, prodi_id, status_hubungan)
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_dosen_id ON dosen_prodi(dosen_id);
CREATE INDEX idx_prodi_id ON dosen_prodi(prodi_id);
CREATE INDEX idx_status_hubungan ON dosen_prodi(status_hubungan);
CREATE INDEX idx_is_kaprodi ON dosen_prodi(is_kaprodi);