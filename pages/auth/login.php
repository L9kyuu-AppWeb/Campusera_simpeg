<?php
if (isLoggedIn()) {
    redirect('index.php?page=dashboard');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username']);
    $password = cleanInput($_POST['password']);
    $captcha_input = cleanInput($_POST['captcha']);
    $captcha_session = $_SESSION['captcha'] ?? '';
    
    // Validate CAPTCHA
    if (strtolower(trim($captcha_input)) !== strtolower(trim($captcha_session))) {
        $error = 'CAPTCHA tidak valid!';
    } elseif (empty($username) || empty($password) || empty($captcha_input)) {
        $error = 'Username, password, dan CAPTCHA harus diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_active'] == 1) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['last_activity'] = time();

                // Update last login
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                // Log activity
                logActivity($user['id'], 'login', 'User logged in');

                // Clear CAPTCHA session
                unset($_SESSION['captcha']);
                
                redirect('index.php?page=dashboard');
            } else {
                $error = 'Akun Anda tidak aktif!';
            }
        } else {
            $error = 'Username atau password salah!';
        }
    }
}

// Generate CAPTCHA values
$num1 = rand(1, 20);
$num2 = rand(1, 20);
$operation = rand(0, 1); // 0 for addition, 1 for subtraction
$operation_symbol = $operation === 0 ? '+' : '-';
$answer = $operation === 0 ? $num1 + $num2 : $num1 - $num2;

// Store correct answer in session
$_SESSION['captcha'] = $answer;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-700 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800"><?php echo SITE_NAME; ?></h1>
                <p class="text-gray-500 mt-2">Masuk ke akun Anda</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['timeout'])): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-xl mb-4">
                Sesi Anda telah berakhir. Silakan login kembali.
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username atau Email</label>
                    <input type="text" name="username" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                           placeholder="Masukkan username atau email">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                           placeholder="Masukkan password">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CAPTCHA</label>
                    <div class="flex items-center space-x-3">
                        <div class="flex-1 flex items-center justify-center bg-gray-100 rounded-xl h-12 border border-gray-200 font-bold text-lg text-gray-800">
                            <?php echo "$num1 $operation_symbol $num2 = ?"; ?>
                        </div>
                        <input type="text" name="captcha" required
                               class="flex-[0.7] px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                               placeholder="Jawaban">
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Masukkan hasil dari perhitungan di atas</p>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    Masuk
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Demo Login: <strong>admin</strong> / <strong>admin123</strong></p>
            </div>
        </div>
    </div>

    <script>
        // Function to refresh CAPTCHA
        function refreshCaptcha() {
            location.reload();
        }
    </script>
</body>
</html>