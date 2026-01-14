<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<?php
// SEO Logic
$siteName = get_setting('site_title', 'Mekan Fotoğrafçısı');
$baseUrl = 'https://mekanfotografcisi.tr';
$currentUrl = $baseUrl . $_SERVER['REQUEST_URI'];

$seoTitle = $pageTitle ?? ($post->title ?? $siteName);
if ($seoTitle !== $siteName) {
    $seoTitle .= ' | ' . $siteName;
}

$seoDescription = $pageDescription ?? ($post->excerpt ?? get_setting('seo_default_desc', 'Antalya ve Muğla bölgesinde profesyonel mimari, iç mekan ve otel fotoğrafçılığı hizmetleri. Profesyonel ekipman ve yaratıcı bakış açısı ile mekanlarınızı en iyi şekilde yansıtıyoruz.'));

// Try to extract image from content if available
$seoImage = $baseUrl . '/assets/img/og-default.jpg';
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
?>

<head>
    <!-- Tailwind Console Warning Filter (must be at the top) -->
    <script>
        (function () {
            const originalWarn = console.warn;
            console.warn = function (...args) {
                if (args[0] && typeof args[0] === 'string' && (args[0].includes('tailwindcss') || args[0].includes('production'))) return;
                originalWarn.apply(console, args);
            };
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">

    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($seoTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl) ?>">
    <link rel="icon" href="<?= htmlspecialchars(get_setting('favicon_url', '/favicon.ico')) ?>">

    <!-- Open Graph -->
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($currentUrl) ?>">
    <meta property="og:site_name" content="<?= $siteName ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seoImage) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seoImage) ?>">

    <!-- Schema.org -->
    <script type="application/ld+json">
    <?= json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <?php
    $primaryColor = get_setting('primary_color', '#0ea5e9'); // Default Sky 500
    $secondaryColor = get_setting('secondary_color', '#0284c7'); // Default Sky 600
    ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', 'sans-serif'],
                        heading: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            400: '#38bdf8', // Added to fix invisible text
                            500: '<?= $primaryColor ?>',
                            600: '<?= $secondaryColor ?>',
                            700: '<?= $secondaryColor ?>', // Fallback
                            900: '#0c4a6e',
                        },
                        cyan: {
                            300: '#67e8f9', // Added for gradients
                        },
                        dark: {
                            900: '#0f172a',
                            800: '#1e293b',
                            50: '#f8fafc',
                        }
                    },
                    backgroundImage: {
                        'brand-gradient': 'linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%)',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-down': 'slideDown 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideDown: {
                            '0%': { transform: 'translateY(-10px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS Overrides -->
    <style>
        :root {
            --brand-primary:
                <?= $primaryColor ?>
            ;
            --brand-secondary:
                <?= $secondaryColor ?>
            ;
            --brand-gradient: linear-gradient(135deg,
                    <?= $primaryColor ?>
                    0%,
                    <?= $secondaryColor ?>
                    100%);
        }

        .bg-brand-gradient {
            background: var(--brand-gradient);
        }

        .text-brand-gradient {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Smooth scrolling adjustments */
        html {
            scroll-padding-top: 100px;
        }

        /* Glassmorphism utilities */
        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Mega menu transitions */
        .group:hover .mega-menu {
            display: block;
            animation: slideDown 0.2s ease-out forwards;
        }
    </style>

    <!-- Legacy Styles (keeping specific ones if needed, but prioritizing Tailwind) -->
    <link rel="stylesheet" href="/assets/css/styles.css?v=<?= time() ?>">
    <script src="/assets/js/gooey-text.js?v=<?= time() ?>"></script>

    <?php if (isset($schemaMarkup)): ?>
        <script type="application/ld+json">
                                                        <?= json_encode($schemaMarkup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
                                                                </script>
    <?php endif; ?>
</head>

<body class="font-sans text-slate-800 bg-white antialiased selection:bg-brand-100 selection:text-brand-900">

    <?php
    // Fetch dropdown services
    if (!isset($db)) {
        require_once __DIR__ . '/../includes/database.php';
        $db = new DatabaseClient();
    }
    $menuServices = $db->select('posts', ['post_type' => 'service', 'post_status' => 'publish', 'limit' => 5]);
    ?>

    <!-- Navigation -->
    <header class="fixed w-full top-0 z-50 transition-all duration-300 glass" id="main-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">

                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="group flex items-center gap-2">
                        <?php $logoUrl = get_setting('logo_url'); ?>
                        <?php if ($logoUrl): ?>
                            <img src="<?= e($logoUrl) ?>" alt="<?= e($siteName) ?>"
                                class="h-12 w-auto object-contain transition-transform group-hover:scale-105">
                        <?php else: ?>
                            <div
                                class="w-10 h-10 bg-brand-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-brand-500/30 transition-transform group-hover:scale-105 group-hover:rotate-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                                    <circle cx="12" cy="13" r="3" />
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="font-heading font-bold text-xl tracking-tight text-slate-900 leading-none">Mekan</span>
                                <span
                                    class="font-heading font-medium text-sm tracking-wide text-slate-500">Fotoğrafçısı</span>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex gap-8 items-center">
                    <a href="/" class="text-sm font-medium text-slate-600 hover:text-brand-600 transition-colors">Ana
                        Sayfa</a>

                    <!-- Dropdown Trigger -->
                    <div class="relative group py-6">
                        <button
                            class="flex items-center gap-1 text-sm font-medium text-slate-600 group-hover:text-brand-600 transition-colors">
                            Hizmetler
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="transition-transform group-hover:rotate-180">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <!-- Mega Menu -->
                        <div
                            class="mega-menu hidden absolute top-full left-1/2 -translate-x-1/2 w-[600px] bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-50">
                            <div class="grid grid-cols-2 gap-2">
                                <?php foreach ($menuServices as $service): ?>
                                    <a href="/<?= $service['slug'] ?>"
                                        class="flex items-start gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group/item">
                                        <div
                                            class="flex-shrink-0 w-10 h-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center group-hover/item:bg-brand-600 group-hover/item:text-white transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                                                <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                                                <path d="M18 9h4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4
                                                class="text-sm font-semibold text-slate-900 group-hover/item:text-brand-600 transition-colors line-clamp-1">
                                                <?= htmlspecialchars($service['title']) ?>
                                            </h4>
                                            <p class="text-xs text-slate-500 line-clamp-2 mt-0.5 leading-relaxed">
                                                <?= htmlspecialchars(substr($service['excerpt'] ?? 'Hizmet detaylarını inceleyin.', 0, 80)) ?>
                                            </p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                                <a href="/hizmetlerimiz"
                                    class="col-span-2 flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors group/link mt-2">
                                    <span class="text-sm font-medium text-slate-700 group-hover/link:text-slate-900">Tüm
                                        Hizmetleri Görüntüle</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="text-slate-400 group-hover/link:text-slate-600">
                                        <path d="M5 12h14" />
                                        <path d="m12 5 7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="/portfolio"
                        class="text-sm font-medium text-slate-600 hover:text-brand-600 transition-colors">Portfolyo</a>
                    <a href="/hizmet-bolgeleri"
                        class="text-sm font-medium text-slate-600 hover:text-brand-600 transition-colors">Bölgeler</a>
                </nav>

                <!-- CTA Button -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="/#iletisim"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-brand-600 px-6 text-sm font-semibold text-white shadow-lg shadow-brand-500/20 transition-all hover:bg-brand-700 hover:scale-105 hover:shadow-brand-500/40 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        Teklif Al
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn"
                    class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" x2="20" y1="12" y2="12" />
                        <line x1="4" x2="20" y1="6" y2="6" />
                        <line x1="4" x2="20" y1="18" y2="18" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu"
            class="hidden md:hidden absolute top-full left-0 w-full bg-white border-b border-slate-100 shadow-xl overflow-hidden transition-all">
            <div class="p-4 space-y-2">
                <a href="/"
                    class="block px-4 py-3 text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-brand-600 rounded-lg">Ana
                    Sayfa</a>
                <a href="/hizmetlerimiz"
                    class="block px-4 py-3 text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-brand-600 rounded-lg">Hizmetler</a>
                <a href="/portfolio"
                    class="block px-4 py-3 text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-brand-600 rounded-lg">Portfolyo</a>
                <a href="/hizmet-bolgeleri"
                    class="block px-4 py-3 text-base font-medium text-slate-600 hover:bg-slate-50 hover:text-brand-600 rounded-lg">Bölgeler</a>
                <a href="/#iletisim"
                    class="block px-4 py-3 text-base font-medium text-brand-600 bg-brand-50 rounded-lg mt-4 text-center">İletişime
                    Geç</a>
            </div>
        </div>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-20"></div>

    <script>
        // Mobile menu toggle
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>