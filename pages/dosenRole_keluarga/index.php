<?php
// Check permission - allow only dosen role
if (!hasRole(['dosen'])) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

// Get current logged in user ID
$current_user_id = $_SESSION['user_id'];

// Get user info first
$user_info_sql = "SELECT * FROM users WHERE id = :user_id";
$user_stmt = $pdo->prepare($user_info_sql);
$user_stmt->bindValue(':user_id', $current_user_id);
$user_stmt->execute();
$user_info = $user_stmt->fetch();

// Get pegawai info based on user's email
$pegawai_sql = "SELECT * FROM pegawai WHERE email = :email";
$pegawai_stmt = $pdo->prepare($pegawai_sql);
$pegawai_stmt->bindValue(':email', $user_info['email']);
$pegawai_stmt->execute();
$pegawai = $pegawai_stmt->fetch();

if (!$pegawai) {
    // If no pegawai record found, show error
    require_once __DIR__ . '/../errors/404.php';
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
    case 'detail':
        require_once 'detail.php';
        break;
    case 'delete':
        require_once 'delete.php';
        break;
    default:
        // Show only the current user's family data
        $family_sql = "SELECT * FROM pegawai_keluarga WHERE pegawai_id = :pegawai_id ORDER BY created_at DESC";
        $family_stmt = $pdo->prepare($family_sql);
        $family_stmt->bindValue(':pegawai_id', $pegawai['id']);
        $family_stmt->execute();
        $keluarga = $family_stmt->fetchAll();
?>

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Keluarga Saya</h1>
        <p class="text-gray-500 mt-1">Data keluarga Anda</p>
    </div>
    <?php if (hasRole(['dosen'])): ?>
    <a href="index.php?page=dosenRole_keluarga&action=create" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-colors inline-flex items-center space-x-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Keluarga</span>
    </a>
    <?php endif; ?>
</div>

<!-- Family Card View for Dosen -->
<div class="space-y-4">
    <?php foreach ($keluarga as $member): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3">
                    <div class="bg-gray-100 rounded-lg w-12 h-12 flex items-center justify-center text-gray-500 flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($member['nama'] ?? ''); ?></h3>
                        <p class="text-sm text-gray-600"><?php
                        $hubungan = $member['hubungan'] ?? '';
                        echo htmlspecialchars($hubungan === 'Suami' ? 'Suami' : ($hubungan === 'Istri' ? 'Istri' : ucfirst(str_replace('_', ' ', $hubungan ?? ''))));
                        ?></p>
                    </div>
                </div>
                <div class="flex space-x-1 ml-2">
                    <a href="index.php?page=dosenRole_keluarga&action=detail&id=<?php echo $member['id']; ?>" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                    <a href="index.php?page=dosenRole_keluarga&action=edit&id=<?php echo $member['id']; ?>" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <a href="index.php?page=dosenRole_keluarga&action=delete&id=<?php echo $member['id']; ?>" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm">
                <div class="flex flex-col">
                    <span class="text-xs text-gray-500">NIK</span>
                    <span class="font-medium"><?php echo htmlspecialchars($member['no_ktp'] ?? '-'); ?></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-gray-500">JK</span>
                    <span class="font-medium"><?php echo htmlspecialchars($member['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'); ?></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-gray-500">Status</span>
                    <span class="font-medium"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $member['status_hidup'] ?? ''))); ?></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs text-gray-500">TTL</span>
                    <span class="font-medium"><?php echo htmlspecialchars($member['tempat_lahir'] ?? '-') . ', ' . htmlspecialchars($member['tanggal_lahir'] ? date('d M Y', strtotime($member['tanggal_lahir'])) : '-'); ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($keluarga)): ?>
    <div class="col-span-full text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data keluarga</h3>
        <p class="mt-1 text-sm text-gray-500">Tambahkan data keluarga Anda pertama kali.</p>
        <div class="mt-6">
            <a href="index.php?page=dosenRole_keluarga&action=create" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Keluarga
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
}
?>