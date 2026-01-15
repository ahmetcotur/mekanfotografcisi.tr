<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<?php
// SEO Logic
require_once __DIR__ . '/../includes/helpers.php';
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
    // Check for custom overrides if they exist
    $customColor = get_setting('customcolor');
    if (!empty($customColor)) {
        $primaryColor = $customColor;
        // Simple darkening for secondary if not set? For now just use primary as base if custom is set
        // But better to stick to primary/secondary if available
    }
    ?>
    <script>
        window.tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Inter"', 'sans-serif'],
                        heading: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '<?= $primaryColor ?>10', // 10% opacity
                            100: '<?= $primaryColor ?>20',
                            200: '<?= $primaryColor ?>40',
                            300: '<?= $primaryColor ?>60',
                            400: '<?= $primaryColor ?>80',
                            500: '<?= $primaryColor ?>',
                            600: '<?= $secondaryColor ?>',
                            700: '<?= $secondaryColor ?>', // Fallback
                            800: '<?= $secondaryColor ?>',
                            900: '#0c4a6e', // Keep dark slate/navy for contrast textuals if needed, or derived? Keep standard for now.
                            950: '#082f49',
                        },
                        accent: {
                            50: '#ecfdf5',
                            500: '#10b981',
                            600: '#059669',
                        }
                    },
                    borderRadius: {
                        '3xl': '1.5rem',
                        '4xl': '2rem',
                        '5xl': '2.5rem',
                    },
                    backgroundImage: {
                        'brand-gradient': 'linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%)',
                        'glass-gradient': 'linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.4))',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-subtle': 'pulseSubtle 3s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        pulseSubtle: {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.8' },
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

        /* Modern Glassmorphism Utilities */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .glass-dark {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .text-gradient {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Smooth interactions */
        .hover-lift {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.1);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Optimized images */
        img {
            content-visibility: auto;
        }
    </style>

    <!-- Legacy Styles (keeping specific ones if needed, but prioritizing Tailwind) -->
    <link rel="stylesheet" href="/assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/assets/css/prose-styles.css?v=<?= time() ?>">
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
    <header class="fixed w-full top-0 z-[100] transition-all duration-500" id="main-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="relative glass-panel rounded-3xl px-6 md:px-10 h-20 flex justify-between items-center transition-all duration-500 border-white/40 shadow-xl shadow-slate-900/5"
                id="header-inner">

                <!-- Logo -->
                <div class="flex-shrink-0 absolute left-1/2 -translate-x-1/2 md:static md:translate-x-0 md:left-auto">
                    <a href="/" class="group flex items-center gap-3">
                        <?php $logoUrl = get_setting('logo_url'); ?>
                        <?php if ($logoUrl): ?>
                            <img src="<?= e($logoUrl) ?>" alt="<?= e($siteName) ?>"
                                class="h-12 md:h-10 w-auto object-contain transition-transform group-hover:scale-105">
                        <?php else: ?>
                            <div
                                class="w-10 h-10 bg-brand-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-brand-500/30 transition-all group-hover:scale-110 group-hover:rotate-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                                    <circle cx="12" cy="13" r="3" />
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="font-heading font-black text-xl tracking-tight text-slate-900 leading-none group-hover:text-brand-600 transition-colors">Mekan</span>
                                <span
                                    class="font-heading font-bold text-[10px] uppercase tracking-widest text-slate-400 group-hover:text-slate-600 transition-colors">Fotoğrafçısı</span>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex gap-1 items-center">
                    <a href="/"
                        class="px-5 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-all rounded-full hover:bg-brand-50 items-center gap-1.5 <?= ($_SERVER['REQUEST_URI'] == '/') ? 'text-brand-600 bg-brand-50' : '' ?>">Ana
                        Sayfa</a>

                    <!-- Dropdown Trigger -->
                    <div class="relative group py-2">
                        <button
                            class="flex items-center gap-1.5 px-5 py-2 text-sm font-bold text-slate-600 group-hover:text-brand-600 transition-all rounded-full group-hover:bg-brand-50">
                            Hizmetler
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                stroke-linejoin="round" class="transition-transform group-hover:rotate-180">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <!-- Mega Menu -->
                        <div
                            class="mega-menu hidden group-hover:block absolute top-[calc(100%+0.5rem)] left-1/2 -translate-x-1/2 w-[640px] bg-white/95 backdrop-blur-xl rounded-4xl shadow-2xl border border-slate-100 p-3 z-50 origin-top">
                            <div class="grid grid-cols-2 gap-2">
                                <?php foreach ($menuServices as $service): ?>
                                    <a href="/<?= $service['slug'] ?>"
                                        class="flex items-start gap-4 p-4 rounded-3xl hover:bg-brand-50/50 transition-all group/item">
                                        <div
                                            class="flex-shrink-0 w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center group-hover/item:bg-brand-600 group-hover/item:text-white transition-all shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M15 8h.01" />
                                                <rect width="16" height="13" x="4" y="5" rx="2" />
                                                <path d="m4 15 3-3 3 3 5-5 5 5" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4
                                                class="text-sm font-bold text-slate-900 group-hover/item:text-brand-600 transition-colors line-clamp-1">
                                                <?= htmlspecialchars($service['title']) ?>
                                            </h4>
                                            <p class="text-slate-400 text-xs font-medium leading-relaxed mt-1 line-clamp-2">
                                                <?= htmlspecialchars(substr(strip_tags($service['content']), 0, 80)) ?>
                                            </p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                                <a href="/hizmetlerimiz"
                                    class="col-span-2 flex items-center justify-between p-4 rounded-3xl bg-slate-50 hover:bg-brand-600 hover:text-white transition-all group/link mt-2">
                                    <span class="text-xs font-black uppercase tracking-widest">Tüm Hizmetleri
                                        Keşfet</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="transition-transform group-hover/link:translate-x-2">
                                        <path d="M5 12h14" />
                                        <path d="m12 5 7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="/portfolio"
                        class="px-5 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-all rounded-full hover:bg-brand-50">Portfolyo</a>
                    <a href="/hizmet-bolgeleri"
                        class="px-5 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-all rounded-full hover:bg-brand-50">Bölgeler</a>
                </nav>

                <div class="hidden md:flex items-center gap-4">
                    <button onclick="openQuoteWizard()"
                        class="inline-flex h-12 items-center justify-center rounded-2xl bg-brand-600 px-8 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-brand-500/25 transition-all hover:bg-brand-700 hover:scale-105 active:scale-95">
                        Teklif Al
                    </button>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn"
                    class="md:hidden ml-auto w-12 h-12 flex items-center justify-center bg-slate-50 text-slate-600 rounded-2xl transition-all active:scale-90 shadow-sm border border-slate-100 hover:bg-brand-50 hover:text-brand-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" x2="20" y1="12" y2="12" />
                        <line x1="4" x2="20" y1="6" y2="6" />
                        <line x1="4" x2="20" y1="18" y2="18" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu"
            class="hidden md:hidden absolute top-[calc(100%-1rem)] left-4 right-4 bg-white/95 backdrop-blur-xl rounded-4xl shadow-2xl border border-slate-100 p-4 transition-all duration-300 origin-top animate-slide-up">
            <div class="space-y-2">
                <a href="/"
                    class="flex items-center gap-4 px-6 py-4 text-base font-bold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-3xl transition-all group">
                    <div
                        class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-brand-200/50 group-hover:text-brand-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                    </div>
                    Ana Sayfa
                </a>
                <a href="/hizmetlerimiz"
                    class="flex items-center gap-4 px-6 py-4 text-base font-bold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-3xl transition-all group">
                    <div
                        class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-brand-200/50 group-hover:text-brand-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="7" height="7" x="3" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="14" rx="1" />
                            <rect width="7" height="7" x="3" y="14" rx="1" />
                        </svg>
                    </div>
                    Hizmetlerimiz
                </a>
                <a href="/portfolio"
                    class="flex items-center gap-4 px-6 py-4 text-base font-bold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-3xl transition-all group">
                    <div
                        class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-brand-200/50 group-hover:text-brand-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                        </svg>
                    </div>
                    Portfolyo
                </a>
                <a href="/hizmet-bolgeleri"
                    class="flex items-center gap-4 px-6 py-4 text-base font-bold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-3xl transition-all group">
                    <div
                        class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-brand-200/50 group-hover:text-brand-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="10" r="3" />
                            <path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 7 8 11.7z" />
                        </svg>
                    </div>
                    Bölgeler
                </a>
                <div class="pt-6 px-2">
                    <button onclick="openQuoteWizard()"
                        class="w-full py-5 text-sm font-black uppercase tracking-widest text-white bg-brand-600 rounded-3xl text-center active:scale-95 transition-all shadow-xl shadow-brand-500/30 hover:bg-brand-700">Teklif
                        Al</button>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.getElementById('main-header');
            const inner = document.getElementById('header-inner');
            if (window.scrollY > 20) {
                inner.classList.remove('rounded-3xl', 'mt-4');
                inner.classList.add('rounded-none', 'md:rounded-full', 'mt-0', 'shadow-2xl');
                header.querySelector('.max-w-7xl').classList.remove('mt-4', 'px-4');
                header.querySelector('.max-w-7xl').classList.add('px-0');
            } else {
                inner.classList.add('rounded-3xl');
                inner.classList.remove('rounded-none', 'md:rounded-full', 'shadow-2xl');
                header.querySelector('.max-w-7xl').classList.add('mt-4', 'px-4');
                header.querySelector('.max-w-7xl').classList.remove('px-0');
            }
        });
    </script>

    <script>
        // Mobile menu toggle
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>