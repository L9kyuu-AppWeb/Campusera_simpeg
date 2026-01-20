CREATE TABLE jenis_izin (
    id_jenis_izin INT AUTO_INCREMENT PRIMARY KEY,
    nama_izin VARCHAR(100),
    keterangan TEXT,
    is_potong_cuti BOOLEAN DEFAULT 0
);