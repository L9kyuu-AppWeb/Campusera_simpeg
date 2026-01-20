schema tabel,
CREATE TABLE master_cuti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT,
    total_cuti INT DEFAULT 12
);
relasi ke tabel roles,

yang bisa akses admin;
untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms;
buatkan modul master izin kode samakan dengan modul games;
pada bagian sidebar letakkan di bagian Pengaturan jika tidak ada bisa dibuatkan;
