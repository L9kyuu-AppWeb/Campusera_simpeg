<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Detail Keluarga Pegawai</h1>
    <p class="text-gray-500 mt-1">Informasi lengkap tentang anggota keluarga</p>
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <!-- Family Member Header with Photo -->
    <?php if (isset($keluarga['foto']) && $keluarga['foto']): ?>
    <div class="relative">
        <img src="../../assets/uploads/pegawai_keluarga/<?php echo htmlspecialchars($keluarga['foto']); ?>"
             class="w-full h-64 object-cover"
             alt="Foto Keluarga">
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
            <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($keluarga['nama'] ?? '-'); ?></h2>
            <p class="text-blue-100"><?php
            $hubungan = $keluarga['hubungan'] ?? '';
            echo htmlspecialchars($hubungan === 'Suami' ? 'Suami' : ($hubungan === 'Istri' ? 'Istri' : ucfirst(str_replace('_', ' ', $hubungan ?? ''))));
            ?></p>
        </div>
    </div>
    <?php else: ?>
    <!-- Fallback header if no photo -->
    <div class="relative">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-32"></div>
        <div class="absolute bottom-0 left-6 pb-6">
            <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($keluarga['nama'] ?? '-'); ?></h2>
            <p class="text-blue-100"><?php
            $hubungan = $keluarga['hubungan'] ?? '';
            echo htmlspecialchars($hubungan === 'Suami' ? 'Suami' : ($hubungan === 'Istri' ? 'Istri' : ucfirst(str_replace('_', ' ', $hubungan ?? ''))));
            ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Family Member Details -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Nama Lengkap</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['nama'] ?? '-'); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Jenis Keluarga</p>
                        <p class="font-medium"><?php
                        $hubungan = $keluarga['hubungan'] ?? '';
                        echo htmlspecialchars($hubungan === 'Suami' ? 'Suami' : ($hubungan === 'Istri' ? 'Istri' : ucfirst(str_replace('_', ' ', $hubungan ?? ''))));
                        ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">No. KTP</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['no_ktp'] ?? '-'); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">No. KK</p>
                        <p class="font-medium"><?php echo htmlspecialchars($keluarga['no_kk'] ?? '-'); ?></p>
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
                        <p class="font-medium"><?php echo htmlspecialchars(isset($keluarga['tanggal_lahir']) && $keluarga['tanggal_lahir'] ? date('d M Y', strtotime($keluarga['tanggal_lahir'])) : '-'); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Status Hidup</p>
                        <p class="font-medium"><?php echo htmlspecialchars(isset($keluarga['status_hidup']) ? ucfirst(str_replace('_', ' ', $keluarga['status_hidup'])) : '-'); ?></p>
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
                        <p class="text-sm text-gray-500">Status Tanggungan</p>
                        <p class="font-medium"><?php echo htmlspecialchars(isset($keluarga['status_tanggungan']) && $keluarga['status_tanggungan'] ? 'Ya' : 'Tidak'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
            <a href="index.php?page=adminRole_pegawai_keluarga" class="px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                Kembali
            </a>
            <a href="index.php?page=adminRole_pegawai_keluarga&action=edit&id=<?php echo $keluarga['id']; ?>" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                Edit
            </a>
        </div>
    </div>
</div>