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


-- Seed data for faculties (fakultas)
INSERT INTO fakultas (id, nama_fakultas, kode_fakultas, created_at, updated_at) VALUES
(1, 'Fakultas Teknik', 'FT', NOW(), NOW()),
(2, 'Fakultas Ilmu Komputer', 'FIKOM', NOW(), NOW()),
(3, 'Fakultas Ekonomi', 'FE', NOW(), NOW()),
(4, 'Fakultas Hukum', 'FH', NOW(), NOW()),
(5, 'Fakultas Kedokteran', 'FK', NOW(), NOW()),
(6, 'Fakultas Matematika dan Ilmu Pengetahuan Alam', 'FMIPA', NOW(), NOW()),
(7, 'Fakultas Ilmu Sosial dan Ilmu Politik', 'FISIP', NOW(), NOW()),
(8, 'Fakultas Keguruan dan Ilmu Pendidikan', 'FKIP', NOW(), NOW()),
(9, 'Fakultas Pertanian', 'FP', NOW(), NOW()),
(10, 'Fakultas Seni dan Desain', 'FSD', NOW(), NOW());

-- Seed data for study programs (prodi)
INSERT INTO prodi (id, nama_prodi, kode_prodi, fakultas_id, created_at, updated_at) VALUES
(1, 'Teknik Informatika', 'TI', 1, NOW(), NOW()),
(2, 'Teknik Elektro', 'TE', 1, NOW(), NOW()),
(3, 'Teknik Mesin', 'TM', 1, NOW(), NOW()),
(4, 'Teknik Industri', 'TI2', 1, NOW(), NOW()),
(5, 'Sistem Informasi', 'SI', 2, NOW(), NOW()),
(6, 'Ilmu Komunikasi', 'IK', 2, NOW(), NOW()),
(7, 'Manajemen', 'M', 3, NOW(), NOW()),
(8, 'Akuntansi', 'A', 3, NOW(), NOW()),
(9, 'Ilmu Hukum', 'IH', 4, NOW(), NOW()),
(10, 'Kedokteran Umum', 'KU', 5, NOW(), NOW()),
(11, 'Matematika', 'MAT', 6, NOW(), NOW()),
(12, 'Fisika', 'FIS', 6, NOW(), NOW()),
(13, 'Kimia', 'KIM', 6, NOW(), NOW()),
(14, 'Biologi', 'BIO', 6, NOW(), NOW()),
(15, 'Ilmu Politik', 'IP', 7, NOW(), NOW()),
(16, 'Sosiologi', 'SOS', 7, NOW(), NOW()),
(17, 'Pendidikan Guru Sekolah Dasar', 'PGSD', 8, NOW(), NOW()),
(18, 'Pendidikan Bahasa Indonesia', 'PBI', 8, NOW(), NOW()),
(19, 'Agroteknologi', 'AGRO', 9, NOW(), NOW()),
(20, 'Teknologi Hasil Pertanian', 'THP', 9, NOW(), NOW()),
(21, 'Desain Komunikasi Visual', 'DKV', 10, NOW(), NOW()),
(22, 'Desain Produk', 'DP', 10, NOW(), NOW());

-- Seed data for leave types (jenis_izin)
INSERT INTO jenis_izin (id_jenis_izin, nama_izin, keterangan, is_potong_cuti) VALUES
(1, 'Izin Sakit', 'Izin karena sakit dengan surat keterangan dokter', 1),
(2, 'Izin Menikah', 'Izin untuk menikah', 1),
(3, 'Izin Melahirkan', 'Izin untuk melahirkan/anak', 1),
(4, 'Izin Keperluan Keluarga Mendesak', 'Izin karena keperluan keluarga mendesak', 1),
(5, 'Izin Keperluan Dinas', 'Izin karena keperluan dinas', 0),
(6, 'Cuti Tahunan', 'Cuti tahunan reguler', 1),
(7, 'Cuti Alasan Penting', 'Cuti karena alasan penting', 1),
(8, 'Cuti Diluar Tanggungan Negara', 'Cuti diluar tanggungan negara', 0),
(9, 'Izin Pengurusan Keperluan Pribadi', 'Izin untuk pengurusan keperluan pribadi', 0),
(10, 'Izin Cuti Bersama', 'Izin cuti bersama yang ditentukan pemerintah', 1);

