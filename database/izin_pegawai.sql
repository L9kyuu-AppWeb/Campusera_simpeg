CREATE TABLE izin_pegawai (
    id_izin INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    jenis_izin_id INT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    jumlah_hari INT,
    keterangan TEXT,
    file_bukti VARCHAR(255),
    status ENUM('Menunggu','Disetujui','Ditolak') DEFAULT 'Menunggu',
    disetujui_oleh INT NULL,
    catatan_atasan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);