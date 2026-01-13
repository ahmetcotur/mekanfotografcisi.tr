<?php
/**
 * Admin Login Page
 */
session_start();
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Lütfen tüm alanları doldurunuz.';
    } else {
        $db = new DatabaseClient();
        $users = $db->select('admin_users', [
            'email.eq' => $email,
            'is_active' => true
        ]);

        if (!empty($users)) {
            $user = $users[0];
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_user_email'] = $user['email'];
                $_SESSION['admin_user_name'] = $user['name'];

                // For compatibility with some older parts of the code
                $_SESSION['user_id'] = $user['id'];

                header('Location: /admin/');
                exit;
            } else {
                $error = 'Hatalı e-posta veya şifre.';
            }
        } else {
            $error = 'Kullanıcı bulunamadı veya pasif.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap | Mekan Fotoğrafçısı Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-8">
                <div
                    class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-white shadow-lg shadow-blue-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                        <circle cx="12" cy="13" r="3" />
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight text-center">Admin Panel Giriş</h1>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-medium border border-red-100">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">E-posta</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Şifre</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/30 transition-all transform hover:-translate-y-0.5 active:scale-95">
                    Giriş Yap
                </button>
            </form>
        </div>
        <div class="bg-gray-50 p-6 text-center border-t border-gray-100">
            <p class="text-gray-400 text-xs font-medium uppercase tracking-widest">&copy;
                <?= date('Y') ?> Mekan Fotoğrafçısı
            </p>
        </div>
    </div>
</body>

</html>