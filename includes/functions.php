<?php
// Redirect function
function redirect($url) {
    // Pastikan tidak ada output sebelum redirect
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        // Fallback jika header sudah terkirim
        echo "<script>window.location.href='" . htmlspecialchars($url) . "';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url) . "'></noscript>";
        exit();
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("
        SELECT u.*, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Check user role
function hasRole($roleNames) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    if (is_array($roleNames)) {
        return in_array($user['role_name'], $roleNames);
    }
    return $user['role_name'] === $roleNames;
}

// Sanitize input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Log activity
function logActivity($userId, $type, $description) {
    global $pdo;
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, activity_type, description, ip_address) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $type, $description, $_SERVER['REMOTE_ADDR']]);
}

// Format date Indonesia
function formatDate($date) {
    return date('d F Y H:i', strtotime($date));
}

// Get avatar URL
function getAvatarUrl($avatar) {
    if (empty($avatar) || $avatar === 'default-avatar.png') {
        return 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['username'] ?? 'User') . '&background=3b82f6&color=fff';
    }
    return BASE_URL . 'assets/uploads/avatars/' . $avatar;
}

// Alert message
function setAlert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

// Upload image
function uploadImage($file, $itemId = 0, $imageType = 'general') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Check if uploads directory exists, create if not
    $uploadDir = UPLOAD_PATH . $imageType . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Allowed file extensions
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Check file type
    if (!in_array($fileExtension, $allowedExtensions)) {
        return false;
    }

    // Check file size (limit to 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    // Generate unique filename
    // If item ID is 0 (temporary), use timestamp instead
    if ($itemId == 0) {
        $fileName = 'tmp_' . time() . '_' . uniqid() . '.' . $fileExtension;
    } else {
        $fileName = $itemId . '_' . time() . '.' . $fileExtension;
    }
    $targetPath = $uploadDir . $fileName;

    // Resize image if needed (optional)
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }

    return false;
}

// Delete image
function deleteImage($imageName, $imageType = 'general') {
    if (!empty($imageName)) {
        $imagePath = UPLOAD_PATH . $imageType . '/' . $imageName;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}

// Get image URL
function getImageUrl($imageName, $imageType = 'general') {
    if (empty($imageName)) {
        return BASE_URL . 'assets/images/no-image.svg'; // Default image if none exists
    }
    return BASE_URL . 'assets/uploads/' . $imageType . '/' . $imageName;
}

// Upload document
function uploadDocument($file, $itemId = 0, $docType = 'general') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Check if uploads directory exists, create if not
    $uploadDir = UPLOAD_PATH . $docType . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Allowed file extensions
    $allowedExtensions = ['pdf', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Check file type
    if (!in_array($fileExtension, $allowedExtensions)) {
        return false;
    }

    // Check file size (limit to 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    // Generate unique filename
    // If item ID is 0 (temporary), use timestamp instead
    if ($itemId == 0) {
        $fileName = 'tmp_' . time() . '_' . uniqid() . '.' . $fileExtension;
    } else {
        $fileName = $itemId . '_' . time() . '.' . $fileExtension;
    }
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }

    return false;
}

// Delete document
function deleteDocument($docName, $docType = 'general') {
    if (!empty($docName)) {
        $docPath = UPLOAD_PATH . $docType . '/' . $docName;
        if (file_exists($docPath)) {
            unlink($docPath);
        }
    }
}

// Get document URL
function getDocumentUrl($docName, $docType = 'general') {
    if (empty($docName)) {
        return BASE_URL . 'assets/docs/no-document.pdf'; // Default document if none exists
    }
    return BASE_URL . 'assets/uploads/' . $docType . '/' . $docName;
}

