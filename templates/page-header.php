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

    ?>

    <!-- Navigation -->
    <header class="fixed w-full top-0 z-[100] transition-all duration-500" id="main-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="relative glass-panel rounded-3xl px-6 md:px-10 h-20 flex justify-between items-center transition-all duration-500 border-white/40 shadow-xl shadow-slate-900/5"
                id="header-inner">

                <!-- Logo -->
                <div class="flex-shrink-0 absolute left-1/2 -translate-x-1/2 md:static md:translate-x-0 md:left-auto">
                    <a href="/"
                        class="group flex items-center gap-4 transition-all duration-500 hover:scale-105 active:scale-95"
                        id="header-logo">
                        <?php $logoUrl = get_setting('logo_url'); ?>
                        <?php if ($logoUrl): ?>
                            <img src="<?= e($logoUrl) ?>" alt="<?= e($siteName) ?>"
                                class="h-16 md:h-14 w-auto object-contain transition-all duration-500 group-hover:drop-shadow-[0_0_15px_rgba(var(--brand-rgb),0.3)] animate-float">
                        <?php else: ?>
                            <div
                                class="w-14 h-14 bg-brand-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-brand-500/30 transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 group-hover:shadow-brand-500/50 animate-float">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                                    <circle cx="12" cy="13" r="3" />
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span
                                    class="font-heading font-black text-2xl tracking-tight text-slate-900 leading-none group-hover:text-brand-600 transition-colors">Mekan</span>
                                <span
                                    class="font-heading font-bold text-[11px] uppercase tracking-[0.2em] text-slate-400 group-hover:text-slate-600 transition-colors">Fotoğrafçısı</span>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>

                <style>
                    @keyframes float {

                        0%,
                        100% {
                            transform: translateY(0);
                        }

                        50% {
                            transform: translateY(-5px);
                        }
                    }

                    .animate-float {
                        animation: float 4s ease-in-out infinite;
                    }

                    @keyframes shake {

                        0%,
                        90%,
                        100% {
                            transform: translateX(0);
                        }

                        91%,
                        93%,
                        95%,
                        97%,
                        99% {
                            transform: translateX(-2px);
                        }

                        92%,
                        94%,
                        96%,
                        98% {
                            transform: translateX(2px);
                        }
                    }

                    .animate-shake {
                        animation: shake 5s ease-in-out infinite;
                    }

                    :root {
                        --brand-rgb: 37, 99, 235;
                        /* Blue 600 default, should be dynamic if possible */
                    }
                </style>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex gap-1 items-center">
                    <a href="/"
                        class="px-5 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-all rounded-full hover:bg-brand-50 items-center gap-1.5 <?= ($_SERVER['REQUEST_URI'] == '/') ? 'text-brand-600 bg-brand-50' : '' ?>">Ana
                        Sayfa</a>

                    <a href="/hizmetlerimiz"
                        class="px-5 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-all rounded-full hover:bg-brand-50 <?= ($_SERVER['REQUEST_URI'] == '/hizmetlerimiz') ? 'text-brand-600 bg-brand-50' : '' ?>">Hizmetler</a>

                    <a href="/nasil-calisir"
                        class="px-5 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-all rounded-full hover:bg-brand-50 <?= ($_SERVER['REQUEST_URI'] == '/nasil-calisir') ? 'text-brand-600 bg-brand-50' : '' ?>">Nasıl
                        Çalışır?</a>

                    <a href="/portfolio"
                        class="px-5 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-all rounded-full hover:bg-brand-50">Portfolyo</a>
                    <a href="/hizmet-bolgeleri"
                        class="px-5 py-2 text-sm font-bold text-slate-600 hover:text-brand-600 transition-all rounded-full hover:bg-brand-50">Bölgeler</a>
                </nav>

                <div class="hidden md:flex items-center gap-4">
                    <button onclick="openQuoteWizard()"
                        class="inline-flex h-12 items-center justify-center rounded-2xl bg-brand-600 px-8 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-brand-500/25 transition-all hover:bg-brand-700 hover:scale-105 active:scale-95 animate-shake">
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
            class="hidden md:hidden absolute top-[calc(100%-1rem)] left-4 right-4 bg-white/95 backdrop-blur-xl rounded-4xl shadow-2xl border border-slate-100 p-4 transition-all duration-300 origin-top animate-slide-up max-h-[85vh] overflow-y-auto">
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

                <a href="/nasil-calisir"
                    class="flex items-center gap-4 px-6 py-4 text-base font-bold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-3xl transition-all group">
                    <div
                        class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-brand-200/50 group-hover:text-brand-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    Nasıl Çalışır?
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
                        class="w-full py-5 text-sm font-black uppercase tracking-widest text-white bg-brand-600 rounded-3xl text-center active:scale-95 transition-all shadow-xl shadow-brand-500/30 hover:bg-brand-700 animate-shake">Teklif
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
            if (window.scrollY > 50) {
                inner.classList.add('md:rounded-full', 'shadow-2xl');
                inner.classList.remove('rounded-3xl', 'shadow-slate-900/5');
                header.querySelector('.max-w-7xl').style.marginTop = '0';
            } else {
                inner.classList.remove('md:rounded-full', 'shadow-2xl');
                inner.classList.add('rounded-3xl', 'shadow-slate-900/5');
                header.querySelector('.max-w-7xl').style.marginTop = '1rem';
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