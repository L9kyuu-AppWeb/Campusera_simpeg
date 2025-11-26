-- ==========================================
-- Unified Database Schema for Campusera Simpeg
-- Combines all schemas with foreign keys at the end
-- ==========================================

-- Create database
CREATE DATABASE IF NOT EXISTS campusera_simpeg;
USE campusera_simpeg;

-- ==========================================
-- Table: roles (no dependencies)
-- ==========================================
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================  
-- Table: pegawai (no dependencies)
-- ==========================================
CREATE TABLE IF NOT EXISTS pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_induk VARCHAR(50) NULL COMMENT 'NULL for dosen luar',
    nik VARCHAR(50) NULL COMMENT 'NULL for dosen luar',
    nama_lengkap VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    tempat_lahir VARCHAR(100) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL COMMENT 'L/P',
    alamat TEXT NOT NULL,
    foto VARCHAR(255) NULL,
    status_aktif ENUM('aktif', 'non-aktif', 'pensiun') NOT NULL,
    tipe_pegawai ENUM('dosen_tetap', 'dosen_luar', 'tendik_tetap', 'tendik_kontrak') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================
-- Table: dokumen_sk (no dependencies)
-- ==========================================
CREATE TABLE IF NOT EXISTS dokumen_sk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_sk VARCHAR(100) NOT NULL,
    judul VARCHAR(255) NOT NULL,
    tanggal_sk DATE NOT NULL,
    jenis_perubahan ENUM('jabatan_struktural_fungsional', 'tugas_tambahan_akademik', 'kepanitiaan_resmi', 'penugasan_khusus', 'pelatihan_sertifikasi_pengembangan_kompetensi', 'penghargaan_reward_pegawai') NOT NULL,
    dokumen_sk VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================  
-- Table: fakultas (depends on dosen, but dosen depends on pegawai)
-- ==========================================
CREATE TABLE IF NOT EXISTS fakultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_fakultas VARCHAR(20) NOT NULL UNIQUE,
    nama_fakultas VARCHAR(255) NOT NULL,
    dekan_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================  
-- Table: dosen (depends on pegawai)
-- ==========================================
CREATE TABLE IF NOT EXISTS dosen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT UNIQUE,
    nidn VARCHAR(50) NULL COMMENT 'NULL untuk dosen luar',
    nidk VARCHAR(50) NULL,
    prodi_homebase_id INT,
    status_dosen ENUM('tetap_yayasan', 'tetap_dikti', 'luar_biasa', 'honorer'),
    status_ikatan ENUM('tetap', 'kontrak', 'part_time'),
    jenjang_pendidikan ENUM('S1', 'S2', 'S3'),
    bidang_keahlian VARCHAR(255),
    jabatan_fungsional ENUM('asisten_ahli', 'lektor', 'lektor_kepala', 'guru_besar'),
    tanggal_mulai_mengajar DATE,
    tanggal_selesai DATE NULL COMMENT 'NULL jika aktif',
    sertifikat_pendidik BOOLEAN DEFAULT FALSE,
    no_sertifikat_pendidik VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================  
-- Table: prodi (depends on fakultas and dosen)
-- ==========================================
CREATE TABLE IF NOT EXISTS prodi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fakultas_id INT NOT NULL,
    kode_prodi VARCHAR(20) NOT NULL UNIQUE,
    nama_prodi VARCHAR(255) NOT NULL,
    jenjang ENUM('D3', 'D4', 'S1', 'S2', 'S3') NOT NULL,
    kaprodi_id INT,
    akreditasi VARCHAR(10),
    kuota_mahasiswa INT DEFAULT 0,
    status_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================  
-- Table: unit_kerja (self-referencing and depends on pegawai)
-- ==========================================
CREATE TABLE IF NOT EXISTS unit_kerja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_unit VARCHAR(255) NOT NULL,
    tipe_unit ENUM('fakultas', 'prodi', 'biro', 'pusat', 'lembaga') NOT NULL,
    parent_id INT NULL,
    kepala_unit_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================  
