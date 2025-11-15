-- Schema untuk tabel DOSEN
-- Berdasarkan skema dari dosen.md
-- Hanya admin yang bisa mengakses
-- Mengikuti struktur modul games

CREATE TABLE IF NOT EXISTS dosen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT UNIQUE,
    nidn VARCHAR(50) NULL COMMENT 'NULL untuk dosen luar',
    nidk VARCHAR(50) NULL,
    prodi_homebase_id INT,
    status_dosen ENUM('tetap_yayasan', 'tetap_dikti', 'luar_biasa', 'honorer'),
    status_ikatan ENUM('tetap', 'kontrak', 'part_time'),
    jenjang_pendidikan ENUM('S1', 'S2', 'S3'),
    bidang_keahlian VARCHAR(255),
    jabatan_fungsional ENUM('asisten_ahli', 'lektor', 'lektor_kepala', 'guru_besar'),
    tanggal_mulai_mengajar DATE,
    tanggal_selesai DATE NULL COMMENT 'NULL jika aktif',
    sertifikat_pendidik BOOLEAN DEFAULT FALSE,
    no_sertifikat_pendidik VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id),
    FOREIGN KEY (prodi_homebase_id) REFERENCES prodi(id)
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_pegawai_id ON dosen(pegawai_id);
CREATE INDEX idx_nidn ON dosen(nidn);
CREATE INDEX idx_prodi_homebase_id ON dosen(prodi_homebase_id);
CREATE INDEX idx_status_dosen ON dosen(status_dosen);
CREATE INDEX idx_jabatan_fungsional ON dosen(jabatan_fungsional);

-- Menambahkan constraint untuk memastikan kombinasi unik jika diperlukan
-- ALTER TABLE dosen ADD CONSTRAINT uk_dosen_nidn UNIQUE (nidn) WHERE nidn IS NOT NULL;