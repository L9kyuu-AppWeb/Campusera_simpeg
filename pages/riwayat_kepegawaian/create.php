<?php
// Check permission
if (!hasRole(['admin'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';
$success = false;

// Get all pegawai for dropdown
$pegawaiList = $pdo->query("SELECT id, nama_lengkap, email FROM pegawai ORDER BY nama_lengkap ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_perubahan = cleanInput($_POST['jenis_perubahan']);
    $keterangan = !empty($_POST['keterangan']) ? cleanInput($_POST['keterangan']) : null;
    $tanggal_efektif = cleanInput($_POST['tanggal_efektif']);
    $pegawai_ids = !empty($_POST['pegawai_ids']) ? $_POST['pegawai_ids'] : [];

    // Document SK information
    $nomor_sk = !empty($_POST['nomor_sk']) ? cleanInput($_POST['nomor_sk']) : null;
    $judul_sk = !empty($_POST['judul_sk']) ? cleanInput($_POST['judul_sk']) : null;
    $tanggal_sk = !empty($_POST['tanggal_sk']) ? cleanInput($_POST['tanggal_sk']) : null;

    // Validation
    if (empty($jenis_perubahan) || empty($tanggal_efektif) || empty($pegawai_ids)) {
        $error = 'Semua field yang bertanda * harus diisi!';
    } else {
        // Check if we need to create or link to an existing dokumen SK
        $dokumen_sk_id = null; // Default value - will store the ID from dokumen_sk table
        if (isset($_FILES['dokumen_sk']) && $_FILES['dokumen_sk']['error'] === UPLOAD_ERR_OK && $_FILES['dokumen_sk']['size'] > 0) {
            // Upload new document SK
            $uploadedDoc = uploadDocument($_FILES['dokumen_sk'], 0, 'dokumen_sk');

            if ($uploadedDoc) {
                // Insert document info into dokumen_sk table
                $stmt_doc = $pdo->prepare("
                    INSERT INTO dokumen_sk (nomor_sk, judul, tanggal_sk, jenis_perubahan, dokumen_sk)
                    VALUES (?, ?, ?, ?, ?)
                ");

                if ($stmt_doc->execute([$nomor_sk, $judul_sk, $tanggal_sk, $jenis_perubahan, $uploadedDoc])) {
                    $dokumen_sk_id = $pdo->lastInsertId();
                    $uploadedDocForCleanup = $uploadedDoc; // Store for cleanup if needed
                } else {
                    $error = 'Gagal menyimpan informasi dokumen SK!';
                }
            } else {
                $error = 'Gagal mengupload dokumen SK!';
            }
        } else if (!empty($nomor_sk) || !empty($judul_sk)) {
            // Link to existing document by nomor SK or title
            $stmt_find = $pdo->prepare("SELECT id FROM dokumen_sk WHERE nomor_sk = ? OR judul = ?");
            $stmt_find->execute([$nomor_sk, $judul_sk]);
            $existingDoc = $stmt_find->fetch();

            if ($existingDoc) {
                $dokumen_sk_id = $existingDoc['id'];
            } else if (!empty($nomor_sk)) {
                // Create new entry for document reference without file
                $stmt_doc = $pdo->prepare("
                    INSERT INTO dokumen_sk (nomor_sk, judul, tanggal_sk, jenis_perubahan)
                    VALUES (?, ?, ?, ?)
                ");

                if ($stmt_doc->execute([$nomor_sk, $judul_sk, $tanggal_sk, $jenis_perubahan])) {
                    $dokumen_sk_id = $pdo->lastInsertId();
                } else {
                    $error = 'Gagal menyimpan referensi dokumen SK!';
                }
            }
        }

        if (empty($error)) {
            $pdo->beginTransaction(); // Use transaction to ensure data consistency

            try {
                // Process each selected employee
                $successCount = 0;
                foreach ($pegawai_ids as $pegawai_id) {
                    $pegawai_id = (int)$pegawai_id;

                    // Insert riwayat_kepegawaian into database
                    $stmt = $pdo->prepare("
                        INSERT INTO riwayat_kepegawaian (pegawai_id, jenis_perubahan, keterangan, tanggal_efektif, dokumen_sk_id)
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    if ($stmt->execute([$pegawai_id, $jenis_perubahan, $keterangan, $tanggal_efektif, $dokumen_sk_id])) {
                        // Also insert into dokumen_sk_pegawai table to maintain many-to-many relationship
                        $stmt_rel = $pdo->prepare("
                            INSERT INTO dokumen_sk_pegawai (dokumen_sk_id, pegawai_id)
                            VALUES (?, ?)
                        ");
                        $stmt_rel->execute([$dokumen_sk_id, $pegawai_id]);
                        $successCount++;
                    } else {
                        throw new Exception("Gagal menambahkan riwayat kepegawaian untuk pegawai ID: $pegawai_id");
                    }
                }

                $pdo->commit(); // Commit the transaction

                logActivity($_SESSION['user_id'], 'create_riwayat_kepegawaian', "Created new riwayat_kepegawaian for $successCount pegawai");
                setAlert('success', "$successCount Riwayat kepegawaian berhasil ditambahkan!");
                redirect('index.php?page=riwayat_kepegawaian');
            } catch (Exception $e) {
                $pdo->rollback(); // Rollback the transaction

                // If document was uploaded but insertion failed, delete the uploaded document
                if (isset($uploadedDocForCleanup) && $uploadedDocForCleanup) {
                    deleteDocument($uploadedDocForCleanup, 'dokumen_sk');

                    // Also delete from dokumen_sk table if entry was created
                    if ($dokumen_sk_id) {
                        $pdo->prepare("DELETE FROM dokumen_sk WHERE id = ?")->execute([$dokumen_sk_id]);
                    }
                }

                $error = $e->getMessage();
            }
        }
    }
}

// Get all jenis perubahan for dropdown
$jenis = $pdo->query("SELECT DISTINCT jenis_perubahan FROM riwayat_kepegawaian WHERE jenis_perubahan IS NOT NULL ORDER BY jenis_perubahan")->fetchAll();
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=riwayat_kepegawaian" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Riwayat Kepegawaian Baru</h1>
            <p class="text-gray-500 mt-1">Tambahkan data riwayat perubahan kepegawaian baru</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai *</label>
                <select name="pegawai_ids[]" multiple size="5" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <?php foreach ($pegawaiList as $pegawai): ?>
                        <option value="<?php echo $pegawai['id']; ?>"
                                <?php echo (isset($_POST['pegawai_ids']) && in_array($pegawai['id'], $_POST['pegawai_ids'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pegawai['nama_lengkap'] . ' (' . $pegawai['email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">Gunakan CTRL/CMD untuk memilih lebih dari satu pegawai</p>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700">Jenis Perubahan *</label>
                    <button type="button" onclick="openJenisPerubahanModal()" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Bantuan
                    </button>
                </div>
                <select name="jenis_perubahan" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Jenis</option>
                    <?php foreach ($jenis as $j): ?>
                        <option value="<?php echo $j['jenis_perubahan']; ?>"
                                <?php echo (isset($_POST['jenis_perubahan']) && $_POST['jenis_perubahan'] === $j['jenis_perubahan']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $j['jenis_perubahan'])); ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options if no records exist yet -->
                    <option value="jabatan_struktural_fungsional" <?php echo (isset($_POST['jenis_perubahan']) && $_POST['jenis_perubahan'] === 'jabatan_struktural_fungsional') ? 'selected' : ''; ?>>Jabatan Struktural / Fungsional</option>
                    <option value="tugas_tambahan_akademik" <?php echo (isset($_POST['jenis_perubahan']) && $_POST['jenis_perubahan'] === 'tugas_tambahan_akademik') ? 'selected' : ''; ?>>Tugas Tambahan Akademik</option>
                    <option value="kepanitiaan_resmi" <?php echo (isset($_POST['jenis_perubahan']) && $_POST['jenis_perubahan'] === 'kepanitiaan_resmi') ? 'selected' : ''; ?>>Kepanitiaan Resmi</option>
                    <option value="penugasan_khusus" <?php echo (isset($_POST['jenis_perubahan']) && $_POST['jenis_perubahan'] === 'penugasan_khusus') ? 'selected' : ''; ?>>Penugasan Khusus</option>
                    <option value="pelatihan_sertifikasi_pengembangan_kompetensi" <?php echo (isset($_POST['jenis_perubahan']) && $_POST['jenis_perubahan'] === 'pelatihan_sertifikasi_pengembangan_kompetensi') ? 'selected' : ''; ?>>Pelatihan, Sertifikasi, dan Pengembangan Kompetensi</option>
                    <option value="penghargaan_reward_pegawai" <?php echo (isset($_POST['jenis_perubahan']) && $_POST['jenis_perubahan'] === 'penghargaan_reward_pegawai') ? 'selected' : ''; ?>>Penghargaan / Reward Pegawai</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Efektif *</label>
                <input type="date" name="tanggal_efektif" required
                       value="<?php echo isset($_POST['tanggal_efektif']) ? htmlspecialchars($_POST['tanggal_efektif']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Dokumen</label>
                <input type="text" name="nomor_sk"
                       value="<?php echo isset($_POST['nomor_sk']) ? htmlspecialchars($_POST['nomor_sk']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Judul Dokumen</label>
                <input type="text" name="judul_sk"
                       value="<?php echo isset($_POST['judul_sk']) ? htmlspecialchars($_POST['judul_sk']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dokumen</label>
                <input type="date" name="tanggal_sk"
                       value="<?php echo isset($_POST['tanggal_sk']) ? htmlspecialchars($_POST['tanggal_sk']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dokumen Pendukung (Opsional)</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="dokumen_sk" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Unggah file</span>
                                <input id="dokumen_sk" name="dokumen_sk" type="file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="sr-only">
                            </label>
                            <p class="pl-1">atau seret dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF, DOC, DOCX maksimal 5MB</p>
                        <p class="text-xs text-gray-500">(Kosongkan jika hanya ingin mereferensikan dokumen tanpa mengupload file)</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
            <textarea name="keterangan" rows="4"
                      class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"><?php echo isset($_POST['keterangan']) ? htmlspecialchars($_POST['keterangan']) : ''; ?></textarea>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Simpan Riwayat
            </button>
            <a href="index.php?page=riwayat_kepegawaian" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('dokumen_sk');
    const uploadArea = fileInput.closest('.mt-1');

    // Handle file selection
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const fileType = file.type.split('/')[0]; // Get file type (application, text, etc.)

            // Check if it's a document file
            if (fileType === 'application' || file.name.toLowerCase().endsWith('.pdf') || file.name.toLowerCase().endsWith('.doc') || file.name.toLowerCase().endsWith('.docx')) {
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

                // Add preview of the document if possible
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remove any previous preview
                    const existingPreview = uploadArea.querySelector('.document-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }

                    // Create preview container
                    const previewContainer = document.createElement('div');
                    previewContainer.className = 'document-preview mt-3';
                    previewContainer.style.maxWidth = '200px';
                    previewContainer.style.maxHeight = '200px';

                    const previewLink = document.createElement('a');
                    previewLink.href = '#';
                    previewLink.target = '_blank';
                    previewLink.className = 'w-full h-auto rounded border';
                    previewLink.style.maxWidth = '100%';
                    previewLink.style.maxHeight = '200px';
                    previewLink.style.objectFit = 'contain';
                    previewLink.textContent = 'Lihat Pratinjau Dokumen';
                    previewLink.style.color = 'blue';

                    previewContainer.appendChild(previewLink);
                    uploadArea.appendChild(previewContainer);
                }
                reader.readAsDataURL(file);
            } else {
                // Reset to original state if not a document
                resetUploadArea();
                alert('Silakan pilih file dokumen (PDF, DOC, DOCX).');
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
        const existingPreview = uploadArea.querySelector('.document-preview');
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
});

// Modal functionality
function openJenisPerubahanModal() {
    const modal = document.getElementById('jenisPerubahanModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeJenisPerubahanModal() {
    const modal = document.getElementById('jenisPerubahanModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('jenisPerubahanModal');
    if (event.target === modal) {
        closeJenisPerubahanModal();
    }
}
</script>

<!-- Modal for Jenis Perubahan Explanation -->
<div id="jenisPerubahanModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-bold text-gray-800">Kategori Riwayat Kepegawaian yang Relevan di Universitas</h3>
                <button type="button" onclick="closeJenisPerubahanModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="prose max-w-none">
                <p class="text-gray-600 mb-4">Riwayat kepegawaian tidak hanya soal kenaikan pangkat dan mutasi, tetapi segala bentuk perubahan status, tugas, atau peran resmi pegawai. Berikut daftar rekomendasinya:</p>

                <div class="space-y-4">
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">1. Jabatan Struktural / Fungsional</h4>
                        <p class="text-gray-600 mb-2">Ini adalah perubahan yang pasti layak masuk riwayat kepegawaian.</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1 ml-4">
                            <li>Kepala Program Studi (Kaprodi)</li>
                            <li>Sekretaris Prodi</li>
                            <li>Ketua Jurusan</li>
                            <li>Sekretaris Jurusan</li>
                            <li>Wakil Dekan</li>
                            <li>Kepala Unit (UPT TI, UPT Bahasa, LPPM, LP3M)</li>
                            <li>Koordinator Lab / Studio</li>
                            <li>Dosen dengan jabatan fungsional (Asisten Ahli, Lektor, Lektor Kepala, Guru Besar)</li>
                            <li>Mutasi unit kerja</li>
                            <li>Tugas tambahan sebagai pejabat tertentu</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">2. Tugas Tambahan Akademik</h4>
                        <p class="text-gray-600 mb-2">Biasanya menjadi bagian dari SK Rektor/Dekan.</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1 ml-4">
                            <li>Penanggung jawab mata kuliah tertentu</li>
                            <li>Penasehat akademik / DPA</li>
                            <li>Penanggung jawab kurikulum</li>
                            <li>Pembina UKM / Organisasi Mahasiswa</li>
                            <li>Dosen penguji skripsi/tesis/disertasi</li>
                            <li>Dosen pembimbing KKN / Magang / Kampus Merdeka</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">3. Kepanitiaan Resmi</h4>
                        <p class="text-gray-600 mb-2">➡️ Pertanyaan inti Anda: apakah kepanitiaan bisa dimasukkan?</p>
                        <p class="text-gray-600 mb-2">Jawabannya: YA, jika kepanitiaan tersebut dibuat dengan SK resmi (Rektor/Dekan/Ketua Unit).<br>Ini termasuk:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1 ml-4">
                            <li>Panitia Wisuda</li>
                            <li>Panitia Seminar Nasional/Internasional</li>
                            <li>Panitia Penerimaan Mahasiswa Baru (PMB)</li>
                            <li>Panitia Audit Mutu Internal (AMI)</li>
                            <li>Panitia Akreditasi Program Studi / Institusi</li>
                            <li>Panitia Event Universitas (Dies Natalis, Expo, Festival Seni)</li>
                            <li>Panitia Kegiatan MBKM (Magang, Studi Independen, KKN Terpadu)</li>
                        </ul>
                        <p class="text-gray-600 mt-2">Kuncinya: ada SK → maka boleh masuk riwayat kepegawaian.</p>
                    </div>

                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">4. Penugasan Khusus</h4>
                        <p class="text-gray-600 mb-2">Biasanya ada SK Rektor/Dekan.</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1 ml-4">
                            <li>Tugas belajar (S2/S3)</li>
                            <li>Tugas penelitian hibah</li>
                            <li>Penugasan ke luar negeri</li>
                            <li>Penugasan mengajar di prodi/fakultas lain</li>
                            <li>Penugasan menjadi reviewer jurnal / asesor BAN-PT/LAM</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">5. Pelatihan, Sertifikasi, dan Pengembangan Kompetensi</h4>
                        <p class="text-gray-600 mb-2">Jika berhubungan dengan kepegawaian.</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1 ml-4">
                            <li>Pelatihan Pekerti/AA (Pendidik Profesional)</li>
                            <li>Sertifikasi dosen (Serdos)</li>
                            <li>Workshop Kurikulum</li>
                            <li>Pelatihan Kepemimpinan (PKA, PKM, PKA Tingkat IV)</li>
                            <li>Pelatihan IT, keuangan, administrasi kampus</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">6. Penghargaan / Reward Pegawai</h4>
                        <p class="text-gray-600 mb-2">Jika diterbitkan oleh universitas:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-1 ml-4">
                            <li>Dosen Berprestasi</li>
                            <li>Tendik Berprestasi</li>
                            <li>Penghargaan Penelitian / Publikasi Terbaik</li>
                            <li>Satya Lencana 10/20/30 tahun</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" onclick="closeJenisPerubahanModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
</div>