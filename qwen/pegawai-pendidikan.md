CREATE TABLE pendidikan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,

    jenjang ENUM('SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3') NOT NULL,
    nama_institusi VARCHAR(150) NOT NULL,
    jurusan VARCHAR(150),

    tahun_masuk YEAR,
    tahun_lulus YEAR,

    no_ijazah VARCHAR(50),
    tanggal_ijazah DATE,

    gelar_depan VARCHAR(20),
    gelar_belakang VARCHAR(50),

    status_terakhir BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pendidikan_pegawai
        FOREIGN KEY (pegawai_id)
        REFERENCES pegawai(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE pendidikan_berkas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendidikan_id INT NOT NULL,

    jenis_berkas ENUM('Ijazah', 'Transkrip', 'SK Penyetaraan', 'Lainnya') NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    path_file VARCHAR(255) NOT NULL,

    ukuran_file INT, -- dalam byte
    tipe_file VARCHAR(50), -- pdf, jpg, png

    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_berkas_pendidikan
        FOREIGN KEY (pendidikan_id)
        REFERENCES pendidikan(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


saya ingin menambahkan modul pendidikan dengan struktur database diatas,
update halaman pegawai/detail.php juga ditambahkan pendidikan
akses nya sama seperti keluarga, dan sesuaikan dengan modul pegawai_keluarga