-- Table: games (no dependencies)
-- ==========================================
CREATE TABLE IF NOT EXISTS games (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    genre VARCHAR(100),
    release_date DATE,
    platform VARCHAR(100),
    price DECIMAL(10, 2),
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================  
-- Table: users (depends on roles)
-- ==========================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    role_id INT NOT NULL DEFAULT 4,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================  
-- Table: activity_logs (depends on users)
-- ==========================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================  
-- Table: sessions (depends on users)
-- ==========================================
CREATE TABLE IF NOT EXISTS sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Table: riwayat_kepegawaian (depends on pegawai and dokumen_sk)
-- ==========================================
CREATE TABLE IF NOT EXISTS riwayat_kepegawaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    dokumen_sk_id INT NULL,  -- Ini digunakan untuk menghubungkan ke dokumen SK yang bisa digunakan bersama
    jenis_perubahan ENUM('jabatan_struktural_fungsional', 'tugas_tambahan_akademik', 'kepanitiaan_resmi', 'penugasan_khusus', 'pelatihan_sertifikasi_pengembangan_kompetensi', 'penghargaan_reward_pegawai') NOT NULL,
    keterangan TEXT,
    tanggal_efektif DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================  
-- Table: dokumen_sk_pegawai (depends on dokumen_sk and pegawai)
-- ==========================================
CREATE TABLE IF NOT EXISTS dokumen_sk_pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dokumen_sk_id INT NOT NULL,
    pegawai_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_dokumen_pegawai (dokumen_sk_id, pegawai_id)
);

-- ==========================================  
-- Table: tendik (depends on pegawai and unit_kerja)
-- ==========================================
CREATE TABLE IF NOT EXISTS tendik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT UNIQUE NOT NULL,
    nip VARCHAR(50) NULL COMMENT 'NULL untuk kontrak',
    unit_kerja_id INT NOT NULL,
    jabatan VARCHAR(255),
    status_kepegawaian ENUM('tetap', 'kontrak', 'honorer') NOT NULL,
    golongan VARCHAR(20) COMMENT 'untuk PNS',
    tanggal_mulai_kerja DATE,
    tanggal_selesai DATE NULL COMMENT 'NULL jika aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==========================================  
-- Table: dosen_prodi (depends on dosen and prodi)
-- ==========================================
CREATE TABLE IF NOT EXISTS dosen_prodi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dosen_id INT NOT NULL,
    prodi_id INT NOT NULL,
    status_hubungan ENUM('homebase', 'pengampu', 'tamu') NOT NULL,
    is_kaprodi BOOLEAN DEFAULT FALSE,
    tanggal_mulai DATE,
    tanggal_selesai DATE NULL COMMENT 'NULL jika aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_dosen_prodi (dosen_id, prodi_id, status_hubungan)
);

-- ==========================================
-- Create indexes for all tables
-- ==========================================

-- Indexes for roles table
CREATE INDEX idx_role_name ON roles(role_name);

-- Indexes for pegawai table
CREATE INDEX idx_nomor_induk ON pegawai(nomor_induk);
CREATE INDEX idx_nik ON pegawai(nik);
CREATE INDEX idx_nama_lengkap ON pegawai(nama_lengkap);
CREATE INDEX idx_email ON pegawai(email);
CREATE INDEX idx_status_aktif ON pegawai(status_aktif);
CREATE INDEX idx_tipe_pegawai ON pegawai(tipe_pegawai);

-- Indexes for dokumen_sk table
CREATE INDEX idx_nomor_sk ON dokumen_sk(nomor_sk);
CREATE INDEX idx_jenis_perubahan ON dokumen_sk(jenis_perubahan);
CREATE INDEX idx_tanggal_sk ON dokumen_sk(tanggal_sk);

