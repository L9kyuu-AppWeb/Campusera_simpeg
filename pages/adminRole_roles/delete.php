<?php
// Check permission - only admin can delete roles
if (!hasRole('admin')) {
    require_once __DIR__ . '/../errors/403.php';
    exit;
}

$roleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$roleId) {
    redirect('index.php?page=adminRole_roles');
}

// Get role data
$stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
$stmt->execute([$roleId]);
$role = $stmt->fetch();

if (!$role) {
    setAlert('error', 'Role tidak ditemukan!');
    redirect('index.php?page=adminRole_roles');
}

// Prevent deleting the admin role
if ($role['role_name'] === 'admin') {
    setAlert('error', 'Role admin tidak dapat dihapus!');
    redirect('index.php?page=adminRole_roles');
}

// Check if this role is used by any users
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role_id = ?");
$stmt->execute([$roleId]);
$userCount = $stmt->fetchColumn();

if ($userCount > 0) {
    setAlert('error', 'Role ini sedang digunakan oleh ' . $userCount . ' user dan tidak dapat dihapus!');
    redirect('index.php?page=adminRole_roles');
}

// Delete role
$stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
if ($stmt->execute([$roleId])) {
    logActivity($_SESSION['user_id'], 'delete_role', "Deleted role: " . $role['role_name']);
    setAlert('success', 'Role berhasil dihapus!');
} else {
    setAlert('error', 'Gagal menghapus role!');
}

redirect('index.php?page=roles');
?>