-- Schema untuk tabel DOKUMEN_SK
-- Digunakan untuk menyimpan dokumen SK yang bisa digunakan oleh lebih dari satu pegawai
-- Hanya admin yang bisa mengakses
-- Mengikuti struktur modul games

CREATE TABLE IF NOT EXISTS dokumen_sk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_sk VARCHAR(100) NOT NULL,
    judul VARCHAR(255) NOT NULL,
    tanggal_sk DATE NOT NULL,
    jenis_perubahan ENUM('pengangkatan', 'promosi', 'mutasi', 'pensiun') NOT NULL,
    dokumen_sk VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_nomor_sk ON dokumen_sk(nomor_sk);
CREATE INDEX idx_jenis_perubahan ON dokumen_sk(jenis_perubahan);
CREATE INDEX idx_tanggal_sk ON dokumen_sk(tanggal_sk);

-- Schema untuk tabel RIWAYAT_KEPEGAWAIAN
-- Berdasarkan skema dari riwayat-pegawai.md
-- Hanya admin yang bisa mengakses
-- Mengikuti struktur modul games
-- Menggunakan dokumen_sk_id untuk menghubungkan ke satu dokumen SK yang bisa digunakan oleh banyak pegawai

CREATE TABLE IF NOT EXISTS riwayat_kepegawaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    dokumen_sk_id INT NULL,  -- Ini digunakan untuk menghubungkan ke dokumen SK yang bisa digunakan bersama
    jenis_perubahan ENUM('pengangkatan', 'promosi', 'mutasi', 'pensiun') NOT NULL,
    keterangan TEXT,
    tanggal_efektif DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id),
    FOREIGN KEY (dokumen_sk_id) REFERENCES dokumen_sk(id)
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_pegawai_id ON riwayat_kepegawaian(pegawai_id);
CREATE INDEX idx_jenis_perubahan ON riwayat_kepegawaian(jenis_perubahan);
CREATE INDEX idx_tanggal_efektif ON riwayat_kepegawaian(tanggal_efektif);
CREATE INDEX idx_dokumen_sk_id ON riwayat_kepegawaian(dokumen_sk_id);

-- Schema untuk tabel DOKUMEN_SK_PEGAWAI (many-to-many relationship)
-- Untuk menyimpan hubungan antara dokumen SK dan banyak pegawai
-- Berguna untuk SK yang mencakup lebih dari satu pegawai

CREATE TABLE IF NOT EXISTS dokumen_sk_pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dokumen_sk_id INT NOT NULL,
    pegawai_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dokumen_sk_id) REFERENCES dokumen_sk(id) ON DELETE CASCADE,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dokumen_pegawai (dokumen_sk_id, pegawai_id)
);

-- Menambahkan indeks untuk kolom-kolom yang sering digunakan dalam query
CREATE INDEX idx_dokumen_sk_pegawai_dokumen_id ON dokumen_sk_pegawai(dokumen_sk_id);
CREATE INDEX idx_dokumen_sk_pegawai_pegawai_id ON dokumen_sk_pegawai(pegawai_id);