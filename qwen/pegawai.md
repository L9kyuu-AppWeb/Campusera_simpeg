schema tabel, 
PEGAWAI {
        int id PK
        string nomor_induk "NULL for dosen luar"
        string nik "NULL for dosen luar"
        string nama_lengkap
        string email
        string no_hp
        string tempat_lahir
        date tanggal_lahir
        enum jenis_kelamin "L/P"
        text alamat
        string foto
        enum status_aktif "aktif/non-aktif/pensiun"
        enum tipe_pegawai "dosen_tetap/dosen_luar/tendik_tetap/tendik_kontrak"
        timestamp created_at
        timestamp updated_at
    }

yang bisa akses admin, untuk pembuatan schema database jangan update file lama, buat file baru agar mudah update ke dbms
pembuatan sama kan dengan modul games

buatkan modul pegawai kode samakan dengan modul games