-- Seed data for annual leave entitlements (master_cuti)
INSERT INTO master_cuti (id, role_id, total_cuti) VALUES
(1, 1, 12), -- Default for role admin
(2, 2, 12), -- Default for role dosen
(3, 3, 12); -- Default for role tendik

-- Seed data for employees (pegawai)
INSERT INTO pegawai (id, nomor_induk, nik, nama_lengkap, email, no_hp, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, status_aktif, tipe_pegawai) VALUES
(1, 'NIP001', '1234567890123456', 'Ahmad Hidayat', 'ahmad.hidayat@example.com', '081234567890', 'Jakarta', '1985-05-15', 'L', 'Jl. Merdeka No. 10, Jakarta Pusat', 'aktif', 'dosen_tetap'),
(2, 'NIP002', '1234567890123457', 'Siti Nurhaliza', 'siti.nurhaliza@example.com', '081234567891', 'Bandung', '1988-07-22', 'P', 'Jl. Diponegoro No. 25, Bandung', 'aktif', 'dosen_tetap'),
(3, 'NIP003', '1234567890123458', 'Budi Santoso', 'budi.santoso@example.com', '081234567892', 'Surabaya', '1982-11-30', 'L', 'Jl. Ahmad Yani No. 45, Surabaya', 'aktif', 'tendik_tetap'),
(4, 'NIP004', '1234567890123459', 'Dewi Kartika', 'dewi.kartika@example.com', '081234567893', 'Yogyakarta', '1990-03-18', 'P', 'Jl. Malioboro No. 12, Yogyakarta', 'aktif', 'tendik_tetap'),
(5, 'NIP005', '1234567890123460', 'Rizki Pratama', 'rizki.pratama@example.com', '081234567894', 'Medan', '1987-09-10', 'L', 'Jl. Thamrin No. 8, Medan', 'aktif', 'dosen_luar'),
(6, 'NIP006', '1234567890123461', 'Lina Marlina', 'lina.marlina@example.com', '081234567895', 'Semarang', '1989-12-25', 'P', 'Jl. Pandanaran No. 30, Semarang', 'aktif', 'tendik_kontrak');

-- Seed data for leave balances (saldo_cuti)
INSERT INTO saldo_cuti (id, pegawai_id, tahun, total_cuti, sisa_cuti, sumber) VALUES
(1, 1, 2024, 12, 10, 'default'),
(2, 2, 2024, 12, 8, 'default'),
(3, 3, 2024, 12, 12, 'default'),
(4, 4, 2024, 12, 5, 'default'),
(5, 5, 2024, 12, 11, 'default'),
(6, 6, 2024, 12, 9, 'default');

-- Seed data for employee family members (pegawai_keluarga)
INSERT INTO pegawai_keluarga (id, pegawai_id, nama, hubungan, jenis_kelamin, tempat_lahir, tanggal_lahir, pendidikan_terakhir, pekerjaan, status_hidup, status_tanggungan, no_ktp, no_kk, foto) VALUES
(1, 1, 'Sri Lestari', 'Istri', 'P', 'Bandung', '1986-08-12', 'S1', 'Guru', 'Hidup', 1, '1234567890123456', '1234567890123456', NULL),
(2, 1, 'Budi Prasetyo', 'Anak', 'L', 'Jakarta', '2010-03-15', 'SD', NULL, 'Hidup', 1, '1234567890123457', '1234567890123456', NULL),
(3, 2, 'Agus Santoso', 'Suami', 'L', 'Surabaya', '1985-11-20', 'S2', 'Dosen', 'Hidup', 1, '1234567890123458', '1234567890123458', NULL),
(4, 2, 'Citra Kirana', 'Anak', 'P', 'Bandung', '2015-07-05', 'TK', NULL, 'Hidup', 1, '1234567890123459', '1234567890123458', NULL),
(5, 3, 'Ratna Dewi', 'Istri', 'P', 'Malang', '1984-01-30', 'D3', 'Perawat', 'Hidup', 1, '1234567890123460', '1234567890123460', NULL),
(6, 3, 'Andi Pratama', 'Anak', 'L', 'Surabaya', '2012-12-22', 'SD', NULL, 'Hidup', 1, '1234567890123461', '1234567890123460', NULL);

