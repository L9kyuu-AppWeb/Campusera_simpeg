schema tabel,
CREATE TABLE saldo_cuti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT,
    tahun YEAR,
    total_cuti INT,
    sisa_cuti INT,
    sumber ENUM('default','custom') DEFAULT 'default',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

untuk pengambil nilai total cuti di ambil pada master izin;
tetapi untuk tabel pegawai tidak ada role_id cuman ada di tabel users;
dan untuk pembuatan saldo cuti dibuat secara keseluruhan tidak manual satu persatu;
dan untuk pembuatan ini pertahun, tetapi bisa juga pembuatan jika ada pegawai yang baru masuk di pertengahan tahun;
dan bisa juga untuk total saldo di edit manual jika ada ketentuan untuk salah satu pegawai;
buatkan modul saldo-izin kode samakan dengan modul games;
pada bagian sidebar letakkan di bagian Master;
