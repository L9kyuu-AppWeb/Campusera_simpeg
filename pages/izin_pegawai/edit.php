<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('index.php?page=izin_pegawai');
}

// Get izin data
$stmt = $pdo->prepare("SELECT * FROM izin_pegawai WHERE id_izin = ?");
$stmt->execute([$id]);
$izin = $stmt->fetch();

if (!$izin) {
    redirect('index.php?page=izin_pegawai');
}

// Get pegawai and jenis izin data for dropdowns
$pegawai_list = $pdo->query("SELECT id, nama_lengkap FROM pegawai ORDER BY nama_lengkap")->fetchAll();
$jenis_izin_list = $pdo->query("SELECT id_jenis_izin, nama_izin FROM jenis_izin ORDER BY nama_izin")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pegawai_id = (int)$_POST['pegawai_id'];
    $jenis_izin_id = (int)$_POST['jenis_izin_id'];
    $tanggal_mulai = cleanInput($_POST['tanggal_mulai']);
    $tanggal_selesai = cleanInput($_POST['tanggal_selesai']);
    $keterangan = cleanInput($_POST['keterangan']);
    $status = cleanInput($_POST['status']);

    // Calculate jumlah_hari
    $start = new DateTime($tanggal_mulai);
    $end = new DateTime($tanggal_selesai);
    $jumlah_hari = $start->diff($end)->days + 1; // +1 to include both start and end dates

    // Get jenis izin info to check if it cuts leave
    $jenis_izin_stmt = $pdo->prepare("SELECT is_potong_cuti FROM jenis_izin WHERE id_jenis_izin = ?");
    $jenis_izin_stmt->execute([$jenis_izin_id]);
    $jenis_izin_info = $jenis_izin_stmt->fetch();
    $is_potong_cuti = $jenis_izin_info['is_potong_cuti'] ?? 0;

    // Handle file upload if exists
    $file_bukti = $izin['file_bukti']; // Keep existing file by default
    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] == 0) {
        $uploadDir = UPLOAD_PATH . 'izin/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Delete old file if exists
        if ($izin['file_bukti'] && file_exists($uploadDir . $izin['file_bukti'])) {
            unlink($uploadDir . $izin['file_bukti']);
        }

        $fileName = time() . '_' . basename($_FILES['file_bukti']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['file_bukti']['tmp_name'], $targetPath)) {
            $file_bukti = $fileName;
        } else {
            $error = "Gagal mengunggah file bukti.";
        }
    }

    // Validate input
    if (empty($pegawai_id) || empty($jenis_izin_id) || empty($tanggal_mulai) || empty($tanggal_selesai)) {
        $error = "Semua field wajib diisi.";
    } elseif ($tanggal_mulai > $tanggal_selesai) {
        $error = "Tanggal mulai tidak boleh lebih besar dari tanggal selesai.";
    } else {
        // Check if this izin previously cut leave and status changed
        $tahun = date('Y', strtotime($tanggal_mulai));

        if ($is_potong_cuti) {
            // Update saldo cuti based on new status
            $pdo->beginTransaction();

            try {
                // Adjust saldo cuti based on previous status
                if ($izin['status'] === 'Disetujui' && $status !== 'Disetujui') {
                    // Previously approved but now changed to not approved - add back the days
                    $update_saldo = $pdo->prepare("UPDATE saldo_cuti SET sisa_cuti = sisa_cuti + ? WHERE pegawai_id = ? AND tahun = ?");
                    $update_saldo->execute([$izin['jumlah_hari'], $pegawai_id, $tahun]);
                } elseif ($izin['status'] !== 'Disetujui' && $status === 'Disetujui') {
                    // Previously not approved but now approved - subtract the days
                    // Check if sufficient leave balance
                    $saldo_stmt = $pdo->prepare("SELECT sisa_cuti FROM saldo_cuti WHERE pegawai_id = ? AND tahun = ? LIMIT 1");
                    $saldo_stmt->execute([$pegawai_id, $tahun]);
                    $saldo = $saldo_stmt->fetch();

                    if (!$saldo || $saldo['sisa_cuti'] < $jumlah_hari) {
                        throw new Exception("Sisa cuti tidak mencukupi. Hanya tersisa {$saldo['sisa_cuti']} hari.");
                    }

                    $update_saldo = $pdo->prepare("UPDATE saldo_cuti SET sisa_cuti = sisa_cuti - ? WHERE pegawai_id = ? AND tahun = ?");
                    $update_saldo->execute([$jumlah_hari, $pegawai_id, $tahun]);
                } elseif ($izin['status'] === 'Disetujui' && $status === 'Disetujui' && $izin['jumlah_hari'] != $jumlah_hari) {
                    // Status remains approved but jumlah_hari changed - adjust difference
                    $difference = $jumlah_hari - $izin['jumlah_hari'];
                    if ($difference > 0) {
                        // More days requested - check if sufficient balance
                        $saldo_stmt = $pdo->prepare("SELECT sisa_cuti FROM saldo_cuti WHERE pegawai_id = ? AND tahun = ? LIMIT 1");
                        $saldo_stmt->execute([$pegawai_id, $tahun]);
                        $saldo = $saldo_stmt->fetch();

                        if (!$saldo || $saldo['sisa_cuti'] < $difference) {
                            throw new Exception("Sisa cuti tidak mencukupi. Hanya tersisa {$saldo['sisa_cuti']} hari.");
                        }
                    }

                    $update_saldo = $pdo->prepare("UPDATE saldo_cuti SET sisa_cuti = sisa_cuti - ? WHERE pegawai_id = ? AND tahun = ?");
                    $update_saldo->execute([$difference, $pegawai_id, $tahun]);
                }

                // Update izin
                $stmt = $pdo->prepare("UPDATE izin_pegawai SET pegawai_id = ?, jenis_izin_id = ?, tanggal_mulai = ?, tanggal_selesai = ?, jumlah_hari = ?, keterangan = ?, file_bukti = ?, status = ? WHERE id_izin = ?");
                $result = $stmt->execute([$pegawai_id, $jenis_izin_id, $tanggal_mulai, $tanggal_selesai, $jumlah_hari, $keterangan, $file_bukti, $status, $id]);

                if ($result) {
                    $pdo->commit();
                    $_SESSION['success_message'] = "Izin pegawai berhasil diperbarui.";
                    redirect('index.php?page=izin_pegawai');
                } else {
                    throw new Exception("Terjadi kesalahan saat menyimpan data.");
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
            }
        } else {
            // Update izin without affecting leave balance
            $stmt = $pdo->prepare("UPDATE izin_pegawai SET pegawai_id = ?, jenis_izin_id = ?, tanggal_mulai = ?, tanggal_selesai = ?, jumlah_hari = ?, keterangan = ?, file_bukti = ?, status = ? WHERE id_izin = ?");
            $result = $stmt->execute([$pegawai_id, $jenis_izin_id, $tanggal_mulai, $tanggal_selesai, $jumlah_hari, $keterangan, $file_bukti, $status, $id]);

            if ($result) {
                $_SESSION['success_message'] = "Izin pegawai berhasil diperbarui.";
                redirect('index.php?page=izin_pegawai');
            } else {
                $error = "Terjadi kesalahan saat menyimpan data.";
            }
        }
    }
} else {
    // Pre-populate form with existing data
    $pegawai_id = $izin['pegawai_id'];
    $jenis_izin_id = $izin['jenis_izin_id'];
    $tanggal_mulai = $izin['tanggal_mulai'];
    $tanggal_selesai = $izin['tanggal_selesai'];
    $keterangan = $izin['keterangan'];
    $status = $izin['status'];
}
?>

