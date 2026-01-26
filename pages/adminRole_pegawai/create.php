<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_induk = !empty($_POST['nomor_induk']) ? cleanInput($_POST['nomor_induk']) : null;
    $nik = !empty($_POST['nik']) ? cleanInput($_POST['nik']) : null;
    $nama_lengkap = cleanInput($_POST['nama_lengkap']);
    $email = cleanInput($_POST['email']);
    $no_hp = cleanInput($_POST['no_hp']);
    $tempat_lahir = cleanInput($_POST['tempat_lahir']);
    $tanggal_lahir = cleanInput($_POST['tanggal_lahir']);
    $jenis_kelamin = cleanInput($_POST['jenis_kelamin']);
    $alamat = cleanInput($_POST['alamat']);
    $status_aktif = cleanInput($_POST['status_aktif']);
    $tipe_pegawai = cleanInput($_POST['tipe_pegawai']);

    // Validation
    if (empty($nama_lengkap) || empty($email) || empty($no_hp) || empty($tempat_lahir) || empty($tanggal_lahir) || empty($jenis_kelamin) || empty($alamat) || empty($status_aktif) || empty($tipe_pegawai)) {
        $error = 'Semua field yang bertanda * harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif ($tipe_pegawai !== 'dosen_luar' && empty($nik) && empty($nomor_induk)) {
        $error = 'Nomor Induk atau NIK harus diisi untuk pegawai tetap!';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM pegawai WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email sudah digunakan!';
        } else {
            // Handle image upload first if provided and valid
            $imageFileName = null; // Default value
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK && $_FILES['foto']['size'] > 0) {
                // Upload image with temporary ID (0), will be renamed with actual pegawai ID after insertion
                $imageFileName = uploadImage($_FILES['foto'], 0, 'pegawai');

                if (!$imageFileName) {
                    $error = 'Gagal mengupload gambar! Pastikan file adalah gambar valid (JPG, PNG, GIF) dengan ukuran maksimal 5MB.';
                }
            }

            if (empty($error)) {
                // Insert pegawai into database first
                $stmt = $pdo->prepare("
                    INSERT INTO pegawai (nomor_induk, nik, nama_lengkap, email, no_hp, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, foto, status_aktif, tipe_pegawai)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                if ($stmt->execute([$nomor_induk, $nik, $nama_lengkap, $email, $no_hp, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $imageFileName, $status_aktif, $tipe_pegawai])) {
                    $pegawaiId = $pdo->lastInsertId();

                    // If image was uploaded successfully, update its filename to include the actual pegawai ID
                    if ($imageFileName) {
                        // Regenerate filename with actual pegawai ID
                        $newImageFileName = $pegawaiId . '_' . time() . '.' . pathinfo($imageFileName, PATHINFO_EXTENSION);
                        $oldPath = UPLOAD_PATH . 'pegawai/' . $imageFileName;
                        $newPath = UPLOAD_PATH . 'pegawai/' . $newImageFileName;

                        if (rename($oldPath, $newPath)) {
                            // Update database record with new filename
                            $stmt = $pdo->prepare("UPDATE pegawai SET foto = ? WHERE id = ?");
                            $stmt->execute([$newImageFileName, $pegawaiId]);

                            $imageFileName = $newImageFileName; // Update variable to new filename
                        } else {
                            // If rename fails, use the temporary filename with pegawai ID
                            $stmt = $pdo->prepare("UPDATE pegawai SET foto = ? WHERE id = ?");
                            $stmt->execute([$imageFileName, $pegawaiId]);
                        }
                    }

                    // Create user account for the new employee
                    $username = !empty($nik) ? $nik : $nomor_induk; // Use NIK if available, otherwise nomor_induk
                    if (!empty($username)) {
                        $defaultPassword = password_hash('1234567', PASSWORD_DEFAULT);
                        $firstName = explode(' ', $nama_lengkap)[0]; // Get first word as first name
                        $lastName = implode(' ', array_slice(explode(' ', $nama_lengkap), 1)); // Remaining words as last name

                        // Determine role based on employee type (case-insensitive search)
                        if (stripos($tipe_pegawai, 'dosen') !== false) {
                            // For dosen types, use 'dosen' role
                            $roleId = 2;
                        } else {
                            // For tendik types, use 'tendik' role
                            $roleId = 3;
                        }

                        // Check if username already exists to avoid conflict
                        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                        $stmt->execute([$username]);
                        if (!$stmt->fetch()) {
                            $stmt = $pdo->prepare("
                                INSERT INTO users (username, email, password, first_name, last_name, phone, role_id, is_active)
                                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                            ");
                            $stmt->execute([
                                $username,
                                $email,
                                $defaultPassword,
                                $firstName,
                                $lastName,
                                $no_hp,
                                $roleId
                            ]);
                        }
                    }

                    logActivity($_SESSION['user_id'], 'create_pegawai', "Created new pegawai: $nama_lengkap");
                    setAlert('success', 'Pegawai berhasil ditambahkan!');
                    redirect('index.php?page=adminRole_pegawai');
                } else {
                    // If image was uploaded but pegawai insertion failed, delete the uploaded image
                    if ($imageFileName) {
                        deleteImage($imageFileName, 'pegawai');
                    }
                    $error = 'Gagal menambahkan pegawai!';
                }
            }
        }
    }
}

