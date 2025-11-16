<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';

switch ($action) {
    case 'create':
        require_once 'create.php';
        break;
    case 'edit':
        require_once 'edit.php';
        break;
    case 'delete':
        require_once 'delete.php';
        break;
    case 'reset_password':
        require_once 'reset_password.php';
        break;
    default:
        // List pegawai
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $statusFilter = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
        $tipeFilter = isset($_GET['tipe']) ? cleanInput($_GET['tipe']) : '';

        $sql = "SELECT * FROM pegawai WHERE 1=1";

        if ($search) {
            $sql .= " AND (nama_lengkap LIKE :search1 OR email LIKE :search2 OR nomor_induk LIKE :search3 OR nik LIKE :search4)";
        }

        if ($statusFilter) {
            $sql .= " AND status_aktif = :status";
        }

        if ($tipeFilter) {
            $sql .= " AND tipe_pegawai = :tipe";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
            $stmt->bindValue(':search4', "%$search%");
        }
        if ($statusFilter) {
            $stmt->bindValue(':status', $statusFilter);
        }
        if ($tipeFilter) {
            $stmt->bindValue(':tipe', $tipeFilter);
        }

        $stmt->execute();
        $pegawai = $stmt->fetchAll();

        // Get distinct status and tipe for filter
        $status = $pdo->query("SELECT DISTINCT status_aktif FROM pegawai WHERE status_aktif IS NOT NULL ORDER BY status_aktif")->fetchAll();
        $tipe = $pdo->query("SELECT DISTINCT tipe_pegawai FROM pegawai WHERE tipe_pegawai IS NOT NULL ORDER BY tipe_pegawai")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Pegawai Management</h1>
        <p class="text-gray-500 mt-1">Kelola data pegawai</p>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <a href="index.php?page=pegawai&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Pegawai</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="pegawai">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama, email, nomor induk, atau NIK..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="status" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Status</option>
            <?php foreach ($status as $s): ?>
                <option value="<?php echo $s['status_aktif']; ?>" <?php echo $statusFilter === $s['status_aktif'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $s['status_aktif'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="tipe" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Tipe</option>
            <?php foreach ($tipe as $t): ?>
                <option value="<?php echo $t['tipe_pegawai']; ?>" <?php echo $tipeFilter === $t['tipe_pegawai'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $t['tipe_pegawai'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $statusFilter || $tipeFilter): ?>
        <a href="index.php?page=pegawai" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Pegawai Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($pegawai) > 0): ?>
        <?php foreach ($pegawai as $p): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Pegawai Image -->
                <div class="relative">
                    <img src="<?php echo getImageUrl($p['foto'], 'pegawai'); ?>"
                         class="w-full h-48 object-cover"
                         alt="<?php echo htmlspecialchars($p['nama_lengkap']); ?>">
                    <div class="absolute top-3 right-3">
                        <?php if ($p['status_aktif'] === 'aktif'): ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-green-500 text-white">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Aktif
                            </span>
                        <?php elseif ($p['status_aktif'] === 'non-aktif'): ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-yellow-500 text-white">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Non-Aktif
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-gray-500 text-white">
                                Pensiun
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pegawai Content -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($p['nama_lengkap']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars($p['email']); ?>
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars($p['tipe_pegawai'] === 'dosen_luar' ? 'Dosen Luar' : (strpos($p['tipe_pegawai'], 'dosen') !== false ? 'Dosen Tetap' : 'Tendik')); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Nomor Induk</p>
                            <p class="font-medium"><?php echo htmlspecialchars($p['nomor_induk'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">NIK</p>
                            <p class="font-medium"><?php echo htmlspecialchars($p['nik'] ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Jenis Kelamin</p>
                            <p class="font-medium"><?php echo htmlspecialchars($p['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tempat Lahir</p>
                            <p class="font-medium"><?php echo htmlspecialchars($p['tempat_lahir']); ?></p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars(date('d M Y', strtotime($p['tanggal_lahir']))); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=pegawai&action=edit&id=<?php echo $p['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=pegawai&action=delete&id=<?php echo $p['id']; ?>"
                               class="text-red-600 hover:text-red-800 transition-colors" title="Hapus">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </a>
                            <form method="POST" action="index.php?page=pegawai&action=reset_password" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mereset password pegawai ini ke default?');">
                                <input type="hidden" name="pegawai_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" class="text-yellow-600 hover:text-yellow-800 transition-colors" title="Reset Password">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full text-center py-12">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data pegawai</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>