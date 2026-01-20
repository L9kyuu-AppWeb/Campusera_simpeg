CREATE TABLE saldo_cuti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT,
    tahun YEAR,
    total_cuti INT,
    sisa_cuti INT,
    sumber ENUM('default','custom') DEFAULT 'default',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
);