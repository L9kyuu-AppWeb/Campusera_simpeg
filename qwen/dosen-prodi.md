schema tabel, 
DOSEN_PRODI {
        int id PK
        int dosen_id FK
        int prodi_id FK
        enum status_hubungan "homebase/pengampu/tamu"
        boolean is_kaprodi
        date tanggal_mulai
        date tanggal_selesai "NULL jika aktif"
        timestamp created_at
    }

yang bisa akses admin, untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms

buatkan modul nya kode samakan dengan modul games

update form pada modul dosen bagian home base itu list dari prodi

saat dosen ditambahkan maka automatis akan menambahkan di dosen_prodi status hombase