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
    case 'edit_keterangan':
        require_once 'edit_keterangan.php';
        break;
    default:
        // List riwayat_kepegawaian
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $jenisFilter = isset($_GET['jenis']) ? cleanInput($_GET['jenis']) : '';

        $sql = "SELECT rk.*, p.nama_lengkap, p.email, ds.nomor_sk, ds.judul as judul_sk, ds.dokumen_sk as dokumen_nama FROM riwayat_kepegawaian rk
                JOIN pegawai p ON rk.pegawai_id = p.id
                LEFT JOIN dokumen_sk ds ON rk.dokumen_sk_id = ds.id
                WHERE 1=1";

        if ($search) {
            $sql .= " AND (p.nama_lengkap LIKE :search1 OR p.email LIKE :search2 OR rk.keterangan LIKE :search3 OR ds.nomor_sk LIKE :search4 OR ds.judul LIKE :search5)";
        }

        if ($jenisFilter) {
            $sql .= " AND rk.jenis_perubahan = :jenis";
        }

        $sql .= " ORDER BY rk.tanggal_efektif DESC, rk.created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
            $stmt->bindValue(':search4', "%$search%");
            $stmt->bindValue(':search5', "%$search%");
        }
        if ($jenisFilter) {
            $stmt->bindValue(':jenis', $jenisFilter);
        }

        $stmt->execute();
        $riwayat = $stmt->fetchAll();

        // Get distinct jenis perubahan for filter
        $jenis = $pdo->query("SELECT DISTINCT jenis_perubahan FROM riwayat_kepegawaian WHERE jenis_perubahan IS NOT NULL ORDER BY jenis_perubahan")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Riwayat Kepegawaian Management</h1>
        <p class="text-gray-500 mt-1">Kelola data riwayat perubahan kepegawaian</p>
    </div>
    <?php if (hasRole(['admin'])): ?>
    <a href="index.php?page=riwayat_kepegawaian&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Riwayat</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="riwayat_kepegawaian">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari nama pegawai, email, atau keterangan..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="jenis" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Jenis</option>
            <?php foreach ($jenis as $j): ?>
                <option value="<?php echo $j['jenis_perubahan']; ?>" <?php echo $jenisFilter === $j['jenis_perubahan'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $j['jenis_perubahan'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $jenisFilter): ?>
        <a href="index.php?page=riwayat_kepegawaian" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Riwayat Kepegawaian Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($riwayat) > 0): ?>
        <?php foreach ($riwayat as $r): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Riwayat Content -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($r['nama_lengkap']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars($r['email']); ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Jenis Perubahan</p>
                            <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $r['jenis_perubahan']))); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Tanggal Efektif</p>
                            <p class="font-medium"><?php echo htmlspecialchars(date('d M Y', strtotime($r['tanggal_efektif']))); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Keterangan</p>
                            <p class="font-medium"><?php echo htmlspecialchars($r['keterangan'] ?? '-'); ?></p>
                        </div>
                        <?php if ($r['dokumen_nama']): ?>
                        <div>
                            <p class="text-xs text-gray-500">Dokumen SK</p>
                            <a href="<?php echo getDocumentUrl($r['dokumen_nama'], 'dokumen_sk'); ?>"
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium break-words">
                                <?php echo htmlspecialchars($r['nomor_sk'] ?: 'Lihat Dokumen'); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars('Dibuat: ' . date('d M Y H:i', strtotime($r['created_at']))); ?>
                        </div>

                        <div class="flex space-x-2">
                            <a href="index.php?page=riwayat_kepegawaian&action=edit&id=<?php echo $r['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=riwayat_kepegawaian&action=edit_keterangan&id=<?php echo $r['id']; ?>"
                               class="text-green-600 hover:text-green-800 transition-colors" title="Edit Keterangan">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=riwayat_kepegawaian&action=delete&id=<?php echo $r['id']; ?>"
                               class="text-red-600 hover:text-red-800 transition-colors" title="Hapus">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full text-center py-12">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data riwayat kepegawaian</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>