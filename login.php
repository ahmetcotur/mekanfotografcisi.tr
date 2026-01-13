<?php
/**
 * Admin Login Page
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

$error = '';

if (isset($_SESSION['admin_user_id'])) {
    header('Location: /admin/');
    exit;
}

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #2563eb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-card {
            max-width: 400px;
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            width: 100%;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .p-8 {
            padding: 2rem;
        }

        .text-center {
            text-align: center;
        }

        .mb-8 {
            margin-bottom: 2rem;
        }

        .logo-box {
            width: 4rem;
            height: 4rem;
            background: var(--brand);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
        }

        .title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
            margin: 0;
        }

        .error-box {
            background: #fef2f2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border: 1px solid #fee2e2;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
        }

        .input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            outline: none;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-submit {
            width: 100%;
            background: var(--brand);
            color: white;
            font-weight: 700;
            padding: 1rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .footer-note {
            background: #f9fafb;
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid #f3f4f6;
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="login-card">
        <div class="p-8">
            <div class="text-center mb-8">
                <div class="logo-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                        <circle cx="12" cy="13" r="3" />
                    </svg>
                </div>
                <h1 class="title">Admin Panel Giriş</h1>
            </div>

            <?php if ($error): ?>
                <div class="error-box">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="label">E-posta</label>
                    <input type="email" name="email" required class="input">
                </div>

                <div class="form-group">
                    <label class="label">Şifre</label>
                    <input type="password" name="password" required class="input">
                </div>

                <button type="submit" class="btn-submit">
                    Giriş Yap
                </button>
            </form>
        </div>
        <div class="footer-note">
            &copy; <?= date('Y') ?> Mekan Fotoğrafçısı
        </div>
    </div>
</body>

</html>