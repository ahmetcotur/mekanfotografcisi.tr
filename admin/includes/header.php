<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        (function () {
            const originalWarn = console.warn;
            console.warn = function (...args) {
                if (args[0] && typeof args[0] === 'string' && (args[0].includes('tailwindcss') || args[0].includes('production'))) return;
                originalWarn.apply(console, args);
            };
        })();
    </script>
    <title>
        <?= $page_title ?> - Mekan Fotoğrafçısı Admin
    </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-header">
            <div class="header-left">
                <!-- Breadcrumbs or mobile toggle -->
            </div>
            <div class="header-right">
                <div class="user-menu">
                    <span class="user-name">Admin User</span>
                    <a href="/logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                </div>
            </div>
        </header>

        <div class="page-content">
            <div class="page-header">
                <h1 class="page-title">
                    <?= $page_title ?>
                </h1>
                <div class="page-actions">
                    <!-- Actions injected by view -->
                </div>
            </div>