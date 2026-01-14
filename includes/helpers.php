<?php
/**
 * Helper Functions
 * Security and utility functions
 */

/**
 * Escape output for HTML context (XSS protection)
 */
function e($string)
{
    if ($string === null || $string === '') {
        return '';
    }
    return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get a setting value by key
 */
function get_setting($key, $default = '')
{
    global $db;
    if (!$db) {
        $db = new DatabaseClient();
    }

    static $settings_cache = null;
    if ($settings_cache === null) {
        $rows = $db->query("SELECT \"key\", \"value\" FROM settings");
        $settings_cache = [];
        foreach ($rows as $row) {
            $settings_cache[$row['key']] = $row['value'];
        }
    }

    return $settings_cache[$key] ?? $default;
}

/**
 * Validate and sanitize slug input
 */
function sanitizeSlug($input)
{
    return preg_replace('/[^a-z0-9-]/', '', strtolower(trim($input)));
}

/**
 * Convert string to permalink (slug)
 * Handles Turkish characters
 */
function to_permalink($str)
{
    $str = mb_strtolower($str, 'UTF-8');
    $str = str_replace(
        ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', ' '],
        ['i', 'g', 'u', 's', 'o', 'c', '-'],
        $str
    );
    $str = preg_replace('/[^a-z0-9-]/', '', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}

/**
 * Validate email
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize string input
 */
function sanitizeString($input)
{
    // FILTER_SANITIZE_STRING is deprecated in PHP 8.1+, use strip_tags instead
    if (PHP_VERSION_ID >= 80100) {
        return strip_tags(trim($input));
    }
    return filter_var(trim($input), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
}

/**
 * Get a random photo from Pexels profile
 */
function get_random_pexels_photo()
{
    static $pexelsService = null;
    if ($pexelsService === null) {
        $pexelsService = new \Core\PexelsService();
    }
    $photo = $pexelsService->getRandomPhoto();

    // Sanitize for template compatibility (ensure src is a string)
    if ($photo && isset($photo['src'])) {
        if (is_array($photo['src'])) {
            $photo['src'] = $photo['src']['large'] ?? $photo['src']['original'] ?? '';
        }
    }

    return $photo;
}

/**
 * Get a batch of random photos from Pexels
 */
function get_random_pexels_photos($count = 3)
{
    static $pexelsService = null;
    if ($pexelsService === null) {
        $pexelsService = new \Core\PexelsService();
    }
    $photos = $pexelsService->getRandomPhotosBatch($count);

    // Sanitize for template compatibility
    if (!empty($photos)) {
        foreach ($photos as &$photo) {
            if (isset($photo['src']) && is_array($photo['src'])) {
                $photo['src'] = $photo['src']['large'] ?? $photo['src']['original'] ?? '';
            }
        }
    }

    return $photos;
}

/**
 * Render a small gallery of photos
 */
function render_pexels_gallery($photos, $title = 'Örnek Çalışmalarımız')
{
    if (empty($photos))
        return '';

    ob_start();
    ?>
    <div class="in-content-gallery-wrapper">
        <h3 class="gallery-title"><?= e($title) ?></h3>
        <div class="in-content-gallery grid-<?= count($photos) ?>">
            <?php foreach ($photos as $photo): ?>
                <div class="gallery-item">
                    <a href="<?= e($photo['url']) ?>" target="_blank" rel="noopener">
                        <img src="<?= e($photo['src']) ?>" alt="<?= e($photo['alt']) ?>" loading="lazy">
                        <div class="gallery-overlay">
                            <span>Pexels'da Görüntüle</span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render a gallery from the media manager folder
 */
function render_media_gallery($folder_id, $title = '')
{
    global $db;
    if (!$db)
        $db = new \DatabaseClient();

    $files = $db->query("SELECT * FROM media WHERE folder_id = ? ORDER BY created_at ASC", [$folder_id]);

    if (empty($files))
        return '';

    ob_start();
    ?>
    <section class="portfolio-gallery py-24 bg-white">
        <?php if ($title): ?>
            <div class="container mx-auto px-4 mb-24 text-center">
                <div class="inline-flex items-center gap-4 mb-8">
                    <span class="h-px w-12 bg-brand-500/30"></span>
                    <span class="text-brand-600 font-bold tracking-[0.3em] text-[11px] uppercase italic">Portfolyo
                        Seçkisi</span>
                    <span class="h-px w-12 bg-brand-500/30"></span>
                </div>
                <h2 class="text-5xl md:text-7xl font-heading font-black text-slate-900 tracking-tight leading-tight mb-8">
                    <?= e($title) ?>
                </h2>
                <div class="h-1 w-20 bg-brand-600 mx-auto rounded-full"></div>
            </div>
        <?php endif; ?>

        <div class="max-w-[1600px] mx-auto px-4 md:px-8">
            <!-- True Masonry Layout using CSS Columns -->
            <div class="columns-1 md:columns-2 lg:columns-3 gap-8 space-y-8">
                <?php foreach ($files as $index => $file): ?>
                    <div
                        class="break-inside-avoid group relative overflow-hidden bg-slate-100 rounded-3xl shadow-xl transition-all duration-700 hover:shadow-2xl hover:-translate-y-2 border border-slate-100">
                        <a href="<?= e($file['public_url']) ?>" class="block w-full h-full lightbox-trigger"
                            onclick="event.preventDefault(); openLightbox('<?= e($file['public_url']) ?>', <?= $index ?>)">

                            <div class="relative w-full overflow-hidden">
                                <img src="<?= e($file['public_url']) ?>" alt="<?= e($file['alt'] ?: 'Profesyonel Çekim') ?>"
                                    class="w-full h-auto object-cover transition-transform duration-1000 group-hover:scale-105"
                                    loading="lazy">

                                <!-- Ultra Premium Interaction Overlay -->
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500 z-10 flex flex-col justify-end p-10">
                                    <div
                                        class="transform translate-y-8 group-hover:translate-y-0 transition-transform duration-500">
                                        <span
                                            class="inline-block px-4 py-1 bg-brand-600 text-white text-[10px] font-bold uppercase tracking-[3px] rounded-full mb-4">Referans</span>
                                        <h3 class="text-white text-2xl font-bold font-heading mb-2 leading-tight">
                                            <?= e($file['alt'] ?: 'Çekim Detayı') ?>
                                        </h3>
                                        <p class="text-slate-300 text-sm font-light tracking-wide">Mekan ve Mimari
                                            Fotoğrafçılığı</p>
                                    </div>
                                </div>

                                <!-- Magnifier Icon -->
                                <div
                                    class="absolute top-10 right-10 w-14 h-14 bg-white/10 backdrop-blur-2xl border border-white/20 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-700 delay-100 transform scale-50 group-hover:scale-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- High-End Lightbox -->
        <div id="gallery-lightbox" onclick="if(event.target === this) closeLightbox()"
            class="fixed inset-0 z-[9999] bg-slate-950/98 backdrop-blur-xl hidden flex items-center justify-center animate-in fade-in duration-500 cursor-zoom-out">
            <!-- Top Bar -->
            <div class="absolute top-0 inset-x-0 p-6 md:p-8 flex items-center justify-between z-10 pointer-events-none">
                <div class="flex items-center gap-4">
                    <div
                        class="w-10 h-10 bg-brand-600 rounded-lg flex items-center justify-center text-white font-bold text-xs uppercase tracking-tighter shadow-lg shadow-brand-600/30">
                        MF</div>
                    <div class="text-white/40 text-[10px] font-bold tracking-[0.4em] uppercase hidden sm:block">
                        <span id="lb-current" class="text-white">01</span> / <span id="lb-total">08</span>
                    </div>
                </div>
                <button onclick="closeLightbox()"
                    class="group flex items-center gap-3 text-white transition-all capitalize text-sm font-bold pointer-events-auto bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full border border-white/20 backdrop-blur-md">
                    <span>Kapat</span>
                    <div
                        class="w-8 h-8 flex items-center justify-center bg-white/10 group-hover:bg-brand-500 rounded-full transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </button>
            </div>

            <!-- Main Content Area -->
            <div class="lb-wrapper relative w-full h-full flex items-center justify-center p-4 md:p-20 overflow-hidden pointer-events-none"
                onclick="if(event.target === this) closeLightbox()">
                <img id="lb-img" src=""
                    class="max-w-full max-h-[85vh] object-contain shadow-[0_0_100px_rgba(0,0,0,0.8)] transition-all duration-700 scale-95 opacity-0 pointer-events-auto cursor-default">

                <!-- Large Nav Buttons -->
                <button id="lb-prev"
                    class="absolute left-10 top-1/2 -translate-y-1/2 w-20 h-20 hidden lg:flex items-center justify-center text-white/20 hover:text-white transition-all bg-white/0 hover:bg-white/5 rounded-full group pointer-events-auto">
                    <div class="transform group-hover:-translate-x-1 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </div>
                </button>
                <button id="lb-next"
                    class="absolute right-10 top-1/2 -translate-y-1/2 w-20 h-20 hidden lg:flex items-center justify-center text-white/20 hover:text-white transition-all bg-white/0 hover:bg-white/5 rounded-full group pointer-events-auto">
                    <div class="transform group-hover:translate-x-1 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </button>
            </div>
        </div>

        <script>
            let currentIdx = 0;
            const images = [<?php foreach ($files as $f)
                echo "'" . e($f['public_url']) . "',"; ?>];

            function openLightbox(url, idx) {
                currentIdx = idx;
                updateLB();
                document.getElementById('gallery-lightbox').classList.remove('hidden');
                document.getElementById('gallery-lightbox').classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function updateLB() {
                const img = document.getElementById('lb-img');
                img.style.opacity = '0';
                img.style.transform = 'scale(0.95) translateY(10px)';

                setTimeout(() => {
                    img.src = images[currentIdx];
                    img.onload = () => {
                        img.style.opacity = '1';
                        img.style.transform = 'scale(1) translateY(0)';
                    };
                }, 100);

                document.getElementById('lb-current').innerText = String(currentIdx + 1).padStart(2, '0');
                document.getElementById('lb-total').innerText = String(images.length).padStart(2, '0');
            }

            function closeLightbox() {
                document.getElementById('gallery-lightbox').classList.add('hidden');
                document.getElementById('gallery-lightbox').classList.remove('flex');
                document.body.style.overflow = 'auto';
            }

            document.getElementById('lb-next').onclick = (e) => {
                e.stopPropagation();
                currentIdx = (currentIdx + 1) % images.length;
                updateLB();
            };

            document.getElementById('lb-prev').onclick = (e) => {
                e.stopPropagation();
                currentIdx = (currentIdx - 1 + images.length) % images.length;
                updateLB();
            };

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowRight') document.getElementById('lb-next').click();
                if (e.key === 'ArrowLeft') document.getElementById('lb-prev').click();
            });
        </script>

        <style>
            @keyframes lb-fade-in {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            .animate-in {
                animation: lb-fade-in 0.5s ease-out;
            }

            .columns-1 {
                column-count: 1;
            }

            @media (min-width: 768px) {
                .md\:columns-2 {
                    column-count: 2;
                }
            }

            @media (min-width: 1024px) {
                .lg\:columns-3 {
                    column-count: 3;
                }
            }

            .break-inside-avoid {
                break-inside: avoid;
            }
        </style>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Process shortcodes in content
 */
function do_shortcode($content)
{
    global $db;
    if (!$db)
        $db = new DatabaseClient();

    // 1. [gallery id="uuid" title="Optional"]
    $content = preg_replace_callback('/\[gallery\s+id="([^"]+)"(?:\s+title="([^"]+)")?\s*\]/', function ($matches) {
        $folder_id = $matches[1];
        $title = $matches[2] ?? '';
        return render_media_gallery($folder_id, $title);
    }, $content);

    // 2. Statistics Shortcodes
    // [stat_services] - Total active services
    $content = preg_replace_callback('/\[stat_services\]/', function () use ($db) {
        $count = $db->query("SELECT count(*) as total FROM services WHERE is_active = true")[0]['total'] ?? 0;
        return $count;
    }, $content);

    // [stat_provinces] - Total active provinces
    $content = preg_replace_callback('/\[stat_provinces\]/', function () use ($db) {
        $count = $db->query("SELECT count(*) as total FROM locations_province WHERE is_active = true OR is_active = 'true'")[0]['total'] ?? 0;
        return $count;
    }, $content);

    // [stat_districts] - Total active districts
    $content = preg_replace_callback('/\[stat_districts\]/', function () use ($db) {
        $count = $db->query("SELECT count(*) as total FROM locations_district WHERE is_active = true OR is_active = 'true'")[0]['total'] ?? 0;
        return $count;
    }, $content);

    // [stat_projects] - Project count from settings
    $content = preg_replace_callback('/\[stat_projects\]/', function () {
        return get_setting('stat_projects', '1000+');
    }, $content);

    return $content;
}
