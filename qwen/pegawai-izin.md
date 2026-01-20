schema tabel,
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


yang bisa akses admin;
untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms;
buatkan modul jenis izin kode samakan dengan modul games;
pada bagian sidebar letakkan di bagian Data Pegawai;