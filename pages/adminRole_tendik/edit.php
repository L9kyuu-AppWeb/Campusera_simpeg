<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$tendikId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$tendikId) {
    redirect('index.php?page=adminRole_tendik');
}

// Get tendik data
$stmt = $pdo->prepare("SELECT * FROM tendik WHERE id = ?");
$stmt->execute([$tendikId]);
$tendik = $stmt->fetch();

if (!$tendik) {
    setAlert('error', 'Tenaga Kependidikan tidak ditemukan!');
    redirect('index.php?page=adminRole_tendik');
}

$error = '';

// Get only tendik-type pegawai for dropdown
$pegawaiList = $pdo->query("SELECT id, nama_lengkap FROM pegawai WHERE tipe_pegawai = 'tendik_tetap' OR tipe_pegawai = 'tendik_kontrak' ORDER BY nama_lengkap ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pegawai_id = (int)$_POST['pegawai_id'];
    $nip = !empty($_POST['nip']) ? cleanInput($_POST['nip']) : null;
    $unit_kerja_id = (int)$_POST['unit_kerja_id'];
    $jabatan = cleanInput($_POST['jabatan']);
    $status_kepegawaian = cleanInput($_POST['status_kepegawaian']);
    $golongan = !empty($_POST['golongan']) ? cleanInput($_POST['golongan']) : null;
    $tanggal_mulai_kerja = cleanInput($_POST['tanggal_mulai_kerja']);
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? cleanInput($_POST['tanggal_selesai']) : null;

    // Validation
    if (empty($pegawai_id) || empty($unit_kerja_id) || empty($jabatan) || empty($status_kepegawaian) || empty($tanggal_mulai_kerja)) {
        $error = 'Semua field yang bertanda * harus diisi!';
    } elseif ($status_kepegawaian === 'tetap' && empty($golongan)) {
        $error = 'Golongan harus diisi untuk status kepegawaian tetap!';
    } else {
        // Check if pegawai_id already exists in tendik table for other records
        $stmt = $pdo->prepare("SELECT id FROM tendik WHERE pegawai_id = ? AND id != ?");
        $stmt->execute([$pegawai_id, $tendikId]);

        if ($stmt->fetch()) {
            $error = 'Pegawai ini sudah terdaftar sebagai tenaga kependidikan!';
        } else {
            // Update tendik in database
            $stmt = $pdo->prepare("
                UPDATE tendik
                SET pegawai_id = ?, nip = ?, unit_kerja_id = ?, jabatan = ?, status_kepegawaian = ?, 
                    golongan = ?, tanggal_mulai_kerja = ?, tanggal_selesai = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$pegawai_id, $nip, $unit_kerja_id, $jabatan, $status_kepegawaian, $golongan, $tanggal_mulai_kerja, $tanggal_selesai, $tendikId])) {
                logActivity($_SESSION['user_id'], 'update_tendik', "Updated tendik ID: $tendikId");
                setAlert('success', 'Tenaga kependidikan berhasil diperbarui!');
                redirect('index.php?page=adminRole_tendik');
            } else {
                $error = 'Gagal memperbarui tenaga kependidikan!';
            }
        }
    }
}

// Get all status for dropdown
$status = $pdo->query("SELECT DISTINCT status_kepegawaian FROM tendik WHERE status_kepegawaian IS NOT NULL ORDER BY status_kepegawaian")->fetchAll();
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_tendik" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Tendik</h1>
            <p class="text-gray-500 mt-1">Perbarui informasi tenaga kependidikan</p>
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
                                <?php echo (isset($_POST['pegawai_id']) ? $_POST['pegawai_id'] : $tendik['pegawai_id']) == $pegawai['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pegawai['nama_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
                <input type="text" name="nip"
                       value="<?php echo isset($_POST['nip']) ? htmlspecialchars($_POST['nip']) : htmlspecialchars($tendik['nip']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk kontrak</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Unit Kerja *</label>
                <select name="unit_kerja_id" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Unit Kerja</option>
                    <?php
                    $unitKerjaList = $pdo->query("SELECT id, nama_unit, tipe_unit FROM unit_kerja ORDER BY nama_unit ASC")->fetchAll();
                    foreach ($unitKerjaList as $unit): ?>
                        <option value="<?php echo $unit['id']; ?>"
                                <?php echo (isset($_POST['unit_kerja_id']) ? $_POST['unit_kerja_id'] : $tendik['unit_kerja_id']) == $unit['id'] ? 'selected' : ''; ?>>
                            [<?php echo ucfirst($unit['tipe_unit']); ?>] <?php echo htmlspecialchars($unit['nama_unit']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan *</label>
                <input type="text" name="jabatan" required
                       value="<?php echo isset($_POST['jabatan']) ? htmlspecialchars($_POST['jabatan']) : htmlspecialchars($tendik['jabatan']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Kepegawaian *</label>
                <select name="status_kepegawaian" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Status</option>
                    <?php foreach ($status as $s): ?>
                        <option value="<?php echo $s['status_kepegawaian']; ?>"
                                <?php echo (isset($_POST['status_kepegawaian']) ? $_POST['status_kepegawaian'] : $tendik['status_kepegawaian']) === $s['status_kepegawaian'] ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $s['status_kepegawaian'])); ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options -->
                    <option value="tetap" <?php echo (isset($_POST['status_kepegawaian']) ? $_POST['status_kepegawaian'] : $tendik['status_kepegawaian']) === 'tetap' ? 'selected' : ''; ?>>Tetap</option>
                    <option value="kontrak" <?php echo (isset($_POST['status_kepegawaian']) ? $_POST['status_kepegawaian'] : $tendik['status_kepegawaian']) === 'kontrak' ? 'selected' : ''; ?>>Kontrak</option>
                    <option value="honorer" <?php echo (isset($_POST['status_kepegawaian']) ? $_POST['status_kepegawaian'] : $tendik['status_kepegawaian']) === 'honorer' ? 'selected' : ''; ?>>Honorer</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Golongan</label>
                <input type="text" name="golongan"
                       value="<?php echo isset($_POST['golongan']) ? htmlspecialchars($_POST['golongan']) : htmlspecialchars($tendik['golongan']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <p class="text-xs text-gray-500 mt-1">Untuk PNS</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai Kerja *</label>
                <input type="date" name="tanggal_mulai_kerja" required
                       value="<?php echo isset($_POST['tanggal_mulai_kerja']) ? htmlspecialchars($_POST['tanggal_mulai_kerja']) : htmlspecialchars($tendik['tanggal_mulai_kerja']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai"
                       value="<?php echo isset($_POST['tanggal_selesai']) ? htmlspecialchars($_POST['tanggal_selesai']) : htmlspecialchars($tendik['tanggal_selesai']); ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika masih aktif</p>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Update Tendik
            </button>
            <a href="index.php?page=adminRole_tendik" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
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