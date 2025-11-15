schema tabel, 
FAKULTAS {
        int id PK
        string kode_fakultas
        string nama_fakultas
        int dekan_id FK "ke DOSEN"
        timestamp created_at
        timestamp updated_at
    }

yang bisa akses admin, untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms

buatkan modul pegawai kode samakan dengan modul games