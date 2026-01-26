<?php
// Check permission
if (!hasRole(['admin', 'dosen', 'tendik'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// For dosen and tendik roles, only allow viewing their own family data
if (hasRole(['dosen', 'tendik'])) {
    // Get user info first
    $user_info_sql = "SELECT * FROM users WHERE id = :user_id";
    $user_stmt = $pdo->prepare($user_info_sql);
    $user_stmt->bindValue(':user_id', $current_user_id);
    $user_stmt->execute();
    $user_info = $user_stmt->fetch();

    // Get pegawai info based on user's email
    $pegawai_sql = "SELECT * FROM pegawai WHERE email = :email";
    $pegawai_stmt = $pdo->prepare($pegawai_sql);
    $pegawai_stmt->bindValue(':email', $user_info['email']);
    $pegawai_stmt->execute();
    $pegawai = $pegawai_stmt->fetch();

    if (!$pegawai) {
        // If no pegawai record found, show error
        require_once __DIR__ . '/../errors/404.php';
        exit;
    }

    // Get family data for this pegawai
    $sql = "SELECT * FROM pegawai_keluarga WHERE id = :id AND pegawai_id = :pegawai_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':pegawai_id', $pegawai['id']);
    $stmt->execute();
    $keluarga = $stmt->fetch();

    if (!$keluarga) {
        require_once __DIR__ . '/../errors/404.php';
        exit;
    }
} else {
    // For admin role, get any family data
    $sql = "SELECT * FROM pegawai_keluarga WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $keluarga = $stmt->fetch();
    
    if (!$keluarga) {
        require_once __DIR__ . '/../errors/404.php';
        exit;
    }
}
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Detail Keluarga Pegawai</h1>
    <p class="text-gray-500 mt-1">Informasi lengkap tentang anggota keluarga</p>
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <!-- Family Member Header -->
    <div class="relative">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-32"></div>
        <div class="absolute bottom-0 left-6 pb-6">
            <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($keluarga['nama_lengkap'] ?? '-'); ?></h2>
            <p class="text-blue-100"><?php
            $hubungan = $keluarga['hubungan'] ?? '';
            echo htmlspecialchars($hubungan === 'Suami' ? 'Suami' : ($hubungan === 'Istri' ? 'Istri' : ucfirst(str_replace('_', ' ', $hubungan ?? ''))));
            ?></p>
        </div>
    </div>

    <!-- Family Member Details -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Nama Lengkap</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['nama_lengkap'] ?? '-'); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Jenis Keluarga</p>
                        <p class="font-medium"><?php
                        $hubungan = $keluarga['hubungan'] ?? '';
                        echo htmlspecialchars($hubungan === 'Suami' ? 'Suami' : ($hubungan === 'Istri' ? 'Istri' : ucfirst(str_replace('_', ' ', $hubungan ?? ''))));
                        ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Nomor Induk</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['nomor_induk'] ?? '-'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">NIK</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['nik'] ?? '-'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Jenis Kelamin</p>
                        <p class="font-medium"><?php echo htmlspecialchars(($keluarga['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan'); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Tempat Lahir</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['tempat_lahir'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Tambahan</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Lahir</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['tanggal_lahir'] ? date('d M Y', strtotime($keluarga['tanggal_lahir'])) : '-'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Status Hidup</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $keluarga['status_hidup']))); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Pekerjaan</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['pekerjaan'] ?? '-'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Pendidikan Terakhir</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['pendidikan_terakhir'] ?? '-'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Nikah</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['tanggal_nikah'] ? date('d M Y', strtotime($keluarga['tanggal_nikah'])) : '-'); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Status BPJS</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['status_bpjs'] ? 'Aktif' : 'Tidak Aktif'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
            <a href="index.php?page=pegawai_keluarga" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                Kembali
            </a>
            <?php if (hasRole(['admin', 'dosen', 'tendik'])): ?>
            <a href="index.php?page=pegawai_keluarga&action=edit&id=<?php echo $keluarga['id']; ?>" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                Edit
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>