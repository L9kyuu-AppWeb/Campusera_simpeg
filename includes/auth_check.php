<?php
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php?page=login');
    exit;
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    redirect('index.php?page=login&timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

// Get current user
$currentUser = getCurrentUser();
if (!$currentUser || $currentUser['is_active'] != 1) {
    session_unset();
    session_destroy();
    redirect('index.php?page=login&error=inactive');
    exit;
}

// Store in session for easy access
$_SESSION['username'] = $currentUser['username'];
$_SESSION['role_name'] = $currentUser['role_name'];
$_SESSION['full_name'] = $currentUser['first_name'] . ' ' . $currentUser['last_name'];