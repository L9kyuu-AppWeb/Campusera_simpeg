-- Schema untuk tabel PRODI
-- Berdasarkan skema dari prodi.md
-- Hanya admin yang bisa mengakses
-- Mengikuti struktur modul games

CREATE TABLE IF NOT EXISTS prodi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fakultas_id INT NOT NULL,
    kode_prodi VARCHAR(20) NOT NULL UNIQUE,
    nama_prodi VARCHAR(255) NOT NULL,
    jenjang ENUM('D3', 'D4', 'S1', 'S2', 'S3') NOT NULL,
    kaprodi_id INT,
    akreditasi VARCHAR(10),
    kuota_mahasiswa INT DEFAULT 0,
    status_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fakultas_id) REFERENCES fakultas(id),
    FOREIGN KEY (kaprodi_id) REFERENCES dosen(id)
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_fakultas_id ON prodi(fakultas_id);
CREATE INDEX idx_kode_prodi ON prodi(kode_prodi);
CREATE INDEX idx_nama_prodi ON prodi(nama_prodi);
CREATE INDEX idx_jenjang ON prodi(jenjang);
CREATE INDEX idx_kaprodi_id ON prodi(kaprodi_id);