-- Seed data for employee education (pendidikan)
INSERT INTO pendidikan (id, pegawai_id, jenjang, nama_institusi, jurusan, tahun_masuk, tahun_lulus, no_ijazah, tanggal_ijazah, gelar_depan, gelar_belakang, status_terakhir) VALUES
(1, 1, 'S2', 'Universitas Indonesia', 'Teknik Informatika', 2005, 2007, '123456789012', '2007-09-15', NULL, 'S.T., M.T.', 1),
(2, 1, 'S1', 'Institut Teknologi Bandung', 'Teknik Elektro', 2001, 2005, '123456789011', '2005-07-20', NULL, 'S.T.', 0),
(3, 2, 'S2', 'Universitas Gadjah Mada', 'Ilmu Komunikasi', 2010, 2012, '123456789013', '2012-11-10', NULL, 'S.I.Kom., M.I.Kom.', 1),
(4, 2, 'S1', 'Universitas Padjadjaran', 'Ilmu Komunikasi', 2006, 2010, '123456789014', '2010-06-15', NULL, 'S.I.Kom.', 0),
(5, 3, 'D3', 'Politeknik Negeri Jakarta', 'Akuntansi', 2000, 2003, '123456789015', '2003-07-10', NULL, 'A.Md.', 1),
(6, 4, 'S1', 'Universitas Negeri Yogyakarta', 'Pendidikan Bahasa Inggris', 2008, 2012, '123456789016', '2012-09-05', NULL, 'S.Pd.', 1);

-- Seed data for education documents (pendidikan_berkas)
INSERT INTO pendidikan_berkas (id, pendidikan_id, jenis_berkas, nama_file, path_file, ukuran_file, tipe_file) VALUES
(1, 1, 'Ijazah', 'ijazah_s2_ahmad_hid.pdf', 'uploads/pendidikan/1/ijazah_s2_ahmad_hid.pdf', 2048576, 'pdf'),
(2, 1, 'Transkrip', 'transkrip_s2_ahmad_hid.pdf', 'uploads/pendidikan/1/transkrip_s2_ahmad_hid.pdf', 1536432, 'pdf'),
(3, 2, 'Ijazah', 'ijazah_s1_ahmad_hid.pdf', 'uploads/pendidikan/2/ijazah_s1_ahmad_hid.pdf', 1843256, 'pdf'),
(4, 2, 'Transkrip', 'transkrip_s1_ahmad_hid.pdf', 'uploads/pendidikan/2/transkrip_s1_ahmad_hid.pdf', 1425678, 'pdf'),
(5, 3, 'Ijazah', 'ijazah_s2_siti_nur.pdf', 'uploads/pendidikan/3/ijazah_s2_siti_nur.pdf', 1967890, 'pdf'),
(6, 3, 'Transkrip', 'transkrip_s2_siti_nur.pdf', 'uploads/pendidikan/3/transkrip_s2_siti_nur.pdf', 1654321, 'pdf'),
(7, 4, 'Ijazah', 'ijazah_s1_siti_nur.pdf', 'uploads/pendidikan/4/ijazah_s1_siti_nur.pdf', 1789012, 'pdf'),
(8, 5, 'Ijazah', 'ijazah_d3_budi_san.pdf', 'uploads/pendidikan/5/ijazah_d3_budi_san.pdf', 1678901, 'pdf'),
(9, 6, 'Ijazah', 'ijazah_s1_dewi_kar.pdf', 'uploads/pendidikan/6/ijazah_s1_dewi_kar.pdf', 1890123, 'pdf');

