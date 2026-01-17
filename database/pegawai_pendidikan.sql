-- Create pendidikan table
CREATE TABLE IF NOT EXISTS pendidikan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    jenjang VARCHAR(10) NOT NULL COMMENT 'E.g., SD, SMP, SMA, D1, D2, D3, D4, S1, S2, S3',
    nama_institusi VARCHAR(255) NOT NULL,
    jurusan VARCHAR(255),
    tahun_masuk YEAR,
    tahun_lulus YEAR,
    no_ijazah VARCHAR(100),
    tanggal_ijazah DATE,
    gelar_depan VARCHAR(50),
    gelar_belakang VARCHAR(50),
    status_terakhir BOOLEAN DEFAULT FALSE COMMENT 'Indicates if this is the highest/latest education',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pendidikan_pegawai FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create pendidikan_berkas table
CREATE TABLE IF NOT EXISTS pendidikan_berkas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendidikan_id INT NOT NULL,
    jenis_berkas ENUM('Ijazah', 'Transkrip', 'SK Penyetaraan', 'Lainnya') NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    path_file VARCHAR(255) NOT NULL,
    ukuran_file INT COMMENT 'dalam byte',
    tipe_file VARCHAR(50) COMMENT 'pdf, jpg, png',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_berkas_pendidikan FOREIGN KEY (pendidikan_id) REFERENCES pendidikan(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_pendidikan_pegawai_id ON pendidikan(pegawai_id);
CREATE INDEX idx_pendidikan_jenjang ON pendidikan(jenjang);
CREATE INDEX idx_pendidikan_tahun_lulus ON pendidikan(tahun_lulus);
CREATE INDEX idx_pendidikan_status_terakhir ON pendidikan(status_terakhir);

CREATE INDEX idx_pendidikan_berkas_pendidikan_id ON pendidikan_berkas(pendidikan_id);
CREATE INDEX idx_pendidikan_berkas_jenis_berkas ON pendidikan_berkas(jenis_berkas);