<div class="mb-6">
    <a href="index.php?page=izin_pegawai" class="text-gray-600 hover:text-gray-800 flex items-center">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
        </svg>
        Kembali ke Daftar Izin Pegawai
    </a>
    <h1 class="text-3xl font-bold text-gray-800 mt-2">Edit Izin Pegawai</h1>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6 max-w-2xl">
    <?php if (isset($error)): ?>
        <div class="mb-6 bg-red-50 text-red-700 px-4 py-3 rounded-xl">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="space-y-6">
            <div>
                <label for="pegawai_id" class="block text-sm font-medium text-gray-700 mb-1">Nama Pegawai *</label>
                <select name="pegawai_id" id="pegawai_id" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        required>
                    <option value="">Pilih Pegawai</option>
                    <?php foreach ($pegawai_list as $pegawai): ?>
                        <option value="<?php echo $pegawai['id']; ?>"
                            <?php echo (isset($pegawai_id) && $pegawai_id == $pegawai['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="jenis_izin_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Izin *</label>
                <select name="jenis_izin_id" id="jenis_izin_id" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        required>
                    <option value="">Pilih Jenis Izin</option>
                    <?php foreach ($jenis_izin_list as $jenis_izin): ?>
                        <option value="<?php echo $jenis_izin['id_jenis_izin']; ?>" 
                            <?php echo (isset($jenis_izin_id) && $jenis_izin_id == $jenis_izin['id_jenis_izin']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($jenis_izin['nama_izin']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" 
                           value="<?php echo isset($tanggal_mulai) ? htmlspecialchars($tanggal_mulai) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                           required>
                </div>
                
                <div>
                    <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai *</label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" 
                           value="<?php echo isset($tanggal_selesai) ? htmlspecialchars($tanggal_selesai) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                           required>
                </div>
            </div>

            <div>
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="4"
                          class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"><?php echo isset($keterangan) ? htmlspecialchars($keterangan) : ''; ?></textarea>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="Menunggu" <?php echo (isset($status) && $status === 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Disetujui" <?php echo (isset($status) && $status === 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="Ditolak" <?php echo (isset($status) && $status === 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                </select>
            </div>

            <div>
                <label for="file_bukti" class="block text-sm font-medium text-gray-700 mb-1">File Bukti</label>
                <input type="file" name="file_bukti" id="file_bukti" 
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       accept=".pdf,.jpg,.jpeg,.png">
                <p class="mt-1 text-sm text-gray-500">Format: PDF, JPG, PNG. Maksimal ukuran file: 5MB.</p>
                
                <?php if ($izin['file_bukti']): ?>
                <div class="mt-2">
                    <p class="text-sm text-gray-600">File saat ini: 
                        <a href="<?php echo UPLOAD_URL . 'izin/' . htmlspecialchars($izin['file_bukti']); ?>" 
                           target="_blank" 
                           class="text-blue-600 hover:text-blue-800">
                            <?php echo htmlspecialchars($izin['file_bukti']); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex justify-end pt-4">
                <a href="index.php?page=izin_pegawai" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors mr-3">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>