-- Indexes for fakultas table
CREATE INDEX idx_kode_fakultas ON fakultas(kode_fakultas);
CREATE INDEX idx_nama_fakultas ON fakultas(nama_fakultas);
CREATE INDEX idx_dekan_id ON fakultas(dekan_id);

-- Indexes for dosen table
CREATE INDEX idx_pegawai_id ON dosen(pegawai_id);
CREATE INDEX idx_nidn ON dosen(nidn);
CREATE INDEX idx_prodi_homebase_id ON dosen(prodi_homebase_id);
CREATE INDEX idx_status_dosen ON dosen(status_dosen);
CREATE INDEX idx_jabatan_fungsional ON dosen(jabatan_fungsional);

-- Indexes for prodi table
CREATE INDEX idx_fakultas_id ON prodi(fakultas_id);
CREATE INDEX idx_kode_prodi ON prodi(kode_prodi);
CREATE INDEX idx_nama_prodi ON prodi(nama_prodi);
CREATE INDEX idx_jenjang ON prodi(jenjang);
CREATE INDEX idx_kaprodi_id ON prodi(kaprodi_id);

-- Indexes for unit_kerja table
CREATE INDEX idx_nama_unit ON unit_kerja(nama_unit);
CREATE INDEX idx_tipe_unit ON unit_kerja(tipe_unit);
CREATE INDEX idx_parent_id ON unit_kerja(parent_id);
CREATE INDEX idx_kepala_unit_id ON unit_kerja(kepala_unit_id);

-- Indexes for games table
CREATE INDEX idx_title ON games(title);
CREATE INDEX idx_genre ON games(genre);
CREATE INDEX idx_platform ON games(platform);
CREATE INDEX idx_is_active ON games(is_active);

-- Indexes for riwayat_kepegawaian table
CREATE INDEX idx_pegawai_id ON riwayat_kepegawaian(pegawai_id);
CREATE INDEX idx_jenis_perubahan ON riwayat_kepegawaian(jenis_perubahan);
CREATE INDEX idx_tanggal_efektif ON riwayat_kepegawaian(tanggal_efektif);
CREATE INDEX idx_dokumen_sk_id ON riwayat_kepegawaian(dokumen_sk_id);

-- Indexes for dokumen_sk_pegawai table
CREATE INDEX idx_dokumen_sk_pegawai_dokumen_id ON dokumen_sk_pegawai(dokumen_sk_id);
CREATE INDEX idx_dokumen_sk_pegawai_pegawai_id ON dokumen_sk_pegawai(pegawai_id);

-- Indexes for tendik table
CREATE INDEX idx_pegawai_id ON tendik(pegawai_id);
CREATE INDEX idx_nip ON tendik(nip);
CREATE INDEX idx_status_kepegawaian ON tendik(status_kepegawaian);
CREATE INDEX idx_jabatan ON tendik(jabatan);

-- Indexes for dosen_prodi table
CREATE INDEX idx_dosen_id ON dosen_prodi(dosen_id);
CREATE INDEX idx_prodi_id ON dosen_prodi(prodi_id);
CREATE INDEX idx_status_hubungan ON dosen_prodi(status_hubungan);
CREATE INDEX idx_is_kaprodi ON dosen_prodi(is_kaprodi);

-- ==========================================
-- Add foreign key constraints at the end to avoid dependency errors
-- ==========================================

-- Foreign keys for users table
ALTER TABLE users ADD CONSTRAINT fk_users_role_id FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT;

-- Foreign keys for activity_logs table
ALTER TABLE activity_logs ADD CONSTRAINT fk_activity_logs_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Foreign keys for sessions table
ALTER TABLE sessions ADD CONSTRAINT fk_sessions_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Foreign keys for fakultas table
ALTER TABLE fakultas ADD CONSTRAINT fk_fakultas_dekan_id FOREIGN KEY (dekan_id) REFERENCES dosen(id) ON DELETE SET NULL;

