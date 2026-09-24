<!DOCTYPE html>
<html lang="tr">
<?php
// SEO Logic
require_once __DIR__ . '/../includes/helpers.php';
$siteName = get_setting('site_title', 'Mekan Fotoğrafçısı');
$baseUrl = 'https://mekanfotografcisi.tr';
$currentUrl = $baseUrl . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

$seoTitle = $pageTitle ?? ($post->title ?? $siteName);
if ($seoTitle !== $siteName) {
    $seoTitle .= ' | ' . $siteName;
}

$seoDescription = $pageDescription ?? ($post->excerpt ?? get_setting('seo_default_desc', 'Mekanını çektirmek isteyenleri bölgesine ve ihtiyacına uygun profesyonel fotoğrafçılarla buluşturan platform.'));

// Per-page override for the robots meta tag (e.g. 'noindex, follow' on
// dashboard/login/registration pages). Defaults to indexable.
$pageRobots = $pageRobots ?? 'index, follow';

// App-like pages (login, registration, dashboards) set this to hide the
// marketing chrome (mobile quote bar, big footer).
$appPage = $appPage ?? ($pageRobots !== 'index, follow');

// Try to extract image from content if available
$seoImage = $baseUrl . '/assets/images/hero-bg.jpg';
if (isset($post) && !empty($post->content)) {
    if (preg_match('/src="([^"]+)"/', $post->content, $matches)) {
        $seoImage = $matches[1];
    }
}

// Schema.org LocalBusiness
$schema = [
    "@context" => "https://schema.org",
    "@type" => "LocalBusiness",
    "name" => $siteName,
    "image" => $seoImage,
    "url" => $baseUrl,
    "telephone" => get_setting('phone', '+905074677502'),
    "email" => get_setting('email', 'info@mekanfotografcisi.tr'),
    "address" => [
        "@type" => "PostalAddress",
        "streetAddress" => "Kalkan Mah. Şehitler Cad. no 7",
        "addressLocality" => "Kaş",
        "addressRegion" => "Antalya",
        "addressCountry" => "TR"
    ],
    "geo" => [
        "@type" => "GeoCoordinates",
        "latitude" => 36.2667,
        "longitude" => 29.4167
    ],
    "openingHoursSpecification" => [
        [
            "@type" => "OpeningHoursSpecification",
            "dayOfWeek" => ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
            "opens" => "09:00",
            "closes" => "18:00"
        ]
    ],
    "priceRange" => "$$"
];

