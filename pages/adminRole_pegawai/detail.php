<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$pegawaiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pegawaiId) {
    redirect('index.php?page=adminRole_pegawai');
}

// Get pegawai data
$stmt = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->execute([$pegawaiId]);
$pegawai = $stmt->fetch();

if (!$pegawai) {
    setAlert('error', 'Pegawai tidak ditemukan!');
    redirect('index.php?page=adminRole_pegawai');
}

// Get employee family data (limited to first 5 for display)
$stmtFamily = $pdo->prepare("SELECT * FROM pegawai_keluarga WHERE pegawai_id = ? ORDER BY created_at DESC LIMIT 5");
$stmtFamily->execute([$pegawaiId]);
$families = $stmtFamily->fetchAll();

// Count total family members
$stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM pegawai_keluarga WHERE pegawai_id = ?");
$stmtCount->execute([$pegawaiId]);
$totalFamilies = $stmtCount->fetch()['total'];
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_pegawai" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Pegawai</h1>
            <p class="text-gray-500 mt-1">Informasi lengkap pegawai dan keluarga</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row gap-6">
        <div class="md:w-1/3">
            <div class="text-center">
                <img src="<?php echo getImageUrl($pegawai['foto'], 'pegawai'); ?>" 
                     class="w-32 h-32 rounded-full object-cover mx-auto mb-4 border-4 border-gray-200"
                     alt="<?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>">
                <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($pegawai['nama_lengkap']); ?></h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($pegawai['email']); ?></p>
                <div class="mt-4">
                    <?php if ($pegawai['status_aktif'] === 'aktif'): ?>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-green-500 text-white">
                            Aktif
                        </span>
                    <?php elseif ($pegawai['status_aktif'] === 'non-aktif'): ?>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-yellow-500 text-white">
                            Non-Aktif
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-gray-500 text-white">
                            Pensiun
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="md:w-2/3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Nomor Induk</p>
                    <p class="font-medium"><?php echo htmlspecialchars($pegawai['nomor_induk'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">NIK</p>
                    <p class="font-medium"><?php echo htmlspecialchars($pegawai['nik'] ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Jenis Kelamin</p>
                    <p class="font-medium"><?php echo htmlspecialchars($pegawai['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tempat Lahir</p>
                    <p class="font-medium"><?php echo htmlspecialchars($pegawai['tempat_lahir']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal Lahir</p>
                    <p class="font-medium"><?php echo htmlspecialchars(date('d M Y', strtotime($pegawai['tanggal_lahir']))); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tipe Pegawai</p>
                    <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pegawai['tipe_pegawai']))); ?></p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Alamat</p>
                    <p class="font-medium"><?php echo htmlspecialchars($pegawai['alamat']); ?></p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">No HP</p>
                    <p class="font-medium"><?php echo htmlspecialchars($pegawai['no_hp']); ?></p>
                </div>
            </div>
            
            <div class="mt-6 flex space-x-3">
                <a href="index.php?page=adminRole_pegawai&action=edit&id=<?php echo $pegawai['id']; ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-xl transition-colors">
                    Edit Pegawai
                </a>
                <a href="index.php?page=adminRole_pegawai" 
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-xl transition-colors">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Family Information Section -->
<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Keluarga</h2>
    </div>

    <?php if (count($families) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hubungan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Hidup</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Tanggungan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($families as $family): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($family['nama']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($family['hubungan']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $family['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($family['status_hidup']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $family['status_tanggungan'] ? 'Ya' : 'Tidak'; ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalFamilies > 5): ?>
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-500"><?php echo $totalFamilies - 5; ?> anggota keluarga lainnya...</p>
        </div>
        <?php endif; ?>

        <div class="mt-4 text-center">
            <a href="index.php?page=adminRole_pegawai_keluarga&pegawai_id=<?php echo $pegawai['id']; ?>"
               class="text-blue-600 hover:text-blue-800 font-medium">
                Lihat semua <?php echo $totalFamilies; ?> anggota keluarga &rarr;
            </a>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data keluarga</h3>
            <p class="mt-1 text-sm text-gray-500">Pegawai ini belum memiliki data keluarga.</p>
            <div class="mt-6">
                <a href="index.php?page=adminRole_pegawai_keluarga&action=create&pegawai_id=<?php echo $pegawaiId; ?>"
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none">
                    Tambahkan Keluarga
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Employment History Section -->
<?php
// Get employee history data (limited to first 5 for display)
$stmtHistory = $pdo->prepare("SELECT * FROM riwayat_kepegawaian WHERE pegawai_id = ? ORDER BY tanggal_efektif DESC, created_at DESC LIMIT 5");
$stmtHistory->execute([$pegawaiId]);
$histories = $stmtHistory->fetchAll();

// Count total history records
$stmtCountHist = $pdo->prepare("SELECT COUNT(*) as total FROM riwayat_kepegawaian WHERE pegawai_id = ?");
$stmtCountHist->execute([$pegawaiId]);
$totalHistories = $stmtCountHist->fetch()['total'];
?>
<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Riwayat Kepegawaian</h2>
    </div>

    <?php if (count($histories) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Perubahan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Efektif</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokumen Pendukung</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($histories as $history): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $history['jenis_perubahan']))); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars(date('d M Y', strtotime($history['tanggal_efektif']))); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($history['keterangan'] ?? '-'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (!empty($history['dokumen_sk_id'])): ?>
                                <?php
                                // Get document info
                                $stmtDoc = $pdo->prepare("SELECT dokumen_sk, nomor_sk FROM dokumen_sk WHERE id = ?");
                                $stmtDoc->execute([$history['dokumen_sk_id']]);
                                $document = $stmtDoc->fetch();

                                if ($document && !empty($document['dokumen_sk'])):
                                ?>
                                    <a href="<?php echo getDocumentUrl($document['dokumen_sk'], 'dokumen_sk'); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                        <?php echo htmlspecialchars($document['nomor_sk'] ?: 'Lihat Dokumen'); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">-</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-500 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalHistories > 5): ?>
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-500"><?php echo $totalHistories - 5; ?> riwayat kepegawaian lainnya...</p>
        </div>
        <?php endif; ?>

        <div class="mt-4 text-center">
            <a href="index.php?page=adminRole_pegawai_riwayat&pegawai_id=<?php echo $pegawai['id']; ?>"
               class="text-blue-600 hover:text-blue-800 font-medium">
                Lihat semua <?php echo $totalHistories; ?> riwayat kepegawaian &rarr;
            </a>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada riwayat kepegawaian</h3>
            <p class="mt-1 text-sm text-gray-500">Pegawai ini belum memiliki riwayat perubahan kepegawaian.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Education Information Section with Files -->
<?php
// Get employee education data (limited to first 5 for display)
$stmtEducation = $pdo->prepare("SELECT * FROM pendidikan WHERE pegawai_id = ? ORDER BY tahun_lulus DESC LIMIT 5");
$stmtEducation->execute([$pegawaiId]);
$educations = $stmtEducation->fetchAll();

// Count total education records
$stmtCountEdu = $pdo->prepare("SELECT COUNT(*) as total FROM pendidikan WHERE pegawai_id = ?");
$stmtCountEdu->execute([$pegawaiId]);
$totalEducations = $stmtCountEdu->fetch()['total'];

// Get all education files for this employee
$stmtAllFiles = $pdo->prepare("
    SELECT pb.*, p.jenjang, p.nama_institusi
    FROM pendidikan_berkas pb
    LEFT JOIN pendidikan p ON pb.pendidikan_id = p.id
    WHERE p.pegawai_id = ?
    ORDER BY pb.pendidikan_id, pb.jenis_berkas
");
$stmtAllFiles->execute([$pegawaiId]);
$allEducationFiles = $stmtAllFiles->fetchAll();

// Group files by education ID
$filesByEducation = [];
foreach ($allEducationFiles as $file) {
    if (!isset($filesByEducation[$file['pendidikan_id']])) {
        $filesByEducation[$file['pendidikan_id']] = [];
    }
    $filesByEducation[$file['pendidikan_id']][] = $file;
}
?>
<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Pendidikan</h2>
    </div>

    <?php if (count($educations) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenjang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institusi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurusan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun Lulus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Terakhir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berkas</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($educations as $edu): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($edu['jenjang']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($edu['nama_institusi']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($edu['jurusan'] ?? '-'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($edu['tahun_lulus'] ?? '-'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $edu['status_terakhir'] ? 'Ya' : 'Tidak'; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (isset($filesByEducation[$edu['id']]) && count($filesByEducation[$edu['id']]) > 0): ?>
                                <?php foreach ($filesByEducation[$edu['id']] as $file): ?>
                                    <div class="text-sm">
                                        <a href="<?php echo getEducationFileUrl(htmlspecialchars($file['path_file'])); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                            <?php echo htmlspecialchars($file['jenis_berkas']); ?>: <?php echo htmlspecialchars($file['nama_file']); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-sm text-gray-500">Belum ada berkas</div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalEducations > 5): ?>
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-500"><?php echo $totalEducations - 5; ?> riwayat pendidikan lainnya...</p>
        </div>
        <?php endif; ?>

        <div class="mt-4 text-center">
            <a href="index.php?page=adminRole_pegawai_pendidikan&pegawai_id=<?php echo $pegawai['id']; ?>"
               class="text-blue-600 hover:text-blue-800 font-medium">
                Lihat semua <?php echo $totalEducations; ?> riwayat pendidikan &rarr;
            </a>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data pendidikan</h3>
            <p class="mt-1 text-sm text-gray-500">Pegawai ini belum memiliki data pendidikan.</p>
            <div class="mt-6">
                <a href="index.php?page=adminRole_pegawai_pendidikan&action=create&pegawai_id=<?php echo $pegawaiId; ?>"
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                    Tambahkan Pendidikan
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

