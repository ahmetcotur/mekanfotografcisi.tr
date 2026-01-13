<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/Core/PexelsService.php';

use Core\PexelsService;

$db = new DatabaseClient();
$pexels = new PexelsService();

echo "Fetching photos...\n";
$photos = $pexels->getRandomPhotos(40);

if (empty($photos)) {
    echo "Warning: Using fallback images.\n";
    $photos = [
        [
            'src' => 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg',
            'thumbnail' => 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg',
            'alt' => 'Interior',
            'photographer' => 'Fallback'
        ]
    ];
}

function getPhoto($photos, $index)
{
    if (empty($photos))
        return null;
    $keys = array_keys($photos);
    $key = $keys[$index % count($keys)];
    return $photos[$key] ?? null;
}

echo "Starting Content Refresh...\n";

// --- 0. ENSURE LOCATIONS PAGE EXISTS ---
$locPage = $db->select('posts', ['slug' => 'locations']);
if (empty($locPage)) {
    $db->insert('posts', [
        'title' => 'Hizmet Bölgelerimiz',
        'slug' => 'locations',
        'content' => '', // Content handled by template
        'post_type' => 'page',
        'post_status' => 'publish',
        'created_at' => date('Y-m-d H:i:s'),
        'author_id' => 1
    ]);
    echo "Created 'locations' page.\n";
}

// --- 1. HOMEPAGE WITH HERO SLIDER ---
$sliderPhotos = [];
for ($i = 0; $i < 5; $i++) {
    $p = getPhoto($photos, $i);
    if ($p)
        $sliderPhotos[] = $p['src']; // CORRECTED: src is string
}
$sliderJson = json_encode($sliderPhotos);

$homeContent = <<<HTML
<!-- Hero Section -->
<section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden" id="hero-slider">
    <!-- Slider Backgrounds -->
    <div class="absolute inset-0 z-0">
        <div id="hero-slides" class="w-full h-full">
            <!-- JS will inject slides here -->
        </div>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 container mx-auto px-4 text-center">
        <span class="inline-block py-1 px-3 rounded-full bg-brand-500/20 border border-brand-500/30 text-brand-300 text-sm font-medium tracking-wide mb-6 backdrop-blur-md animate-fade-in">
            Antalya & Muğla Bölgesi
        </span>
        <h1 class="font-heading font-bold text-5xl md:text-7xl lg:text-8xl text-white mb-6 leading-tight tracking-tight drop-shadow-2xl">
            Mekanınızı <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-cyan-300">Sanata</span> Dönüştürüyoruz
        </h1>
        <p class="text-lg md:text-xl text-slate-200 max-w-2xl mx-auto mb-10 leading-relaxed font-light">
            Mimari yapılar, oteller ve iç mekanlar için profesyonel fotoğrafçılık çözümleri.
        </p>
        <div class="flex flex-col md:flex-row gap-4 justify-center items-center">
            <button onclick="openQuoteWizard()" class="w-full md:w-auto px-8 py-4 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-semibold shadow-lg shadow-brand-500/25 transition-all hover:scale-105">
                Hemen Fiyat Hesapla
            </button>
            <a href="/portfolio" class="w-full md:w-auto px-8 py-4 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl font-semibold backdrop-blur-md transition-all hover:scale-105">
                Portfolyoyu İncele
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-20 bg-white border-b border-slate-100">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="text-center group">
                <div class="text-4xl md:text-5xl font-bold text-brand-600 mb-2 group-hover:scale-110 transition-transform">500+</div>
                <div class="text-slate-600 font-medium">Mutlu Müşteri</div>
            </div>
            <div class="text-center group">
                <div class="text-4xl md:text-5xl font-bold text-brand-600 mb-2 group-hover:scale-110 transition-transform">1000+</div>
                <div class="text-slate-600 font-medium">Proje Teslimi</div>
            </div>
            <div class="text-center group">
                <div class="text-4xl md:text-5xl font-bold text-brand-600 mb-2 group-hover:scale-110 transition-transform">8+</div>
                <div class="text-slate-600 font-medium">Yıllık Deneyim</div>
            </div>
             <div class="text-center group">
                <div class="text-4xl md:text-5xl font-bold text-brand-600 mb-2 group-hover:scale-110 transition-transform">2</div>
                <div class="text-slate-600 font-medium">Bölge</div>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section class="py-24 bg-slate-50" id="hizmetler">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="font-heading font-bold text-3xl md:text-4xl text-slate-900 mb-4">Hizmetlerimiz</h2>
            <p class="text-slate-600 text-lg">Mekanlarınızın potansiyelini ortaya çıkaran profesyonel çekim hizmetleri.</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Service 1 -->
            <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300">
                <div class="relative h-64 overflow-hidden">
                    <img src="https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg" alt="Mimari" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <h3 class="absolute bottom-4 left-4 text-2xl font-bold text-white">Mimari Fotoğrafçılık</h3>
                </div>
                <div class="p-8">
                    <p class="text-slate-600 mb-6">Oteller, villalar ve ticari yapılar için profesyonel mimari çekimler.</p>
                    <a href="/services/mimari-fotografcilik" class="text-brand-600 font-semibold group-hover:underline">Detaylı İncele -></a>
                </div>
            </div>
             <!-- Service 2 -->
            <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300">
                <div class="relative h-64 overflow-hidden">
                    <img src="https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg" alt="İç Mekan" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <h3 class="absolute bottom-4 left-4 text-2xl font-bold text-white">İç Mekan & Dekorasyon</h3>
                </div>
                <div class="p-8">
                    <p class="text-slate-600 mb-6">Mekanınızın detaylarını ve atmosferini yansıtan estetik kareler.</p>
                    <a href="/services/ic-mekan-fotografciligi" class="text-brand-600 font-semibold group-hover:underline">Detaylı İncele -></a>
                </div>
            </div>
             <!-- Service 3 -->
             <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300">
                <div class="relative h-64 overflow-hidden">
                    <img src="https://images.pexels.com/photos/7045926/pexels-photo-7045926.jpeg" alt="Havadan" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <h3 class="absolute bottom-4 left-4 text-2xl font-bold text-white">Havadan Çekim</h3>
                </div>
                <div class="p-8">
                    <p class="text-slate-600 mb-6">Drone ile mekanınızın konumunu ve büyüklüğünü vurgulayan çekimler.</p>
                    <a href="/services" class="text-brand-600 font-semibold group-hover:underline">Detaylı İncele -></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-24 relative bg-slate-900" id="iletisim">
    <div class="container mx-auto px-4 relative z-10 text-center">
        <h2 class="font-heading font-bold text-4xl text-white mb-6">Bizimle İletişime Geçin</h2>
        <p class="text-xl text-brand-100 mb-10">Profesyonel çekimler için hemen teklif alın.</p>
        <a href="mailto:info@mekanfotografcisi.tr" class="inline-block px-8 py-4 bg-white text-brand-900 rounded-xl font-bold hover:bg-brand-50 transition-colors">
            info@mekanfotografcisi.tr
        </a>
    </div>
