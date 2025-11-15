schema tabel, 
PRODI {
        int id PK
        int fakultas_id FK
        string kode_prodi
        string nama_prodi
        enum jenjang "D3/D4/S1/S2/S3"
        int kaprodi_id FK "ke DOSEN"
        string akreditasi
        int kuota_mahasiswa
        boolean status_aktif
        timestamp created_at
        timestamp updated_at
    }

yang bisa akses admin, untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms

buatkan modul pegawai kode samakan dengan modul games