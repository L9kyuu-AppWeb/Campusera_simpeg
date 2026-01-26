<?php
// Check permission
if (!hasRole(['admin', 'dosen', 'tendik'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// For dosen and tendik roles, only allow deleting their own family data
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete the photo file if it exists
    if ($keluarga['foto']) {
        $uploadDir = __DIR__ . '/../../assets/uploads/pegawai_keluarga/';
        $photoPath = $uploadDir . $keluarga['foto'];
        
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }
    }
    
    // Delete the record
    $deleteSql = "DELETE FROM pegawai_keluarga WHERE id = :id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->bindValue(':id', $id);
    
    if ($deleteStmt->execute()) {
        header("Location: index.php?page=pegawai_keluarga&success=Data keluarga berhasil dihapus");
        exit;
    } else {
        $error = "Terjadi kesalahan saat menghapus data";
    }
}
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Hapus Keluarga Pegawai</h1>
    <p class="text-gray-500 mt-1">Konfirmasi penghapusan data anggota keluarga</p>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <?php if (isset($error)): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Data Keluarga</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                <p class="text-sm text-gray-500">NIK</p>
                <p class="font-medium"><?php echo htmlspecialchars($keluarga['nik'] ?? '-'); ?></p>
            </div>
            
            <div>
                <p class="text-sm text-gray-500">Status Hidup</p>
                <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $keluarga['status_hidup'] ?? ''))); ?></p>
            </div>
        </div>
    </div>
    
    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
        <h4 class="font-medium text-yellow-800 mb-2">Peringatan!</h4>
        <p class="text-yellow-700">Anda akan menghapus data keluarga ini secara permanen. Tindakan ini tidak dapat dibatalkan.</p>
    </div>
    
    <form method="POST">
        <div class="flex justify-end space-x-3">
            <a href="index.php?page=pegawai_keluarga" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 transition-colors">
                Hapus Data
            </button>
        </div>
    </form>
</div>