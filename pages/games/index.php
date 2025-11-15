<?php
// Check permission
if (!hasRole(['admin', 'manager'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$action = isset($_GET['action']) ? cleanInput($_GET['action']) : 'list';

switch ($action) {
    case 'create':
        require_once 'create.php';
        break;
    case 'edit':
        require_once 'edit.php';
        break;
    case 'delete':
        require_once 'delete.php';
        break;
    default:
        // List games
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $genreFilter = isset($_GET['genre']) ? cleanInput($_GET['genre']) : '';
        $platformFilter = isset($_GET['platform']) ? cleanInput($_GET['platform']) : '';

        $sql = "SELECT * FROM games WHERE 1=1";

        if ($search) {
            $sql .= " AND (title LIKE :search1 OR description LIKE :search2 OR genre LIKE :search3 OR platform LIKE :search4)";
        }

        if ($genreFilter) {
            $sql .= " AND genre = :genre";
        }

        if ($platformFilter) {
            $sql .= " AND platform = :platform";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($search) {
            $stmt->bindValue(':search1', "%$search%");
            $stmt->bindValue(':search2', "%$search%");
            $stmt->bindValue(':search3', "%$search%");
            $stmt->bindValue(':search4', "%$search%");
        }
        if ($genreFilter) {
            $stmt->bindValue(':genre', $genreFilter);
        }
        if ($platformFilter) {
            $stmt->bindValue(':platform', $platformFilter);
        }

        $stmt->execute();
        $games = $stmt->fetchAll();

        // Get distinct genres and platforms for filter
        $genres = $pdo->query("SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL ORDER BY genre")->fetchAll();
        $platforms = $pdo->query("SELECT DISTINCT platform FROM games WHERE platform IS NOT NULL ORDER BY platform")->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Game Management</h1>
        <p class="text-gray-500 mt-1">Kelola koleksi game</p>
    </div>
    <?php if (hasRole(['admin', 'manager'])): ?>
    <a href="index.php?page=games&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Game</span>
    </a>
    <?php endif; ?>
</div>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <input type="hidden" name="page" value="games">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                   placeholder="Cari judul, deskripsi, genre, atau platform..."
                   class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
        </div>
        <select name="genre" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Genre</option>
            <?php foreach ($genres as $genre): ?>
                <option value="<?php echo $genre['genre']; ?>" <?php echo $genreFilter === $genre['genre'] ? 'selected' : ''; ?>>
                    <?php echo ucfirst($genre['genre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="platform" class="px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
            <option value="">Semua Platform</option>
            <?php foreach ($platforms as $platform): ?>
                <option value="<?php echo $platform['platform']; ?>" <?php echo $platformFilter === $platform['platform'] ? 'selected' : ''; ?>>
                    <?php echo $platform['platform']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-xl transition-colors">
            Filter
        </button>
        <?php if ($search || $genreFilter || $platformFilter): ?>
        <a href="index.php?page=games" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2 rounded-xl transition-colors inline-flex items-center">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Games Card View -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (count($games) > 0): ?>
        <?php foreach ($games as $game): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Game Image -->
                <div class="relative">
                    <img src="<?php echo getImageUrl($game['image'], 'games'); ?>" 
                         class="w-full h-48 object-cover" 
                         alt="<?php echo htmlspecialchars($game['title']); ?>">
                    <div class="absolute top-3 right-3">
                        <?php if ($game['is_active']): ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-green-500 text-white">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Aktif
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-red-500 text-white">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Tidak Aktif
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Game Content -->
                <div class="p-5">
                    <div class="mb-3">
                        <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($game['title']); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars(substr($game['description'], 0, 100)) . (strlen($game['description']) > 100 ? '...' : ''); ?>
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <p class="text-xs text-gray-500">Genre</p>
                            <p class="font-medium"><?php echo htmlspecialchars($game['genre']); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Platform</p>
                            <p class="font-medium"><?php echo htmlspecialchars($game['platform']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-800">Rp <?php echo number_format($game['price'], 2); ?></span>
                        
                        <div class="flex space-x-2">
                            <a href="index.php?page=games&action=edit&id=<?php echo $game['id']; ?>"
                               class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="index.php?page=games&action=delete&id=<?php echo $game['id']; ?>"
                               class="text-red-600 hover:text-red-800 transition-colors" title="Hapus">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full text-center py-12">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tidak ada data game</p>
        </div>
    <?php endif; ?>
</div>

<?php
        break;
}
?>