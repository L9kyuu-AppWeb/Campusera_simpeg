<?php
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'copy') {
    // Check if config-example.php exists
    if (!file_exists('config-example.php')) {
        echo "Error: config-example.php not found!";
        exit;
    }

    // Check if config.php already exists
    if (file_exists('config.php')) {
        echo "config.php already exists. Please remove it first if you want to recreate it.";
        exit;
    }

    // Attempt to copy config-example.php to config.php
    if (copy('config-example.php', 'config.php')) {
        echo "Successfully copied config-example.php to config.php!\n\n" .
             "Note: You still need to edit config.php to add your database credentials and other settings.";
    } else {
        echo "Failed to copy config-example.php to config.php.\n" .
             "Please manually copy the file or check file permissions.";
    }
} else {
    echo "Invalid request.";
}
?>