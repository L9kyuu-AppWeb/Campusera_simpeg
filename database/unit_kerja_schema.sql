-- Schema untuk tabel UNIT_KERJA
-- Berdasarkan skema dari unit-kerja.md
-- Hanya admin yang bisa mengakses
-- Mengikuti struktur modul games

CREATE TABLE IF NOT EXISTS unit_kerja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_unit VARCHAR(255) NOT NULL,
    tipe_unit ENUM('fakultas', 'prodi', 'biro', 'pusat', 'lembaga') NOT NULL,
    parent_id INT NULL,
    kepala_unit_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES unit_kerja(id),
    FOREIGN KEY (kepala_unit_id) REFERENCES pegawai(id)
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_nama_unit ON unit_kerja(nama_unit);
CREATE INDEX idx_tipe_unit ON unit_kerja(tipe_unit);
CREATE INDEX idx_parent_id ON unit_kerja(parent_id);
CREATE INDEX idx_kepala_unit_id ON unit_kerja(kepala_unit_id);