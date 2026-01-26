<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';
$success = false;

// Get only dosen-type pegawai for dropdown
$pegawaiList = $pdo->query("SELECT id, nama_lengkap FROM pegawai WHERE tipe_pegawai = 'dosen_tetap' OR tipe_pegawai = 'dosen_luar' ORDER BY nama_lengkap ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pegawai_id = !empty($_POST['pegawai_id']) ? (int)$_POST['pegawai_id'] : null;
    $nidn = !empty($_POST['nidn']) ? cleanInput($_POST['nidn']) : null;
    $nidk = !empty($_POST['nidk']) ? cleanInput($_POST['nidk']) : null;
    $prodi_homebase_id = !empty($_POST['prodi_homebase_id']) ? (int)$_POST['prodi_homebase_id'] : null;
    $status_dosen = cleanInput($_POST['status_dosen']);
    $status_ikatan = cleanInput($_POST['status_ikatan']);
    $jenjang_pendidikan = cleanInput($_POST['jenjang_pendidikan']);
    $bidang_keahlian = cleanInput($_POST['bidang_keahlian']);
    $jabatan_fungsional = cleanInput($_POST['jabatan_fungsional']);
    $tanggal_mulai_mengajar = !empty($_POST['tanggal_mulai_mengajar']) ? cleanInput($_POST['tanggal_mulai_mengajar']) : null;
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? cleanInput($_POST['tanggal_selesai']) : null;
    $sertifikat_pendidik = isset($_POST['sertifikat_pendidik']) ? 1 : 0;
    $no_sertifikat_pendidik = !empty($_POST['no_sertifikat_pendidik']) ? cleanInput($_POST['no_sertifikat_pendidik']) : null;

    // Validation
    if (empty($pegawai_id) || empty($status_dosen) || empty($status_ikatan) || empty($jenjang_pendidikan)) {
        $error = 'Semua field yang bertanda * harus diisi!';
    } else {
        // Check if pegawai_id already exists in dosen table
        $stmt = $pdo->prepare("SELECT id FROM dosen WHERE pegawai_id = ?");
        $stmt->execute([$pegawai_id]);

        if ($stmt->fetch()) {
            $error = 'Pegawai ini sudah terdaftar sebagai dosen!';
        } else {
            // Insert dosen into database
            $stmt = $pdo->prepare("
                INSERT INTO dosen (
                    pegawai_id, nidn, nidk, prodi_homebase_id, status_dosen, status_ikatan,
                    jenjang_pendidikan, bidang_keahlian, jabatan_fungsional,
                    tanggal_mulai_mengajar, tanggal_selesai, sertifikat_pendidik, no_sertifikat_pendidik
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt->execute([
                $pegawai_id, $nidn, $nidk, $prodi_homebase_id, $status_dosen, $status_ikatan,
                $jenjang_pendidikan, $bidang_keahlian, $jabatan_fungsional,
                $tanggal_mulai_mengajar, $tanggal_selesai, $sertifikat_pendidik, $no_sertifikat_pendidik
            ])) {
                // Get the ID of the newly inserted dosen
                $newDosenId = $pdo->lastInsertId();

                // If prodi_homebase_id is specified, create a dosen_prodi record with homebase status
                if (!empty($prodi_homebase_id)) {
                    $stmtDosenProdi = $pdo->prepare("
                        INSERT INTO dosen_prodi (dosen_id, prodi_id, status_hubungan, is_kaprodi, tanggal_mulai)
                        VALUES (?, ?, 'homebase', 0, ?)
                    ");

                    $stmtDosenProdi->execute([$newDosenId, $prodi_homebase_id, $tanggal_mulai_mengajar]);
                }

                logActivity($_SESSION['user_id'], 'create_dosen', "Created new dosen for pegawai ID: $pegawai_id");
                setAlert('success', 'Dosen berhasil ditambahkan!');
                redirect('index.php?page=adminRole_dosen');
            } else {
                $error = 'Gagal menambahkan dosen!';
            }
        }
    }
}

// Get all status and ikatan for dropdowns
$status = $pdo->query("SELECT DISTINCT status_dosen FROM dosen WHERE status_dosen IS NOT NULL ORDER BY status_dosen")->fetchAll();
$ikatan = $pdo->query("SELECT DISTINCT status_ikatan FROM dosen WHERE status_ikatan IS NOT NULL ORDER BY status_ikatan")->fetchAll();
$jenjang = $pdo->query("SELECT DISTINCT jenjang_pendidikan FROM dosen WHERE jenjang_pendidikan IS NOT NULL ORDER BY jenjang_pendidikan")->fetchAll();
$jabatan = $pdo->query("SELECT DISTINCT jabatan_fungsional FROM dosen WHERE jabatan_fungsional IS NOT NULL ORDER BY jabatan_fungsional")->fetchAll();
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_dosen" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Dosen Baru</h1>
            <p class="text-gray-500 mt-1">Tambahkan data dosen baru</p>
        </div>
    </div>
</div>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
    <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <form method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai *</label>
                <select name="pegawai_id" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none search-select">
                    <option value="">Pilih Pegawai</option>
                    <?php foreach ($pegawaiList as $pegawai): ?>
                        <option value="<?php echo $pegawai['id']; ?>"
                                <?php echo (isset($_POST['pegawai_id']) && $_POST['pegawai_id'] == $pegawai['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">NIDN</label>
                <input type="text" name="nidn"
                       value="<?php echo isset($_POST['nidn']) ? htmlspecialchars($_POST['nidn']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk dosen luar</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">NIDK</label>
                <input type="text" name="nidk"
                       value="<?php echo isset($_POST['nidk']) ? htmlspecialchars($_POST['nidk']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prodi Homebase</label>
                <select name="prodi_homebase_id"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Prodi Homebase</option>
                    <?php
                    $prodiList = $pdo->query("SELECT id, nama_prodi FROM prodi ORDER BY nama_prodi ASC")->fetchAll();
                    foreach ($prodiList as $prodi): ?>
                        <option value="<?php echo $prodi['id']; ?>"
                                <?php echo (isset($_POST['prodi_homebase_id']) && $_POST['prodi_homebase_id'] == $prodi['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prodi['nama_prodi']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Dosen *</label>
                <select name="status_dosen" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Status</option>
                    <?php foreach ($status as $s): ?>
                        <option value="<?php echo $s['status_dosen']; ?>"
                                <?php echo (isset($_POST['status_dosen']) && $_POST['status_dosen'] === $s['status_dosen']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $s['status_dosen'])); ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options if no records exist yet -->
                    <option value="tetap_yayasan" <?php echo (isset($_POST['status_dosen']) && $_POST['status_dosen'] === 'tetap_yayasan') ? 'selected' : ''; ?>>Tetap Yayasan</option>
                    <option value="tetap_dikti" <?php echo (isset($_POST['status_dosen']) && $_POST['status_dosen'] === 'tetap_dikti') ? 'selected' : ''; ?>>Tetap Dikti</option>
                    <option value="luar_biasa" <?php echo (isset($_POST['status_dosen']) && $_POST['status_dosen'] === 'luar_biasa') ? 'selected' : ''; ?>>Luar Biasa</option>
                    <option value="honorer" <?php echo (isset($_POST['status_dosen']) && $_POST['status_dosen'] === 'honorer') ? 'selected' : ''; ?>>Honorer</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Ikatan *</label>
                <select name="status_ikatan" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Ikatan</option>
                    <?php foreach ($ikatan as $i): ?>
                        <option value="<?php echo $i['status_ikatan']; ?>"
                                <?php echo (isset($_POST['status_ikatan']) && $_POST['status_ikatan'] === $i['status_ikatan']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $i['status_ikatan'])); ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options if no records exist yet -->
                    <option value="tetap" <?php echo (isset($_POST['status_ikatan']) && $_POST['status_ikatan'] === 'tetap') ? 'selected' : ''; ?>>Tetap</option>
                    <option value="kontrak" <?php echo (isset($_POST['status_ikatan']) && $_POST['status_ikatan'] === 'kontrak') ? 'selected' : ''; ?>>Kontrak</option>
                    <option value="part_time" <?php echo (isset($_POST['status_ikatan']) && $_POST['status_ikatan'] === 'part_time') ? 'selected' : ''; ?>>Part Time</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenjang Pendidikan *</label>
                <select name="jenjang_pendidikan" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Jenjang</option>
                    <?php foreach ($jenjang as $j): ?>
                        <option value="<?php echo $j['jenjang_pendidikan']; ?>"
                                <?php echo (isset($_POST['jenjang_pendidikan']) && $_POST['jenjang_pendidikan'] === $j['jenjang_pendidikan']) ? 'selected' : ''; ?>>
                            <?php echo $j['jenjang_pendidikan']; ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options if no records exist yet -->
                    <option value="S1" <?php echo (isset($_POST['jenjang_pendidikan']) && $_POST['jenjang_pendidikan'] === 'S1') ? 'selected' : ''; ?>>S1</option>
                    <option value="S2" <?php echo (isset($_POST['jenjang_pendidikan']) && $_POST['jenjang_pendidikan'] === 'S2') ? 'selected' : ''; ?>>S2</option>
                    <option value="S3" <?php echo (isset($_POST['jenjang_pendidikan']) && $_POST['jenjang_pendidikan'] === 'S3') ? 'selected' : ''; ?>>S3</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan Fungsional</label>
                <select name="jabatan_fungsional"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Jabatan</option>
                    <?php foreach ($jabatan as $j): ?>
                        <option value="<?php echo $j['jabatan_fungsional']; ?>"
                                <?php echo (isset($_POST['jabatan_fungsional']) && $_POST['jabatan_fungsional'] === $j['jabatan_fungsional']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $j['jabatan_fungsional'])); ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options if no records exist yet -->
                    <option value="asisten_ahli" <?php echo (isset($_POST['jabatan_fungsional']) && $_POST['jabatan_fungsional'] === 'asisten_ahli') ? 'selected' : ''; ?>>Asisten Ahli</option>
                    <option value="lektor" <?php echo (isset($_POST['jabatan_fungsional']) && $_POST['jabatan_fungsional'] === 'lektor') ? 'selected' : ''; ?>>Lektor</option>
                    <option value="lektor_kepala" <?php echo (isset($_POST['jabatan_fungsional']) && $_POST['jabatan_fungsional'] === 'lektor_kepala') ? 'selected' : ''; ?>>Lektor Kepala</option>
                    <option value="guru_besar" <?php echo (isset($_POST['jabatan_fungsional']) && $_POST['jabatan_fungsional'] === 'guru_besar') ? 'selected' : ''; ?>>Guru Besar</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai Mengajar</label>
                <input type="date" name="tanggal_mulai_mengajar"
                       value="<?php echo isset($_POST['tanggal_mulai_mengajar']) ? htmlspecialchars($_POST['tanggal_mulai_mengajar']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai"
                       value="<?php echo isset($_POST['tanggal_selesai']) ? htmlspecialchars($_POST['tanggal_selesai']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika masih aktif</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No Sertifikat Pendidik</label>
                <input type="text" name="no_sertifikat_pendidik"
                       value="<?php echo isset($_POST['no_sertifikat_pendidik']) ? htmlspecialchars($_POST['no_sertifikat_pendidik']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bidang Keahlian</label>
                <input type="text" name="bidang_keahlian"
                       value="<?php echo isset($_POST['bidang_keahlian']) ? htmlspecialchars($_POST['bidang_keahlian']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sertifikat Pendidik</label>
            <div class="flex items-center space-x-3 mt-3">
                <input type="checkbox" name="sertifikat_pendidik" id="sertifikat_pendidik" value="1"
                       <?php echo (isset($_POST['sertifikat_pendidik']) && $_POST['sertifikat_pendidik']) ? 'checked' : (isset($_POST['sertifikat_pendidik']) ? '' : ''); ?>
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="sertifikat_pendidik" class="text-sm text-gray-700">Sudah Bersertifikat</label>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Simpan Dosen
            </button>
            <a href="index.php?page=adminRole_dosen" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
// Enable search functionality for select elements with class 'search-select'
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('.search-select');

    selects.forEach(select => {
        // Create wrapper div
        const wrapper = document.createElement('div');
        wrapper.className = 'relative';

        // Wrap the select element
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        // Add custom search input
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Cari pegawai...';
        searchInput.className = 'w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none';

        // Insert search input before select
        wrapper.insertBefore(searchInput, select);
        select.style.display = 'none'; // Hide original select

        // Create dropdown container
        const dropdown = document.createElement('div');
        dropdown.className = 'absolute z-10 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto mt-1 hidden';
        wrapper.appendChild(dropdown);

        // Populate dropdown with options
        const options = Array.from(select.options);
        options.shift(); // Remove the first option (placeholder)

        options.forEach(option => {
            const div = document.createElement('div');
            div.className = 'px-4 py-2 hover:bg-blue-50 cursor-pointer';
            div.textContent = option.text;
            div.dataset.value = option.value;

            div.addEventListener('click', () => {
                select.value = option.value;
                searchInput.value = option.text;
                dropdown.classList.add('hidden');
            });

            dropdown.appendChild(div);
        });

        // Show/hide dropdown
        searchInput.addEventListener('focus', () => {
            dropdown.classList.remove('hidden');
            searchInput.select();
        });

        // Handle search input
        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();
            const items = dropdown.querySelectorAll('div');

            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Set initial value if select already has one
        if (select.value) {
            const selectedOption = Array.from(select.options).find(opt => opt.value === select.value);
            if (selectedOption) {
                searchInput.value = selectedOption.text;
            }
        }
    });
});
</script>
</div>
</div>