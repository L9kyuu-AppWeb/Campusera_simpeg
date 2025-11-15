<?php
if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'logout', 'User logged out');
}

session_unset();
session_destroy();
redirect('index.php?page=login');
?>