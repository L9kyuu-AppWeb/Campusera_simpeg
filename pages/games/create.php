<?php
// Check permission
if (!hasRole(['admin', 'manager'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = cleanInput($_POST['title']);
    $description = cleanInput($_POST['description']);
    $genre = cleanInput($_POST['genre']);
    $release_date = !empty($_POST['release_date']) ? cleanInput($_POST['release_date']) : null;
    $platform = cleanInput($_POST['platform']);
    $price = !empty($_POST['price']) ? floatval($_POST['price']) : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (empty($title) || empty($genre) || empty($platform)) {
        $error = 'Semua field yang bertanda * harus diisi!';
    } else {
        // Check if game with same title already exists
        $stmt = $pdo->prepare("SELECT id FROM games WHERE title = ?");
        $stmt->execute([$title]);

        if ($stmt->fetch()) {
            $error = 'Judul game sudah digunakan!';
        } else {
            // Handle image upload first if provided and valid
            $imageFileName = null; // Default value
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['size'] > 0) {
                // Upload image with temporary ID (0), will be renamed with actual game ID after insertion
                $imageFileName = uploadImage($_FILES['image'], 0, 'games');
                
                if (!$imageFileName) {
                    $error = 'Gagal mengupload gambar! Pastikan file adalah gambar valid (JPG, PNG, GIF) dengan ukuran maksimal 5MB.';
                }
            }

            if (empty($error)) {
                // Insert game into database first
                $stmt = $pdo->prepare("
                    INSERT INTO games (title, description, genre, release_date, platform, price, image, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                if ($stmt->execute([$title, $description, $genre, $release_date, $platform, $price, $imageFileName, $is_active])) {
                    $gameId = $pdo->lastInsertId();

                    // If image was uploaded successfully, update its filename to include the actual game ID
                    if ($imageFileName) {
                        // Regenerate filename with actual game ID
                        $newImageFileName = $gameId . '_' . time() . '.' . pathinfo($imageFileName, PATHINFO_EXTENSION);
                        $oldPath = UPLOAD_PATH . 'games/' . $imageFileName;
                        $newPath = UPLOAD_PATH . 'games/' . $newImageFileName;
                        
                        if (rename($oldPath, $newPath)) {
                            // Update database record with new filename
                            $stmt = $pdo->prepare("UPDATE games SET image = ? WHERE id = ?");
                            $stmt->execute([$newImageFileName, $gameId]);
                            
                            $imageFileName = $newImageFileName; // Update variable to new filename
                        } else {
                            // If rename fails, use the temporary filename with game ID
                            $stmt = $pdo->prepare("UPDATE games SET image = ? WHERE id = ?");
                            $stmt->execute([$imageFileName, $gameId]);
                        }
                    }

                    logActivity($_SESSION['user_id'], 'create_game', "Created new game: $title");
                    setAlert('success', 'Game berhasil ditambahkan!');
                    redirect('index.php?page=games');
                } else {
                    // If image was uploaded but game insertion failed, delete the uploaded image
                    if ($imageFileName) {
                        deleteImage($imageFileName, 'games');
                    }
                    $error = 'Gagal menambahkan game!';
                }
            }
        }
    }
}

// Get all genres and platforms for dropdowns
$genres = $pdo->query("SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL ORDER BY genre")->fetchAll();
$platforms = $pdo->query("SELECT DISTINCT platform FROM games WHERE platform IS NOT NULL ORDER BY platform")->fetchAll();
?>

<div class="mb-6">
    <div class="flex items-center space-x-4 mb-4">
        <a href="index.php?page=games" class="text-gray-600 hover:text-gray-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Tambah Game Baru</h1>
            <p class="text-gray-500 mt-1">Tambahkan game ke koleksi</p>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
                <input type="text" name="title" required 
                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Genre *</label>
                <select name="genre" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Genre</option>
                    <?php foreach ($genres as $genre_item): ?>
                        <option value="<?php echo $genre_item['genre']; ?>" 
                                <?php echo (isset($_POST['genre']) && $_POST['genre'] === $genre_item['genre']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($genre_item['genre']); ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options if no records exist yet -->
                    <option value="Action" <?php echo (isset($_POST['genre']) && $_POST['genre'] === 'Action') ? 'selected' : ''; ?>>Action</option>
                    <option value="Adventure" <?php echo (isset($_POST['genre']) && $_POST['genre'] === 'Adventure') ? 'selected' : ''; ?>>Adventure</option>
                    <option value="RPG" <?php echo (isset($_POST['genre']) && $_POST['genre'] === 'RPG') ? 'selected' : ''; ?>>RPG</option>
                    <option value="Strategy" <?php echo (isset($_POST['genre']) && $_POST['genre'] === 'Strategy') ? 'selected' : ''; ?>>Strategy</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Platform *</label>
                <select name="platform" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    <option value="">Pilih Platform</option>
                    <?php foreach ($platforms as $platform_item): ?>
                        <option value="<?php echo $platform_item['platform']; ?>" 
                                <?php echo (isset($_POST['platform']) && $_POST['platform'] === $platform_item['platform']) ? 'selected' : ''; ?>>
                            <?php echo $platform_item['platform']; ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Default options if no records exist yet -->
                    <option value="PC" <?php echo (isset($_POST['platform']) && $_POST['platform'] === 'PC') ? 'selected' : ''; ?>>PC</option>
                    <option value="PlayStation 5" <?php echo (isset($_POST['platform']) && $_POST['platform'] === 'PlayStation 5') ? 'selected' : ''; ?>>PlayStation 5</option>
                    <option value="Xbox Series X/S" <?php echo (isset($_POST['platform']) && $_POST['platform'] === 'Xbox Series X/S') ? 'selected' : ''; ?>>Xbox Series X/S</option>
                    <option value="Nintendo Switch" <?php echo (isset($_POST['platform']) && $_POST['platform'] === 'Nintendo Switch') ? 'selected' : ''; ?>>Nintendo Switch</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Harga</label>
                <input type="number" name="price" step="0.01" min="0" 
                       value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '0.00'; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Rilis</label>
                <input type="date" name="release_date" 
                       value="<?php echo isset($_POST['release_date']) ? htmlspecialchars($_POST['release_date']) : ''; ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="flex items-center space-x-3 mt-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                           <?php echo (!isset($_POST['is_active']) || $_POST['is_active']) ? 'checked' : ''; ?>
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="text-sm text-gray-700">Game Aktif</label>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
            <textarea name="description" rows="4"
                      class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Game</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                        <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                            <span>Unggah file</span>
                            <input id="image" name="image" type="file" accept="image/*" class="sr-only">
                        </label>
                        <p class="pl-1">atau seret dan lepas</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF maksimal 5MB</p>
                </div>
            </div>
        </div>

        <div class="flex space-x-3 pt-4 border-t">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors">
                Simpan Game
            </button>
            <a href="index.php?page=games" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-6 rounded-xl transition-colors inline-block">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('image');
    const uploadArea = fileInput.closest('.mt-1');
    
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
});
</script>
</div>