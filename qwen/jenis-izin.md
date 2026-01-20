schema tabel,
CREATE TABLE jenis_izin (
    id_jenis_izin INT AUTO_INCREMENT PRIMARY KEY,
    nama_izin VARCHAR(100),
    keterangan TEXT,
    is_potong_cuti BOOLEAN DEFAULT 0
);

yang bisa akses admin;
untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms;
buatkan modul jenis izin kode samakan dengan modul games;
pada bagian sidebar letakkan di bagian master;