// Get all status and tipe for dropdowns
$status = $pdo->query("SELECT DISTINCT status_aktif FROM pegawai WHERE status_aktif IS NOT NULL ORDER BY status_aktif")->fetchAll();
$tipe = $pdo->query("SELECT DISTINCT tipe_pegawai FROM pegawai WHERE tipe_pegawai IS NOT NULL ORDER BY tipe_pegawai")->fetchAll();
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=adminRole_pegawai" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Pegawai Baru</h1>
            <p class="text-gray-500 mt-1">Tambahkan data pegawai baru</p>
        </div>
    </div>
</div>

<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
    <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-2xl shadow-sm p-6">
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Induk</label>
                <input type="text" name="nomor_induk"
                       value="<?php echo isset($_POST['nomor_induk']) ? htmlspecialchars($_POST['nomor_induk']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk dosen luar</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">NIK</label>
                <input type="text" name="nik"
                       value="<?php echo isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan untuk dosen luar</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" required
                       value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No HP *</label>
                <input type="text" name="no_hp" required
                       value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir *</label>
                <input type="text" name="tempat_lahir" required
                       value="<?php echo isset($_POST['tempat_lahir']) ? htmlspecialchars($_POST['tempat_lahir']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir *</label>
                <input type="date" name="tanggal_lahir" required
                       value="<?php echo isset($_POST['tanggal_lahir']) ? htmlspecialchars($_POST['tanggal_lahir']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin *</label>
                <select name="jenis_kelamin" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="L" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="P" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'P') ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Aktif *</label>
                <select name="status_aktif" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Status</option>
                    <?php foreach ($status as $s): ?>
                        <option value="<?php echo $s['status_aktif']; ?>"
                                <?php echo (isset($_POST['status_aktif']) && $_POST['status_aktif'] === $s['status_aktif']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $s['status_aktif'])); ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options if no records exist yet -->
                    <option value="aktif" <?php echo (isset($_POST['status_aktif']) && $_POST['status_aktif'] === 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                    <option value="non-aktif" <?php echo (isset($_POST['status_aktif']) && $_POST['status_aktif'] === 'non-aktif') ? 'selected' : ''; ?>>Non-Aktif</option>
                    <option value="pensiun" <?php echo (isset($_POST['status_aktif']) && $_POST['status_aktif'] === 'pensiun') ? 'selected' : ''; ?>>Pensiun</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Pegawai *</label>
                <select name="tipe_pegawai" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Tipe</option>
                    <?php foreach ($tipe as $t): ?>
                        <option value="<?php echo $t['tipe_pegawai']; ?>"
                                <?php echo (isset($_POST['tipe_pegawai']) && $_POST['tipe_pegawai'] === $t['tipe_pegawai']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $t['tipe_pegawai'])); ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options if no records exist yet -->
                    <option value="dosen_tetap" <?php echo (isset($_POST['tipe_pegawai']) && $_POST['tipe_pegawai'] === 'dosen_tetap') ? 'selected' : ''; ?>>Dosen Tetap</option>
                    <option value="dosen_luar" <?php echo (isset($_POST['tipe_pegawai']) && $_POST['tipe_pegawai'] === 'dosen_luar') ? 'selected' : ''; ?>>Dosen Luar</option>
                    <option value="tendik_tetap" <?php echo (isset($_POST['tipe_pegawai']) && $_POST['tipe_pegawai'] === 'tendik_tetap') ? 'selected' : ''; ?>>Tendik Tetap</option>
                    <option value="tendik_kontrak" <?php echo (isset($_POST['tipe_pegawai']) && $_POST['tipe_pegawai'] === 'tendik_kontrak') ? 'selected' : ''; ?>>Tendik Kontrak</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat *</label>
            <textarea name="alamat" rows="4" required
                      class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Pegawai</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                        <label for="foto" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                            <span>Unggah file</span>
                            <input id="foto" name="foto" type="file" accept="image/*" class="sr-only">
                        </label>
                        <p class="pl-1">atau seret dan lepas</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF maksimal 5MB</p>
                </div>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Simpan Pegawai
            </button>
            <a href="index.php?page=pegawai" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('foto');
    const uploadArea = fileInput.closest('.mt-1');
    const tipePegawaiSelect = document.querySelector('select[name="tipe_pegawai"]');
    const nomorIndukInput = document.querySelector('input[name="nomor_induk"]');
    const nikInput = document.querySelector('input[name="nik"]');

    // Handle file selection
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const fileType = file.type.split('/')[0]; // Get file type (image, video, etc.)

            // Check if it's an image
            if (fileType === 'image') {
                // Change border color to indicate file is selected
                uploadArea.classList.remove('border-gray-300');
                uploadArea.classList.add('border-green-500', 'bg-green-50');

                // Update text to show filename
                const textElements = uploadArea.querySelectorAll('.text-sm');
                if (textElements.length > 1) {
                    textElements[1].innerHTML = `<span class="text-green-600 font-medium">File dipilih: ${file.name}</span>`;
                } else {
                    // If no existing text element, create one
                    const filenameDiv = document.createElement('div');
                    filenameDiv.className = 'text-sm text-green-600 font-medium mt-2';
                    filenameDiv.textContent = `File dipilih: ${file.name}`;
                    uploadArea.appendChild(filenameDiv);
                }

                // Add preview of the image if possible
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remove any previous preview
                    const existingPreview = uploadArea.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }

                    // Create preview container
                    const previewContainer = document.createElement('div');
                    previewContainer.className = 'image-preview mt-3';
                    previewContainer.style.maxWidth = '200px';
                    previewContainer.style.maxHeight = '200px';

                    const previewImg = document.createElement('img');
                    previewImg.src = e.target.result;
                    previewImg.alt = 'Preview Gambar';
                    previewImg.className = 'w-full h-auto rounded border';
                    previewImg.style.maxWidth = '100%';
                    previewImg.style.maxHeight = '200px';
                    previewImg.style.objectFit = 'contain';

                    previewContainer.appendChild(previewImg);
                    uploadArea.appendChild(previewContainer);
                }
                reader.readAsDataURL(file);
            } else {
                // Reset to original state if not an image
                resetUploadArea();
                alert('Silakan pilih file gambar (JPG, PNG, GIF).');
            }
        } else {
            // Reset if no file is selected (user canceled)
            resetUploadArea();
        }
    });

    // Function to reset upload area to original state
    function resetUploadArea() {
        uploadArea.classList.remove('border-green-500', 'bg-green-50');
        uploadArea.classList.add('border-gray-300');

        // Remove preview if exists
        const existingPreview = uploadArea.querySelector('.image-preview');
        if (existingPreview) {
            existingPreview.remove();
        }

        // Remove filename text and add back original text
        const textElements = uploadArea.querySelectorAll('.text-sm');
        if (textElements.length > 1) {
            textElements[1].innerHTML = '<span>Unggah file</span>';
        }
    }

    // Handle drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-blue-500', 'bg-blue-50');
    });

    uploadArea.addEventListener('dragleave', function() {
        this.classList.remove('border-blue-500', 'bg-blue-50');

        // Return to green if file already selected
        if (fileInput.files.length > 0) {
            this.classList.add('border-green-500', 'bg-green-50');
        } else {
            this.classList.add('border-gray-300');
        }
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-blue-500', 'bg-blue-50');

        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;

            // Trigger change event to handle the file
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        }
    });
    
    // Handle tipe pegawai change
    tipePegawaiSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        
        // If dosen_luar is selected, make nomor_induk and nik optional
        if (selectedValue === 'dosen_luar') {
            nomorIndukInput.required = false;
            nikInput.required = false;
        } else {
            // For other types, make at least one of nomor_induk or nik required
            nomorIndukInput.required = false;
            nikInput.required = false;
        }
    });
});
</script>
</div>