$navItems = [
    ['href' => '/fotografcilar', 'label' => 'Fotoğrafçılar', 'prefix' => true],
    ['href' => '/hizmetlerimiz', 'label' => 'Hizmetler', 'prefix' => true],
    ['href' => '/hizmet-bolgeleri', 'label' => 'Bölgeler', 'prefix' => true],
    ['href' => '/nasil-calisir', 'label' => 'Nasıl Çalışır?', 'prefix' => false],
    ['href' => '/portfolio', 'label' => 'Portfolyo', 'prefix' => false],
    ['href' => '/blog', 'label' => 'Blog', 'prefix' => true],
];
$logoUrl = get_setting('logo_url');
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="<?= e($pageRobots) ?>">
    <meta name="theme-color" content="#faf8f5">

    <!-- SEO Meta Tags -->
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="<?= e($seoDescription) ?>">
    <link rel="canonical" href="<?= e($currentUrl) ?>">
    <link rel="icon" href="<?= e(get_setting('favicon_url', '/favicon.ico')) ?>">

    <!-- Open Graph -->
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($seoTitle) ?>">
    <meta property="og:description" content="<?= e($seoDescription) ?>">
    <meta property="og:url" content="<?= e($currentUrl) ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:image" content="<?= e($seoImage) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seoTitle) ?>">
    <meta name="twitter:description" content="<?= e($seoDescription) ?>">
    <meta name="twitter:image" content="<?= e($seoImage) ?>">

    <!-- Schema.org -->
    <script type="application/ld+json">
    <?= json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
    </script>
    <?php if (isset($schemaMarkup)): ?>
        <script type="application/ld+json"><?= json_encode($schemaMarkup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap">

    <!-- Prebuilt Tailwind (npm run build:css) + accent colour from settings -->
    <link rel="stylesheet" href="<?= asset_url('assets/css/app.css') ?>">
    <style><?= brand_palette_css() ?></style>
</head>

<body class="flex min-h-screen flex-col<?= $appPage ? '' : ' pb-20 md:pb-0' ?>">
    <a href="#main"
        class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[300] focus:rounded-full focus:bg-ink focus:px-4 focus:py-2 focus:text-white">İçeriğe
        geç</a>

    <header id="site-header" class="sticky top-0 z-[100] border-b border-transparent bg-paper/90 backdrop-blur transition-colors">
        <div class="container-page flex h-[var(--header-h)] items-center gap-6">
            <a href="/" class="flex shrink-0 items-center gap-2.5" aria-label="<?= e($siteName) ?> — Ana sayfa">
                <?php if ($logoUrl): ?>
                    <img src="<?= e($logoUrl) ?>" alt="<?= e($siteName) ?>" class="h-9 w-auto md:h-10" width="160" height="40">
                <?php else: ?>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-ink text-white">
                        <?= icon('camera', 'h-5 w-5') ?>
                    </span>
                    <span class="font-display text-lg font-semibold leading-none">Mekan<span class="text-brand-600">Fotoğrafçısı</span></span>
                <?php endif; ?>
            </a>

            <nav class="hidden flex-1 items-center justify-center gap-1 lg:flex" aria-label="Ana menü">
                <?php foreach ($navItems as $item): ?>
                    <a href="<?= $item['href'] ?>" class="nav-link" <?= is_current_path($item['href'], $item['prefix']) ? 'aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
                <?php endforeach; ?>
            </nav>

            <div class="ml-auto hidden items-center gap-2 lg:flex">
                <a href="/kayit/fotografci" class="btn btn-ghost" data-auth="guest">Fotoğrafçı mısın?</a>
                <a href="/giris" class="btn btn-ghost" data-auth="guest">Giriş</a>
                <a href="/panel" class="btn btn-ghost" data-auth="user" hidden><?= icon('user', 'h-4 w-4') ?> Panelim</a>
                <button type="button" onclick="openQuoteWizard()" class="btn btn-primary">Teklif Al</button>
            </div>

            <button type="button" id="mobile-menu-btn"
                class="ml-auto inline-flex h-10 w-10 items-center justify-center rounded-full text-ink hover:bg-stone-100 lg:hidden"
                aria-controls="mobile-menu" aria-expanded="false" aria-label="Menüyü aç">
                <span data-icon="open"><?= icon('menu', 'h-6 w-6') ?></span>
                <span data-icon="close" hidden><?= icon('x', 'h-6 w-6') ?></span>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="border-t border-line bg-paper lg:hidden" hidden>
            <nav class="container-page flex h-[calc(100dvh-var(--header-h))] flex-col overflow-y-auto py-4" aria-label="Mobil menü">
                <?php foreach ($navItems as $item): ?>
                    <a href="<?= $item['href'] ?>"
                        class="flex items-center justify-between border-b border-line py-3.5 text-base font-medium <?= is_current_path($item['href'], $item['prefix']) ? 'text-brand-700' : 'text-ink' ?>">
                        <?= e($item['label']) ?> <?= icon('chevron-right', 'h-4 w-4 text-ink-muted') ?>
                    </a>
                <?php endforeach; ?>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <a href="/giris" class="btn btn-outline" data-auth="guest">Giriş Yap</a>
                    <a href="/panel" class="btn btn-outline" data-auth="user" hidden>Panelim</a>
                    <a href="/kayit/fotografci" class="btn btn-outline">Fotoğrafçı Ol</a>
                    <button type="button" onclick="openQuoteWizard()" class="btn btn-primary col-span-2 py-3">Ücretsiz Teklif Al</button>
                </div>
            </nav>
        </div>
    </header>

    <script>
        (function () {
            var header = document.getElementById('site-header');
            var btn = document.getElementById('mobile-menu-btn');
            var menu = document.getElementById('mobile-menu');

            function onScroll() {
                header.classList.toggle('border-line', window.scrollY > 8);
                header.classList.toggle('border-transparent', window.scrollY <= 8);
            }
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            function setMenu(open) {
                menu.hidden = !open;
                btn.setAttribute('aria-expanded', String(open));
                btn.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
                btn.querySelector('[data-icon="open"]').hidden = open;
                btn.querySelector('[data-icon="close"]').hidden = !open;
                document.documentElement.classList.toggle('overflow-hidden', open);
            }
            btn.addEventListener('click', function () { setMenu(menu.hidden); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !menu.hidden) setMenu(false); });

            // Logged-in freelancers/clients (JWT in localStorage) get a link to
            // their own dashboard instead of the guest login/register links.
            var role = null;
            try { role = localStorage.getItem('mf_role'); } catch (e) { }
            if (role === 'freelancer' || role === 'client') {
                document.querySelectorAll('[data-auth="guest"]').forEach(function (el) { el.hidden = true; });
                document.querySelectorAll('[data-auth="user"]').forEach(function (el) {
                    el.hidden = false;
                    el.setAttribute('href', role === 'client' ? '/musteri' : '/panel');
                });
            }
        })();
    </script>
