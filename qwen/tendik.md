schema tabel, 
TENDIK {
        int id PK
        int pegawai_id FK "UNIQUE"
        string nip "NULL untuk kontrak"
        int unit_kerja_id FK
        string jabatan
        enum status_kepegawaian "tetap/kontrak/honorer"
        string golongan "untuk PNS"
        date tanggal_mulai_kerja
        date tanggal_selesai "NULL jika aktif"
        timestamp created_at
        timestamp updated_at
    }

yang bisa akses admin, untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms

buatkan modul pegawai kode samakan dengan modul games