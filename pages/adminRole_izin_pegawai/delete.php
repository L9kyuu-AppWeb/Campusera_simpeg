<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('index.php?page=adminRole_izin_pegawai');
}

// Get izin data
$stmt = $pdo->prepare("SELECT i.*, p.nama_lengkap as nama_pegawai, j.nama_izin as nama_jenis_izin
                      FROM izin_pegawai i
                      LEFT JOIN pegawai p ON i.pegawai_id = p.id
                      LEFT JOIN jenis_izin j ON i.jenis_izin_id = j.id_jenis_izin
                      WHERE i.id_izin = ?");
$stmt->execute([$id]);
$izin = $stmt->fetch();

if (!$izin) {
    redirect('index.php?page=adminRole_izin_pegawai');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this izin cuts leave and was approved
    $jenis_izin_stmt = $pdo->prepare("SELECT is_potong_cuti FROM jenis_izin WHERE id_jenis_izin = ?");
    $jenis_izin_stmt->execute([$izin['jenis_izin_id']]);
    $jenis_izin_info = $jenis_izin_stmt->fetch();
    $is_potong_cuti = $jenis_izin_info['is_potong_cuti'] ?? 0;

    if ($is_potong_cuti && $izin['status'] === 'Disetujui') {
        // Need to add back the leave days to the employee's balance
        $tahun = date('Y', strtotime($izin['tanggal_mulai']));

        $pdo->beginTransaction();

        try {
            // Add back the leave days
            $update_saldo = $pdo->prepare("UPDATE saldo_cuti SET sisa_cuti = sisa_cuti + ? WHERE pegawai_id = ? AND tahun = ?");
            $update_saldo->execute([$izin['jumlah_hari'], $izin['pegawai_id'], $tahun]);

            // Delete izin
            $stmt = $pdo->prepare("DELETE FROM izin_pegawai WHERE id_izin = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                // Delete associated file if exists
                if ($izin['file_bukti']) {
                    $filePath = UPLOAD_PATH . 'izin/' . $izin['file_bukti'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                $pdo->commit();
                $_SESSION['success_message'] = "Izin pegawai berhasil dihapus. {$izin['jumlah_hari']} hari cuti dikembalikan ke saldo.";
            } else {
                throw new Exception("Terjadi kesalahan saat menghapus data.");
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = $e->getMessage();
            redirect('index.php?page=adminRole_izin_pegawai');
        }
    } else {
        // Delete izin without affecting leave balance
        $stmt = $pdo->prepare("DELETE FROM izin_pegawai WHERE id_izin = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            // Delete associated file if exists
            if ($izin['file_bukti']) {
                $filePath = UPLOAD_PATH . 'izin/' . $izin['file_bukti'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $_SESSION['success_message'] = "Izin pegawai berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Terjadi kesalahan saat menghapus data.";
        }
    }

    redirect('index.php?page=adminRole_izin_pegawai');
}
?>

<div class="mb-6">
    <a href="index.php?page=adminRole_izin_pegawai" class="text-gray-600 hover:text-gray-800 flex items-center">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
        </svg>
        Kembali ke Daftar Izin Pegawai
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-2">Hapus Izin Pegawai</h1>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6 max-w-2xl">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    Anda yakin ingin menghapus izin pegawai <strong>"<?php echo htmlspecialchars($izin['nama_pegawai']); ?>"</strong> untuk izin <strong>"<?php echo htmlspecialchars($izin['nama_jenis_izin']); ?>"</strong>? 
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="bg-gray-50 rounded-xl p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Nama Pegawai</p>
                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($izin['nama_pegawai']); ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Jenis Izin</p>
                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($izin['nama_jenis_izin']); ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Tanggal</p>
                    <p class="mt-1 text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($izin['tanggal_mulai'])); ?> - <?php echo date('d/m/Y', strtotime($izin['tanggal_selesai'])); ?></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Jumlah Hari</p>
                    <p class="mt-1 text-sm text-gray-900"><?php echo $izin['jumlah_hari']; ?> hari</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <p class="mt-1 text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php 
                                if ($izin['status'] === 'Disetujui') {
                                    echo 'bg-green-100 text-green-800';
                                } elseif ($izin['status'] === 'Ditolak') {
                                    echo 'bg-red-100 text-red-800';
                                } else {
                                    echo 'bg-yellow-100 text-yellow-800';
                                }
                            ?>">
                            <?php echo htmlspecialchars($izin['status']); ?>
                        </span>
                    </p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm font-medium text-gray-500">Keterangan</p>
                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($izin['keterangan']); ?></p>
                </div>
                <?php if ($izin['file_bukti']): ?>
                <div class="md:col-span-2">
                    <p class="text-sm font-medium text-gray-500">File Bukti</p>
                    <p class="mt-1 text-sm text-gray-900">
                        <a href="<?php echo UPLOAD_URL . 'izin/' . htmlspecialchars($izin['file_bukti']); ?>" 
                           target="_blank" 
                           class="text-blue-600 hover:text-blue-800">
                            <?php echo htmlspecialchars($izin['file_bukti']); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end">
            <a href="index.php?page=adminRole_izin_pegawai" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors mr-3">
                Batal
            </a>
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Hapus Permanen
            </button>
        </div>
    </form>
</div>