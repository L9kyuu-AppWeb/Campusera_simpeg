<?php
// Check permission
if (!hasRole(['dosen'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// For dosen role, only allow viewing their own data
if (hasRole(['dosen'])) {
    // Get user info first
    $user_info_sql = "SELECT * FROM users WHERE id = :user_id";
    $user_stmt = $pdo->prepare($user_info_sql);
    $user_stmt->bindValue(':user_id', $current_user_id);
    $user_stmt->execute();
    $user_info = $user_stmt->fetch();

    // Try to find the dosen record associated with the current user
    $dosen_sql = "SELECT d.*, p.nama_lengkap, p.email, p.foto FROM dosen d
                  JOIN pegawai p ON d.pegawai_id = p.id
                  WHERE p.email = :email AND d.id = :id";

    $dosen_stmt = $pdo->prepare($dosen_sql);
    $dosen_stmt->bindValue(':email', $user_info['email']);
    $dosen_stmt->bindValue(':id', $id);
    $dosen_stmt->execute();
    $dosen = $dosen_stmt->fetch();

    if (!$dosen) {
        // If no dosen record found, show error
        echo "<div class='alert alert-warning'>Data dosen tidak ditemukan atau tidak terhubung dengan akun Anda.</div>";
        echo "<a href='index.php?page=dosenRole_mengajar'>Kembali ke Daftar</a>";
        exit;
    }
}
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Detail Tim Pengajar</h1>
    <p class="text-gray-500 mt-1">Informasi lengkap tentang dosen sebagai tim pengajar</p>
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <!-- Dosen Header -->
    <div class="relative">
        <img src="<?php echo getImageUrl($dosen['foto'], 'pegawai'); ?>"
             class="w-full h-64 object-cover"
             alt="<?php echo htmlspecialchars($dosen['nama_lengkap']); ?>">
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6 pt-12">
            <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($dosen['nama_lengkap']); ?></h2>
            <p class="text-gray-200"><?php echo htmlspecialchars($dosen['email']); ?></p>
        </div>
    </div>

    <!-- Dosen Details -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dosen</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Nama Lengkap</p>
                        <p class="font-medium"><?php echo htmlspecialchars($dosen['nama_lengkap']); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($dosen['email']); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">NIDN</p>
                        <p class="font-medium"><?php echo htmlspecialchars($dosen['nidn'] ?? '-'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">NIDK</p>
                        <p class="font-medium"><?php echo htmlspecialchars($dosen['nidk'] ?? '-'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Status Dosen</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $dosen['status_dosen']))); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Ikatan Kerja</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $dosen['status_ikatan']))); ?></p>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Tambahan</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Jenjang Pendidikan</p>
                        <p class="font-medium"><?php echo htmlspecialchars($dosen['jenjang_pendidikan']); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Jabatan Fungsional</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $dosen['jabatan_fungsional']))); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Sertifikat Pendidik</p>
                        <p class="font-medium"><?php echo htmlspecialchars($dosen['sertifikat_pendidik'] ? 'Ya' : 'Tidak'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Status Aktif</p>
                        <p class="font-medium"><?php echo htmlspecialchars(($dosen['is_active'] ?? true) ? 'Aktif' : 'Non-Aktif'); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Tanggal Bergabung</p>
                        <p class="font-medium"><?php echo htmlspecialchars(isset($dosen['created_at']) ? date('d M Y', strtotime($dosen['created_at'])) : '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dosen Prodi Information Section -->
        <?php
        // Get dosen_prodi information for this dosen
        $dosen_prodi_sql = "SELECT dp.*, pr.nama_prodi FROM dosen_prodi dp
                            JOIN prodi pr ON dp.prodi_id = pr.id
                            WHERE dp.dosen_id = :dosen_id";

        $dosen_prodi_stmt = $pdo->prepare($dosen_prodi_sql);
        $dosen_prodi_stmt->bindValue(':dosen_id', $dosen['id']);
        $dosen_prodi_stmt->execute();
        $dosen_prodi_list = $dosen_prodi_stmt->fetchAll();
        ?>
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Prodi yang Diampu</h3>
            <?php if (count($dosen_prodi_list) > 0): ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($dosen_prodi_list as $dp): ?>
                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($dp['nama_prodi']); ?></h4>
                                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Status Hubungan</p>
                                            <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $dp['status_hubungan']))); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Peran</p>
                                            <p class="font-medium">
                                                <?php
                                                if ($dp['is_kaprodi']) {
                                                    echo 'Ketua Program Studi';
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Tanggal Mulai</p>
                                            <p class="font-medium">
                                                <?php
                                                if ($dp['tanggal_mulai']) {
                                                    echo date('d M Y', strtotime($dp['tanggal_mulai']));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Tanggal Selesai</p>
                                            <p class="font-medium">
                                                <?php
                                                if ($dp['tanggal_selesai']) {
                                                    echo date('d M Y', strtotime($dp['tanggal_selesai']));
                                                } else {
                                                    echo 'Aktif';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 text-center text-gray-500">
                    Belum terdaftar di prodi manapun
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
            <a href="index.php?page=dosenRole_mengajar" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                Kembali
            </a>
        </div>
    </div>
</div>