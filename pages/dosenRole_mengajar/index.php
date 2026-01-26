<?php
// Check permission
if (!hasRole(['dosen'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';

switch ($action) {
    case 'detail':
        require_once 'detail.php';
        break;
    default:
        // For dosen role, show only their own data
        if (hasRole(['dosen'])) {
            // Get user info first
            $user_info_sql = "SELECT * FROM users WHERE id = :user_id";
            $user_stmt = $pdo->prepare($user_info_sql);
            $user_stmt->bindValue(':user_id', $current_user_id);
            $user_stmt->execute();
            $user_info = $user_stmt->fetch();

            // Try to find the dosen record associated with the current user
            // We'll use email as a common field to connect users and dosen
            $dosen_sql = "SELECT d.*, p.nama_lengkap, p.email, p.foto FROM dosen d
                          JOIN pegawai p ON d.pegawai_id = p.id
                          WHERE p.email = :email";

            $dosen_stmt = $pdo->prepare($dosen_sql);
            $dosen_stmt->bindValue(':email', $user_info['email']);
            $dosen_stmt->execute();
            $dosen_data = $dosen_stmt->fetch();

            if (!$dosen_data) {
                // If no dosen record found, show error
                require_once __DIR__ . '/../errors/404.php';
                exit;
            }

            // Get dosen_prodi information for this dosen
            $dosen_prodi_sql = "SELECT dp.*, pr.nama_prodi FROM dosen_prodi dp
                                JOIN prodi pr ON dp.prodi_id = pr.id
                                WHERE dp.dosen_id = :dosen_id";

            $dosen_prodi_stmt = $pdo->prepare($dosen_prodi_sql);
            $dosen_prodi_stmt->bindValue(':dosen_id', $dosen_data['id']);
            $dosen_prodi_stmt->execute();
            $dosen_prodi_list = $dosen_prodi_stmt->fetchAll();

            // Redirect to detail page for the current user's data
            header("Location: index.php?page=dosenRole_mengajar&action=detail&id=" . $dosen_data['id']);
            exit;
        }
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Tim Pengajar</h1>
    <p class="text-gray-500 mt-1">Informasi dosen sebagai tim pengajar</p>
</div>

<!-- Dosen Profile Card -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (isset($dosen_data) && $dosen_data): ?>
        <?php $d = $dosen_data; ?>
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
            <!-- Dosen Content -->
            <div class="p-5">
                <div class="mb-3">
                    <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($d['nama_lengkap']); ?></h3>
                    <p class="text-sm text-gray-500 mt-1">
                        <?php echo htmlspecialchars($d['email']); ?>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <p class="text-xs text-gray-500">NIDN</p>
                        <p class="font-medium"><?php echo htmlspecialchars($d['nidn'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">NIDK</p>
                        <p class="font-medium"><?php echo htmlspecialchars($d['nidk'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status Dosen</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $d['status_dosen']))); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Ikatan Kerja</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $d['status_ikatan']))); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Jenjang Pendidikan</p>
                        <p class="font-medium"><?php echo htmlspecialchars($d['jenjang_pendidikan']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Jabatan Fungsional</p>
                        <p class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $d['jabatan_fungsional']))); ?></p>
                    </div>
                </div>

                <!-- Dosen Prodi Information -->
                <div class="mb-4">
                    <p class="text-xs text-gray-500">Prodi</p>
                    <div class="mt-1">
                        <?php if (count($dosen_prodi_list) > 0): ?>
                            <?php foreach ($dosen_prodi_list as $dp): ?>
                                <div class="bg-gray-50 p-2 rounded mb-1">
                                    <div class="text-sm font-medium"><?php echo htmlspecialchars($dp['nama_prodi']); ?></div>
                                    <div class="text-xs text-gray-500">
                                        <?php
                                        $status_hubungan = ucfirst(str_replace('_', ' ', $dp['status_hubungan']));
                                        echo $status_hubungan;
                                        if ($dp['is_kaprodi']) echo ' (Kaprodi)';
                                        ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php
                                        if ($dp['tanggal_mulai']) {
                                            echo 'Mulai: ' . date('d M Y', strtotime($dp['tanggal_mulai']));
                                        }
                                        if ($dp['tanggal_selesai']) {
                                            echo ' | Selesai: ' . date('d M Y', strtotime($dp['tanggal_selesai']));
                                        } else {
                                            echo ' | Status: Aktif';
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-xs text-gray-500">Belum terdaftar di prodi manapun</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <?php echo htmlspecialchars('Sertifikat: ' . ($d['sertifikat_pendidik'] ? 'Ya' : 'Tidak')); ?>
                    </div>

                    <div class="flex space-x-2">
                        <a href="index.php?page=dosenRole_mengajar&action=detail&id=<?php echo $d['id']; ?>"
                           class="text-green-600 hover:text-green-800 transition-colors" title="Detail">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-span-full text-center py-12">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data dosen</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>