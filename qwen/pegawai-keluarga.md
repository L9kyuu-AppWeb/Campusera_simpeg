CREATE TABLE pegawai_keluarga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,

    nama VARCHAR(100) NOT NULL,
    hubungan ENUM('Suami', 'Istri', 'Anak', 'Ayah', 'Ibu', 'Lainnya') NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,

    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,

    pendidikan_terakhir VARCHAR(50),
    pekerjaan VARCHAR(100),

    status_hidup ENUM('Hidup', 'Meninggal') DEFAULT 'Hidup',
    status_tanggungan BOOLEAN DEFAULT FALSE,

    no_ktp VARCHAR(20),
    no_kk VARCHAR(20),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_keluarga_pegawai
        FOREIGN KEY (pegawai_id)
        REFERENCES pegawai(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

saya ingin menambahkan modul keluarga dengan struktur database diatas,
buatkan tombol view detail pada modul pegawai, nanti dihalaman view deteil setiap pegawai ada akses untuk manajemen keluarga,
tapi nantinya juga ada yang lain selain deteil keluarga, agar kamu tau membuatkan detail lebih dari 1