-- Seed data for lecturers (dosen)
INSERT INTO dosen (id, pegawai_id, nidn, nidk, prodi_homebase_id, status_dosen, status_ikatan, jenjang_pendidikan, bidang_keahlian, jabatan_fungsional, tanggal_mulai_mengajar, sertifikat_pendidik, no_sertifikat_pendidik) VALUES
(1, 1, '0123456789', 'DK001', 1, 'tetap_yayasan', 'tetap', 'S2', 'Teknik Informatika', 'lektor', '2010-08-01', 1, '0123456789/Sertifikat-Pendidik'),
(2, 2, '0987654321', 'DK002', 5, 'tetap_dikti', 'tetap', 'S2', 'Ilmu Komunikasi', 'lektor', '2013-08-01', 1, '0987654321/Sertifikat-Pendidik'),
(3, 5, NULL, 'DL001', 2, 'luar_biasa', 'part_time', 'S2', 'Sistem Informasi', 'lektor_kepala', '2015-01-15', 1, '0147258369/Sertifikat-Pendidik');

-- Seed data for unit_kerja
INSERT INTO unit_kerja (id, nama_unit, tipe_unit, parent_id, kepala_unit_id, created_at) VALUES
(1, 'Universitas ABC', 'lembaga', NULL, NULL, NOW()),
(2, 'Fakultas Teknik', 'fakultas', 1, NULL, NOW()),
(3, 'Fakultas Ilmu Komputer', 'fakultas', 1, NULL, NOW()),
(4, 'Biro Administrasi Akademik', 'biro', 1, NULL, NOW()),
(5, 'Biro Administrasi Umum', 'biro', 1, NULL, NOW()),
(6, 'Biro Keuangan', 'biro', 1, NULL, NOW()),
(7, 'Pusat Teknologi Informasi', 'pusat', 1, NULL, NOW());

-- Seed data for dosen-prodi relationship
INSERT INTO dosen_prodi (id, dosen_id, prodi_id, status_hubungan, is_kaprodi, tanggal_mulai, tanggal_selesai) VALUES
(1, 1, 1, 'homebase', 1, '2010-08-01', NULL), -- Ahmad Hidayat - Teknik Informatika (Kaprodi)
(2, 1, 4, 'pengampu', 0, '2015-01-01', '2017-12-31'), -- Ahmad Hidayat - Teknik Industri (Dosen Pengampu)
(3, 2, 5, 'homebase', 1, '2013-08-01', NULL), -- Siti Nurhaliza - Sistem Informasi (Kaprodi)
(4, 2, 6, 'pengampu', 0, '2018-01-01', NULL), -- Siti Nurhaliza - Ilmu Komunikasi (Dosen Pengampu)
(5, 3, 2, 'homebase', 0, '2015-01-15', NULL), -- Rizki Pratama - Teknik Elektro (Dosen Luar Biasa)
(6, 3, 5, 'pengampu', 0, '2016-01-01', NULL); -- Rizki Pratama - Sistem Informasi (Dosen Pengampu)

-- Seed data for tendik
INSERT INTO tendik (id, pegawai_id, nip, unit_kerja_id, jabatan, status_kepegawaian, golongan, tanggal_mulai_kerja, tanggal_selesai) VALUES
(1, 3, 'NIP003', 4, 'Staff Administrasi Akademik', 'tetap', 'III/B', '2010-06-01', NULL), -- Budi Santoso - Biro Administrasi Akademik
(2, 6, NULL, 5, 'Staff Administrasi Umum', 'kontrak', NULL, '2015-07-01', NULL); -- Lina Marlina - Biro Administrasi Umum (Kontrak)