</section>

<!-- Scripts for Slider -->
<script>
    (function() {
        const images = {$sliderJson};
        const container = document.getElementById('hero-slides');
        let current = 0;

        // Initialize slides
        images.forEach((img, index) => {
            const div = document.createElement('div');
            div.className = 'absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-1000 ease-in-out';
            div.style.backgroundImage = `url('\${img}')`;
            div.style.opacity = index === 0 ? '1' : '0';
            container.appendChild(div);
        });

        // Loop
        setInterval(() => {
            const slides = container.children;
            slides[current].style.opacity = '0';
            current = (current + 1) % slides.length;
            slides[current].style.opacity = '1';
        }, 5000);
    })();
</script>
HTML;

$db->update('posts', ['content' => $homeContent], ['slug' => 'homepage']);
echo "Homepage Slider Updated.\n";

// --- 2. SERVICES REDESIGN WITH GALLERIES ---
$services = $db->select('posts', ['post_type' => 'service']);
$sIndex = 0;
foreach ($services as $svc) {
    if ($svc['slug'] === 'services')
        continue;

    $p = getPhoto($photos, $sIndex + 5);
    $heroUrl = $p['src'] ?? ''; // CORRECTED
    $title = htmlspecialchars($svc['title']);

    // Build explicit HTML for gallery
    $galleryHtml = '<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-12 mb-12">';
    for ($j = 0; $j < 4; $j++) {
        $gP = getPhoto($photos, $sIndex + 15 + $j);
        $src = $gP['thumbnail'] ?? ''; // CORRECTED
        $full = $gP['src'] ?? ''; // CORRECTED
        $galleryHtml .= <<<HTML
            <a href="{$full}" target="_blank" class="block rounded-xl overflow-hidden aspect-square hover:opacity-90 transition-opacity relative group">
                <img src="{$src}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
            </a>
HTML;
    }
    $galleryHtml .= '</div>';

    $svcContent = <<<HTML
    <article class="pt-32 pb-20">
        <div class="container mx-auto px-4">
            <!-- Header -->
            <div class="max-w-4xl mx-auto text-center mb-16">
                <span class="text-brand-600 font-bold tracking-wider uppercase text-sm mb-4 block">Hizmetlerimiz</span>
                <h1 class="font-heading font-extrabold text-4xl md:text-6xl text-slate-900 mb-6">{$title}</h1>
                <div class="h-1 w-20 bg-brand-500 mx-auto rounded-full"></div>
            </div>

            <!-- Main Feature -->
            <div class="grid lg:grid-cols-2 gap-16 items-center mb-12">
                <div class="relative group rounded-3xl overflow-hidden shadow-2xl aspect-[4/3]">
                    <img src="{$heroUrl}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                <div class="space-y-6">
                    <h2 class="text-3xl font-bold text-slate-900">Profesyonel Çözüm</h2>
                    <p class="text-lg text-slate-600 leading-relaxed">
                        {$svc['excerpt']}
                        Mekanınızın en iyi özelliklerini ön plana çıkaran profesyonel çekim teknikleri kullanıyoruz.
                        Işık, kompozisyon ve ileri düzey düzenleme ile markanızın görsel kalitesini artırıyoruz.
                    </p>
                    <ul class="space-y-3 pt-4">
                        <li class="flex items-center gap-3 text-slate-700">✓ Yüksek Çözünürlük (4K+)</li>
                        <li class="flex items-center gap-3 text-slate-700">✓ Profesyonel Retouch</li>
                        <li class="flex items-center gap-3 text-slate-700">✓ Hızlı Teslimat</li>
                    </ul>
                     <div class="pt-6">
                        <button onclick="openQuoteWizard('{$svc['slug']}')" class="inline-flex px-8 py-3 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-500 shadow-lg shadow-brand-500/25 transition-all hover:scale-105">
                            Hemen Fiyat Al ✨
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Gallery -->
            <div class="mb-20">
                <h3 class="text-2xl font-bold text-slate-900 mb-6 text-center">Örnek Çalışmalar</h3>
                {$galleryHtml}
            </div>
            
        </div>
    </article>
HTML;

    $db->update('posts', ['content' => $svcContent], ['id' => $svc['id']]);
    echo "Service with Gallery Updated: {$title}\n";
    $sIndex++;
}

echo "All updates complete.\n";