-- Foreign keys for dosen table
ALTER TABLE dosen ADD CONSTRAINT fk_dosen_pegawai_id FOREIGN KEY (pegawai_id) REFERENCES pegawai(id);
ALTER TABLE dosen ADD CONSTRAINT fk_dosen_prodi_homebase_id FOREIGN KEY (prodi_homebase_id) REFERENCES prodi(id) ON DELETE SET NULL;

-- Foreign keys for prodi table
ALTER TABLE prodi ADD CONSTRAINT fk_prodi_fakultas_id FOREIGN KEY (fakultas_id) REFERENCES fakultas(id);
ALTER TABLE prodi ADD CONSTRAINT fk_prodi_kaprodi_id FOREIGN KEY (kaprodi_id) REFERENCES dosen(id) ON DELETE SET NULL;

-- Foreign keys for unit_kerja table
ALTER TABLE unit_kerja ADD CONSTRAINT fk_unit_kerja_parent_id FOREIGN KEY (parent_id) REFERENCES unit_kerja(id);
ALTER TABLE unit_kerja ADD CONSTRAINT fk_unit_kerja_kepala_unit_id FOREIGN KEY (kepala_unit_id) REFERENCES pegawai(id);

-- Foreign keys for riwayat_kepegawaian table
ALTER TABLE riwayat_kepegawaian ADD CONSTRAINT fk_riwayat_kepegawaian_pegawai_id FOREIGN KEY (pegawai_id) REFERENCES pegawai(id);
ALTER TABLE riwayat_kepegawaian ADD CONSTRAINT fk_riwayat_kepegawaian_dokumen_sk_id FOREIGN KEY (dokumen_sk_id) REFERENCES dokumen_sk(id);

-- Foreign keys for dokumen_sk_pegawai table
ALTER TABLE dokumen_sk_pegawai ADD CONSTRAINT fk_dokumen_sk_pegawai_dokumen_sk_id FOREIGN KEY (dokumen_sk_id) REFERENCES dokumen_sk(id) ON DELETE CASCADE;
ALTER TABLE dokumen_sk_pegawai ADD CONSTRAINT fk_dokumen_sk_pegawai_pegawai_id FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE;

-- Foreign keys for tendik table
ALTER TABLE tendik ADD CONSTRAINT fk_tendik_pegawai_id FOREIGN KEY (pegawai_id) REFERENCES pegawai(id);
ALTER TABLE tendik ADD CONSTRAINT fk_tendik_unit_kerja_id FOREIGN KEY (unit_kerja_id) REFERENCES unit_kerja(id);

-- Foreign keys for dosen_prodi table
ALTER TABLE dosen_prodi ADD CONSTRAINT fk_dosen_prodi_dosen_id FOREIGN KEY (dosen_id) REFERENCES dosen(id);
ALTER TABLE dosen_prodi ADD CONSTRAINT fk_dosen_prodi_prodi_id FOREIGN KEY (prodi_id) REFERENCES prodi(id);

-- ==========================================
-- Insert default data
-- ==========================================

-- Insert default roles
INSERT INTO roles (role_name, description) VALUES
('admin', 'Full system access with all permissions'),
('dosen', 'Management level access with limited permissions'),
('tendik', 'Basic staff access for daily operations')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default admin user
-- Username: admin
-- Password: admin123
INSERT INTO users (username, email, password, first_name, last_name, role_id, is_active) VALUES
('admin', 'admin@example.com', '$2y$10$vuT0T56.1nqR1mzWBGKKH.lILeAA7EvUjyBTnBmaCVwuixoZgfKqy', 'Admin', 'User', 1, 1)
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Insert sample activity logs
INSERT INTO activity_logs (user_id, activity_type, description, ip_address) VALUES
(1, 'login', 'User logged in', '127.0.0.1')
ON DUPLICATE KEY UPDATE activity_type = VALUES(activity_type);

