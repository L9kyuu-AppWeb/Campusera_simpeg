schema tabel, 
DOSEN {
        int id PK
        int pegawai_id FK "UNIQUE"
        string nidn "NULL untuk dosen luar"
        string nidk "NULL"
        int prodi_homebase_id FK
        enum status_dosen "tetap_yayasan/tetap_dikti/luar_biasa/honorer"
        enum status_ikatan "tetap/kontrak/part_time"
        string jenjang_pendidikan "S1/S2/S3"
        string bidang_keahlian
        enum jabatan_fungsional "asisten_ahli/lektor/lektor_kepala/guru_besar"
        date tanggal_mulai_mengajar
        date tanggal_selesai "NULL jika aktif"
        boolean sertifikat_pendidik
        string no_sertifikat_pendidik
        timestamp created_at
        timestamp updated_at
    }

yang bisa akses admin, untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms

buatkan modul pegawai kode samakan dengan modul games