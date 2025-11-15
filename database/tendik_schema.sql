-- Schema untuk tabel TENDIK
-- Berdasarkan skema dari tendik.md
-- Hanya admin yang bisa mengakses
-- Mengikuti struktur modul games

CREATE TABLE IF NOT EXISTS tendik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT UNIQUE NOT NULL,
    nip VARCHAR(50) NULL COMMENT 'NULL untuk kontrak',
    unit_kerja_id INT NOT NULL,
    jabatan VARCHAR(255),
    status_kepegawaian ENUM('tetap', 'kontrak', 'honorer') NOT NULL,
    golongan VARCHAR(20) COMMENT 'untuk PNS',
    tanggal_mulai_kerja DATE,
    tanggal_selesai DATE NULL COMMENT 'NULL jika aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id)
    -- Catatan: Karena unit_kerja_id digunakan tapi tabel unit_kerja belum dibuat, 
    -- maka foreign key ke unit_kerja akan ditambahkan setelah tabel unit_kerja dibuat
    -- FOREIGN KEY (unit_kerja_id) REFERENCES unit_kerja(id)
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_pegawai_id ON tendik(pegawai_id);
CREATE INDEX idx_nip ON tendik(nip);
CREATE INDEX idx_status_kepegawaian ON tendik(status_kepegawaian);
CREATE INDEX idx_jabatan ON tendik(jabatan);