schema tabel, 
UNIT_KERJA {
        int id PK
        string nama_unit
        enum tipe_unit "fakultas/prodi/biro/pusat/lembaga"
        int parent_id FK "self reference"
        int kepala_unit_id FK "ke TENDIK"
        timestamp created_at
    }

yang bisa akses admin, untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms

buatkan modul pegawai kode samakan dengan modul games