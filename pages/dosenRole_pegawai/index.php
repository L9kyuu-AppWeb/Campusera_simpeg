<?php
// Check permission - allow only dosen role
if (!hasRole(['dosen'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'view';

switch ($action) {
    case 'edit':
        require_once 'edit.php';
        break;
    default:
        // For dosen role, show their own data
        if (hasRole(['dosen'])) {
            // Get user info first
            $user_info_sql = "SELECT * FROM users WHERE id = :user_id";
            $user_stmt = $pdo->prepare($user_info_sql);
            $user_stmt->bindValue(':user_id', $current_user_id);
            $user_stmt->execute();
            $user_info = $user_stmt->fetch();

            // Try to find the pegawai record associated with the current user
            // Only get pegawai records that are linked to dosen
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

            // First, get the pegawai ID associated with this user's email
            if ($id == 0) {
                $pegawai_sql = "SELECT p.id FROM pegawai p
                                JOIN dosen d ON p.id = d.pegawai_id
                                WHERE p.email = :email";

                $pegawai_stmt = $pdo->prepare($pegawai_sql);
                $pegawai_stmt->bindValue(':email', $user_info['email']);
                $pegawai_stmt->execute();
                $pegawai_result = $pegawai_stmt->fetch();

                if (!$pegawai_result) {
                    // If no pegawai record found or not linked to dosen, show error
                    echo "<div class='alert alert-warning'>Data pegawai tidak ditemukan atau tidak terhubung dengan akun Anda sebagai dosen.</div>";
                    echo "<a href='index.php?page=dashboard'>Kembali ke Dashboard</a>";
                    exit;
                }

                $id = $pegawai_result['id'];
            }

            $pegawai_sql = "SELECT p.* FROM pegawai p
                            JOIN dosen d ON p.id = d.pegawai_id
                            WHERE p.email = :email AND p.id = :id";

            $pegawai_stmt = $pdo->prepare($pegawai_sql);
            $pegawai_stmt->bindValue(':email', $user_info['email']);
            $pegawai_stmt->bindValue(':id', $id);
            $pegawai_stmt->execute();
            $pegawai = $pegawai_stmt->fetch();

            if (!$pegawai) {
                // If no pegawai record found or not linked to dosen, show error
                echo "<div class='alert alert-warning'>Data pegawai tidak ditemukan atau tidak terhubung dengan akun Anda sebagai dosen.</div>";
                echo "<a href='index.php?page=dashboard'>Kembali ke Dashboard</a>";
                exit;
            }

            // Get the dosen record for this pegawai
            $dosen_sql = "SELECT d.* FROM dosen d
                          WHERE d.pegawai_id = :pegawai_id";

            $dosen_stmt = $pdo->prepare($dosen_sql);
            $dosen_stmt->bindValue(':pegawai_id', $pegawai['id']);
            $dosen_stmt->execute();
            $dosen_data = $dosen_stmt->fetch();

            // Add dosen status to the pegawai data
            if ($dosen_data) {
                $pegawai['dosen_status'] = 'exists';
                $pegawai['dosen_info'] = $dosen_data;
            } else {
                $pegawai['dosen_status'] = 'missing';
            }
            
            // Get additional related data for the employee
            // Get family data
            $keluarga_sql = "SELECT * FROM pegawai_keluarga WHERE pegawai_id = :pegawai_id ORDER BY created_at DESC";
            $keluarga_stmt = $pdo->prepare($keluarga_sql);
            $keluarga_stmt->bindValue(':pegawai_id', $pegawai['id']);
            $keluarga_stmt->execute();
            $pegawai['keluarga'] = $keluarga_stmt->fetchAll();
            
            // Get education data
            $pendidikan_sql = "SELECT * FROM pendidikan WHERE pegawai_id = :pegawai_id ORDER BY tahun_lulus DESC";
            $pendidikan_stmt = $pdo->prepare($pendidikan_sql);
            $pendidikan_stmt->bindValue(':pegawai_id', $pegawai['id']);
            $pendidikan_stmt->execute();
            $pegawai['pendidikan'] = $pendidikan_stmt->fetchAll();
            
            // Get employment history
            $riwayat_sql = "SELECT * FROM riwayat_kepegawaian WHERE pegawai_id = :pegawai_id ORDER BY tanggal_efektif DESC";
            $riwayat_stmt = $pdo->prepare($riwayat_sql);
            $riwayat_stmt->bindValue(':pegawai_id', $pegawai['id']);
            $riwayat_stmt->execute();
            $pegawai['riwayat_kepegawaian'] = $riwayat_stmt->fetchAll();
            
            // Get document SK data
            $dokumen_sql = "SELECT ds.* FROM dokumen_sk ds 
                            JOIN dokumen_sk_pegawai dsp ON ds.id = dsp.dokumen_sk_id
                            WHERE dsp.pegawai_id = :pegawai_id
                            ORDER BY ds.tanggal_sk DESC";
            $dokumen_stmt = $pdo->prepare($dokumen_sql);
            $dokumen_stmt->bindValue(':pegawai_id', $pegawai['id']);
            $dokumen_stmt->execute();
            $pegawai['dokumen_sk'] = $dokumen_stmt->fetchAll();
            
            // Get dosen-prodi relationship data
            $dosen_prodi_sql = "SELECT dp.*, pr.nama_prodi FROM dosen_prodi dp
                                JOIN prodi pr ON dp.prodi_id = pr.id
                                JOIN dosen d ON dp.dosen_id = d.id
                                WHERE d.pegawai_id = :pegawai_id
                                ORDER BY dp.tanggal_mulai DESC";
            $dosen_prodi_stmt = $pdo->prepare($dosen_prodi_sql);
            $dosen_prodi_stmt->bindValue(':pegawai_id', $pegawai['id']);
            $dosen_prodi_stmt->execute();
            $pegawai['dosen_prodi'] = $dosen_prodi_stmt->fetchAll();
            
            // Get cuti data
            $cuti_sql = "SELECT ic.*, ji.nama_izin FROM izin_pegawai ic
                         JOIN jenis_izin ji ON ic.jenis_izin_id = ji.id_jenis_izin
                         WHERE ic.pegawai_id = :pegawai_id
                         ORDER BY ic.tanggal_mulai DESC";
            $cuti_stmt = $pdo->prepare($cuti_sql);
            $cuti_stmt->bindValue(':pegawai_id', $pegawai['id']);
            $cuti_stmt->execute();
            $pegawai['cuti'] = $cuti_stmt->fetchAll();
            
            // Get saldo cuti data
            $saldo_cuti_sql = "SELECT * FROM saldo_cuti WHERE pegawai_id = :pegawai_id ORDER BY tahun DESC";
            $saldo_cuti_stmt = $pdo->prepare($saldo_cuti_sql);
            $saldo_cuti_stmt->bindValue(':pegawai_id', $pegawai['id']);
            $saldo_cuti_stmt->execute();
            $pegawai['saldo_cuti'] = $saldo_cuti_stmt->fetchAll();
        }
        break;
}
?>

<?php if ($action === 'view'): ?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Detail Pegawai</h1>
    <p class="text-gray-500 mt-1">Informasi lengkap tentang pegawai</p>
</div>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden">
    <!-- Pegawai Header -->
    <div class="relative">
        <img src="<?php echo getImageUrl($pegawai['foto'], 'pegawai'); ?>"
             class="w-full h-64 object-cover"
             alt="<?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>">
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6 pt-12">
            <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($pegawai['nama_lengkap']); ?></h2>
            <p class="text-gray-200"><?php echo htmlspecialchars($pegawai['email']); ?></p>
            <?php if (hasRole(['dosen']) && $pegawai['dosen_status'] === 'missing'): ?>
            <p class="text-yellow-300 text-sm mt-1"><i>Data dosen belum ditambahkan oleh admin</i></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pegawai Details -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dasar</h3>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Nama Lengkap</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['nama_lengkap']); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['email']); ?></p>
                    </div>

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
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['tempat_lahir'] ?? ''); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Tanggal Lahir</p>
                        <p class="font-medium"><?php echo htmlspecialchars(date('d M Y', strtotime($pegawai['tanggal_lahir'] ?? ''))); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Status Aktif</p>
                        <p class="font-medium">
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
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Tipe Pegawai</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pegawai['tipe_pegawai']))); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Alamat</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['alamat'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dosen</h3>

                <div class="space-y-4">
                    <?php if (isset($pegawai['dosen_info']) && $pegawai['dosen_info']): ?>
                    <div>
                        <p class="text-sm text-gray-500">NIDN</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['dosen_info']['nidn'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">NIDK</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['dosen_info']['nidk'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status Dosen</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pegawai['dosen_info']['status_dosen'] ?? ''))); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ikatan Kerja</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pegawai['dosen_info']['status_ikatan'] ?? ''))); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Jenjang Pendidikan</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['dosen_info']['jenjang_pendidikan'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Jabatan Fungsional</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pegawai['dosen_info']['jabatan_fungsional'] ?? ''))); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Sertifikat Pendidik</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['dosen_info']['sertifikat_pendidik'] ? 'Ya' : 'Tidak'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">No. Sertifikat Pendidik</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['dosen_info']['no_sertifikat_pendidik'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Mulai Mengajar</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['dosen_info']['tanggal_mulai_mengajar'] ? date('d M Y', strtotime($pegawai['dosen_info']['tanggal_mulai_mengajar'])) : '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Selesai</p>
                        <p class="font-medium"><?php echo htmlspecialchars($pegawai['dosen_info']['tanggal_selesai'] ? date('d M Y', strtotime($pegawai['dosen_info']['tanggal_selesai'])) : '-'); ?></p>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-gray-500">Data dosen belum tersedia. Silakan hubungi administrator untuk informasi lebih lanjut.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Family Information Section -->
        <?php if (isset($pegawai['keluarga']) && !empty($pegawai['keluarga'])): ?>
        <div class="p-6 border-t border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Data Keluarga</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hubungan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tempat/Tanggal Lahir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Tanggungan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pegawai['keluarga'] as $keluarga): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($keluarga['nama']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($keluarga['hubungan']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($keluarga['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(($keluarga['tempat_lahir'] ?? '') . ', ' . date('d M Y', strtotime($keluarga['tanggal_lahir'] ?? ''))); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $keluarga['status_tanggungan'] ? 'Ya' : 'Tidak'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Education Information Section -->
        <?php if (isset($pegawai['pendidikan']) && !empty($pegawai['pendidikan'])): ?>
        <div class="p-6 border-t border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Pendidikan</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenjang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institusi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurusan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun Lulus</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Ijazah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pegawai['pendidikan'] as $pendidikan): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($pendidikan['jenjang'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($pendidikan['nama_institusi'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($pendidikan['jurusan'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($pendidikan['tahun_lulus'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($pendidikan['no_ijazah'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $pendidikan['status_terakhir'] ? 'Ya' : 'Tidak'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Employment History Section -->
        <?php if (isset($pegawai['riwayat_kepegawaian']) && !empty($pegawai['riwayat_kepegawaian'])): ?>
        <div class="p-6 border-t border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Kepegawaian</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Perubahan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Efektif</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pegawai['riwayat_kepegawaian'] as $riwayat): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $riwayat['jenis_perubahan'] ?? ''))); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($riwayat['keterangan'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('d M Y', strtotime($riwayat['tanggal_efektif'] ?? ''))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Document SK Section -->
        <?php if (isset($pegawai['dokumen_sk']) && !empty($pegawai['dokumen_sk'])): ?>
        <div class="p-6 border-t border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Dokumen SK</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor SK</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal SK</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pegawai['dokumen_sk'] as $dokumen): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($dokumen['nomor_sk'] ?? ''); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($dokumen['judul'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('d M Y', strtotime($dokumen['tanggal_sk'] ?? ''))); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $dokumen['jenis_perubahan'] ?? ''))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Dosen-Prodi Relationship Section -->
        <?php if (isset($pegawai['dosen_prodi']) && !empty($pegawai['dosen_prodi'])): ?>
        <div class="p-6 border-t border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Hubungan Dosen-Prodi</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program Studi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Hubungan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kaprodi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pegawai['dosen_prodi'] as $relasi): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($relasi['nama_prodi'] ?? ''); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $relasi['status_hubungan'] ?? ''))); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $relasi['is_kaprodi'] ? 'Ya' : 'Tidak'; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('d M Y', strtotime($relasi['tanggal_mulai'] ?? ''))); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $relasi['tanggal_selesai'] ? htmlspecialchars(date('d M Y', strtotime($relasi['tanggal_selesai'] ?? ''))) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Leave Information Section -->
        <?php if (isset($pegawai['cuti']) && !empty($pegawai['cuti'])): ?>
        <div class="p-6 border-t border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Cuti</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Cuti</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Hari</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pegawai['cuti'] as $cuti): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($cuti['nama_izin'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('d M Y', strtotime($cuti['tanggal_mulai'] ?? ''))); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('d M Y', strtotime($cuti['tanggal_selesai'] ?? ''))); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($cuti['jumlah_hari'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($cuti['status'] === 'Disetujui'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?php echo $cuti['status']; ?></span>
                                <?php elseif ($cuti['status'] === 'Ditolak'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"><?php echo $cuti['status']; ?></span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><?php echo $cuti['status']; ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Leave Balance Section -->
        <?php if (isset($pegawai['saldo_cuti']) && !empty($pegawai['saldo_cuti'])): ?>
        <div class="p-6 border-t border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Saldo Cuti</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cuti</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Cuti</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($pegawai['saldo_cuti'] as $saldo): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($saldo['tahun'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($saldo['total_cuti'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($saldo['sisa_cuti'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
            <a href="index.php?page=dosenRole_pegawai&action=edit&id=<?php echo $pegawai['id']; ?>" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                Edit
            </a>
        </div>
    </div>
</div>

<?php endif; ?>