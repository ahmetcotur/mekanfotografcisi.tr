<?php
/**
 * Homepage template with dynamic hero slider and services
 */
include __DIR__ . '/../page-header.php';

// Prepare Pexels Slider Images
require_once __DIR__ . '/../../includes/Core/PexelsService.php';
$pexels = new \Core\PexelsService();
$sliderPhotos = $pexels->getRandomPhotos(5);
$sliderImagesJson = json_encode(array_map(function ($p) {
    return $p['src'];
}, $sliderPhotos));

// Fetch services from database
require_once __DIR__ . '/../../includes/database.php';
$db = new DatabaseClient();
$services = $db->select('services', [
    'eq.is_active' => true,
    'select' => 'id,name,slug,short_intro',
    'order' => 'name'
]);

// Service images mapping (Pexels URLs from the current homepage)
$serviceImages = [
    'mimari-fotografcilik' => 'https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg',
    'ic-mekan-fotografciligi' => 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg',
    'otel-fotografciligi' => 'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg',
    'emlak-fotografciligi' => 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg',
    'otel-restoran-fotografciligi' => 'https://images.pexels.com/photos/67468/pexels-photo-67468.jpeg',
    'yemek-fotografciligi' => 'https://images.pexels.com/photos/67468/pexels-photo-67468.jpeg',
    'villa-fotografciligi' => 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg',
    'yat-fotografciligi' => 'https://images.pexels.com/photos/7045926/pexels-photo-7045926.jpeg',
    'butik-otel-fotografciligi' => 'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg',
    'lifestyle-fotografciligi' => 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg',
    'konut-projeleri-fotografciligi' => 'https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg',
    'ofis-fotografciligi' => 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg',
    'is-merkezi-fotografciligi' => 'https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg',
    'ticari-alan-fotografciligi' => 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg',
    'pansiyon-fotografciligi' => 'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg',
    'termal-tesis-fotografciligi' => 'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg',
];

// Default fallback image
$defaultImage = 'https://images.pexels.com/photos/7045926/pexels-photo-7045926.jpeg';

// Build services HTML
$servicesHtml = '';
$serviceCount = 0;
foreach ($services as $service) {
    if ($serviceCount >= 16)
        break; // Limit to 16 services max

    $serviceName = htmlspecialchars($service['name']);
    $serviceSlug = htmlspecialchars($service['slug']);
    $serviceIntro = htmlspecialchars($service['short_intro'] ?? 'Profesyonel fotoğrafçılık hizmeti.');
    $serviceImage = $serviceImages[$serviceSlug] ?? $defaultImage;

    $servicesHtml .= <<<HTML
            <!-- Service: {$serviceName} -->
            <div class="group relative bg-slate-900 rounded-[2.5rem] h-[500px] overflow-hidden shadow-2xl">
                <img src="{$serviceImage}" alt="{$serviceName}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full p-10 transform translate-y-6 group-hover:translate-y-0 transition-transform duration-500">
                    <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                    </div>
                    <h3 class="text-3xl font-black text-white mb-4 tracking-tight">{$serviceName}</h3>
                    <p class="text-slate-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">{$serviceIntro}</p>
                    <a href="/hizmetlerimiz/{$serviceSlug}" class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn">
                        Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

HTML;
    $serviceCount++;
}

// Get the content and replace the services section
$content = $post->content;

// Find and replace the services grid section
$pattern = '/<!-- Services Preview -->.*?<\/section>/s';
$replacement = <<<HTML
<!-- Services Preview -->
<section class="py-32 bg-white" id="hizmetler">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-4xl mx-auto mb-24">
             <span class="text-brand-600 font-black tracking-[0.2em] uppercase text-xs mb-6 block">Kategoriler</span>
            <h2 class="font-heading font-black text-4xl md:text-6xl text-slate-900 mb-8">Neler Yapıyoruz?</h2>
            <p class="text-slate-500 text-xl lg:text-2xl font-light leading-relaxed">Her mekanın kendine has bir dili vardır. Biz o dili görselleştiriyoruz.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
{$servicesHtml}        </div>
    </div>
</section>
HTML;

$content = preg_replace($pattern, $replacement, $content);

echo do_shortcode($content);
?>

<!-- Dynamic Slider Logic Footer -->
<script>
    (function () {
        const images = <?= $sliderImagesJson ?>;
        const container = document.getElementById('hero-slides');
        if (!container) return;

        // Clear existing placeholder slides
        container.innerHTML = '';
        let current = 0;

        if (images.length === 0) {
            // Fallback image if Pexels fails
            images.push('https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg');
        }

        // Initialize slides
        images.forEach((img, index) => {
            const div = document.createElement('div');
            div.className = 'absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-1000 ease-in-out';
            div.style.backgroundImage = `url('${img}')`;
            div.style.opacity = index === 0 ? '1' : '0';
            container.appendChild(div);
        });

        // Loop slider
        if (images.length > 1) {
            setInterval(() => {
                const slides = container.children;
                if (!slides || !slides[current]) return;
                slides[current].style.opacity = '0';
                current = (current + 1) % slides.length;
                if (slides[current]) {
                    slides[current].style.opacity = '1';
                }
            }, 5000);
        }
    })();
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>