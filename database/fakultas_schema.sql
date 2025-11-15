-- Schema untuk tabel FAKULTAS
-- Berdasarkan skema dari fakultas.md
-- Hanya admin yang bisa mengakses
-- Mengikuti struktur modul games

CREATE TABLE IF NOT EXISTS fakultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_fakultas VARCHAR(20) NOT NULL UNIQUE,
    nama_fakultas VARCHAR(255) NOT NULL,
    dekan_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dekan_id) REFERENCES dosen(id)
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_kode_fakultas ON fakultas(kode_fakultas);
CREATE INDEX idx_nama_fakultas ON fakultas(nama_fakultas);
CREATE INDEX idx_dekan_id ON fakultas(dekan_id);