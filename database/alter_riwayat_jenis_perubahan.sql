-- ALTER script to update jenis_perubahan ENUM values in existing databases
-- This script replaces the existing jenis_perubahan values with the new ones

-- Update dokumen_sk table
ALTER TABLE dokumen_sk MODIFY COLUMN jenis_perubahan ENUM('jabatan_struktural_fungsional', 'tugas_tambahan_akademik', 'kepanitiaan_resmi', 'penugasan_khusus', 'pelatihan_sertifikasi_pengembangan_kompetensi', 'penghargaan_reward_pegawai') NOT NULL;

-- Update riwayat_kepegawaian table
ALTER TABLE riwayat_kepegawaian MODIFY COLUMN jenis_perubahan ENUM('jabatan_struktural_fungsional', 'tugas_tambahan_akademik', 'kepanitiaan_resmi', 'penugasan_khusus', 'pelatihan_sertifikasi_pengembangan_kompetensi', 'penghargaan_reward_pegawai') NOT NULL;

-- Note: Existing records with old values (pengangkatan, promosi, mutasi, pensiun)
-- will need to be handled separately if migration is required

-- Update indexes if necessary (shouldn't be needed, but just in case)
-- The indexes were already created in the original schema and should still be valid