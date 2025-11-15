schema tabel, 
RIWAYAT_KEPEGAWAIAN {
        int id PK
        int pegawai_id FK
        enum jenis_perubahan "pengangkatan/promosi/mutasi/pensiun"
        text keterangan
        date tanggal_efektif
        string dokumen_sk
        timestamp created_at
    }

yang bisa akses admin, untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms

buatkan modulnya kode samakan dengan modul games