-- Insert sample games for testing
INSERT INTO games (title, description, genre, release_date, platform, price, image, is_active) VALUES
('Super Mario Odyssey', 'An action-adventure platform game featuring Mario and Cappy', 'Platform', '2017-10-27', 'Nintendo Switch', 59.99, 'mario-odyssey.jpg', 1),
('The Legend of Zelda: Breath of the Wild', 'Open-world action-adventure game in the Zelda series', 'Adventure', '2017-03-03', 'Nintendo Switch', 69.99, 'zelda-botw.jpg', 1),
('Cyberpunk 2077', 'Action role-playing video game set in Night City', 'RPG', '2020-12-10', 'PC, PS4, PS5, Xbox One, Xbox Series X/S', 59.99, 'cyberpunk2077.jpg', 1),
('The Last of Us Part II', 'Post-apocalyptic survival horror game', 'Action', '2020-06-19', 'PlayStation 4', 49.99, 'last-of-us-part2.jpg', 1),
('Red Dead Redemption 2', 'Western action-adventure game set in 1899', 'Action', '2018-10-26', 'PC, PS4, Xbox One', 59.99, 'red-dead-redemption2.jpg', 1)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- ==========================================
-- Add unique constraints after table creation
-- ==========================================

-- Unique constraint for pegawai email
ALTER TABLE pegawai ADD CONSTRAINT uk_email UNIQUE (email);

-- ==========================================
-- Views for easy data access
-- ==========================================

-- View: users with role information
CREATE OR REPLACE VIEW v_users_with_roles AS
SELECT
    u.id,
    u.username,
    u.email,
    u.first_name,
    u.last_name,
    u.phone,
    u.avatar,
    u.is_active,
    u.last_login,
    u.created_at,
    u.updated_at,
    r.role_name,
    r.description as role_description
FROM users u
JOIN roles r ON u.role_id = r.id;

-- View: recent activities with user info
CREATE OR REPLACE VIEW v_recent_activities AS
SELECT
    al.id,
    al.activity_type,
    al.description,
    al.ip_address,
    al.created_at,
    u.username,
    u.first_name,
    u.last_name,
    u.email
FROM activity_logs al
LEFT JOIN users u ON al.user_id = u.id
ORDER BY al.created_at DESC;

-- Create view for active games
CREATE OR REPLACE VIEW v_active_games AS
SELECT
    id,
    title,
    description,
    genre,
    release_date,
    platform,
    price,
    image,
    is_active,
    created_at,
    updated_at
FROM games
WHERE is_active = 1;

-- ==========================================
-- Additional indexes
-- ==========================================

CREATE INDEX idx_users_role_active ON users(role_id, is_active);
CREATE INDEX idx_activity_user_date ON activity_logs(user_id, created_at);

-- ==========================================
-- Stored Procedures (Optional)
-- ==========================================

DELIMITER //

-- Procedure: Get user statistics
CREATE PROCEDURE sp_get_user_statistics()
BEGIN
    SELECT
        COUNT(*) as total_users,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users,
        SUM(CASE WHEN role_id = 1 THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN role_id = 2 THEN 1 ELSE 0 END) as manager_count,
        SUM(CASE WHEN role_id = 3 THEN 1 ELSE 0 END) as staff_count,
        SUM(CASE WHEN role_id = 4 THEN 1 ELSE 0 END) as user_count
    FROM users;
END //

-- Procedure: Clean old activity logs (older than 90 days)
CREATE PROCEDURE sp_clean_old_logs()
BEGIN
    DELETE FROM activity_logs
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
END //

-- ==========================================
-- Triggers
-- ==========================================

-- Trigger: Log when user is created
CREATE TRIGGER tr_user_created
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, activity_type, description, ip_address)
    VALUES (NEW.id, 'user_created', CONCAT('New user account created: ', NEW.username), '127.0.0.1');
END //

DELIMITER ;

-- ==========================================
-- Database information
-- ==========================================

SELECT 'Unified database schema created successfully!' as message;
SELECT 'Default admin credentials:' as info, 'Username: admin, Password: admin123' as credentials;
SELECT 'Total tables created:' as info, COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'campusera_simpeg';