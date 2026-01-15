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
// Fetch all active services from posts table
$services = $db->select('posts', [
    'post_type' => 'service',
    'post_status' => 'publish',
    'limit' => 50,
    'order' => 'title'
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
// Fetch Pexels photos for services
require_once __DIR__ . '/../../includes/Core/PexelsService.php';
$pexelsService = new \Core\PexelsService();
$servicePhotos = $pexelsService->getRandomPhotos(count($services));

foreach ($services as $index => $service) {
    if ($serviceCount >= 24)
        break; // Limit to 24 services max

    $serviceName = htmlspecialchars($service['title']);
    $serviceSlug = htmlspecialchars($service['slug']);
    $serviceIntro = htmlspecialchars($service['short_intro'] ?? 'Profesyonel fotoğrafçılık hizmeti.');

    // Get Pexels photo
    $photo = $servicePhotos[$index] ?? null;
    $serviceImage = $photo ? ($photo['src'] ?? $photo['src']['large']) : ($serviceImages[$serviceSlug] ?? $defaultImage);

    // Replace service card glass styling
    $servicesHtml .= <<<HTML
            <!-- Service: {$serviceName} -->
            <div class="group relative bg-slate-900 rounded-5xl h-[550px] overflow-hidden shadow-2xl hover-lift min-w-[85vw] md:min-w-[350px] snap-center shrink-0">
                <img src="{$serviceImage}" alt="{$serviceName}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[2s] group-hover:scale-110 opacity-60">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/40 to-transparent"></div>
                
                <div class="absolute inset-0 p-10 flex flex-col justify-end transform translate-y-8 group-hover:translate-y-0 transition-transform duration-500">
                    <div class="bg-white/10 backdrop-blur-md border border-white/10 p-8 rounded-4xl">
                        <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                            <div class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                        </div>
                        <h3 class="text-3xl font-black text-white mb-4 tracking-tight">{$serviceName}</h3>
                        <p class="text-slate-200 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">{$serviceIntro}</p>
                        <a href="/hizmetlerimiz/{$serviceSlug}" class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn border border-white/20 px-6 py-3 rounded-full hover:bg-white hover:text-brand-900 transition-all">
                            Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
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
        
        <div class="flex overflow-x-auto snap-x snap-mandatory gap-6 pb-8 hide-scrollbar -mx-4 px-4 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-10 md:overflow-visible md:pb-0 md:mx-0 md:px-0">
{$servicesHtml}        </div>
    </div>
</section>
HTML;

$newContent = preg_replace($pattern, $replacement, $content);
if ($newContent !== null) {
    $content = $newContent;
}

// Remove any existing freelancer sections from content to avoid duplication
$content = preg_replace('/<section[^>]*id="freelancer-basvuru"[^>]*>.*?<\/section>/s', '', $content);
$content = preg_replace('/<section[^>]*class="[^"]*bg-gradient-to-br from-slate-50 to-white[^"]*"[^>]*>.*?<\/section>/s', '', $content);

// Replace the entire H1 content with the React mount point for a unified experience
// This refined pattern removes any static "Dönüştürüyoruz" or other content inside the H1
$heroPattern = '/(<h1[^>]*>).*?(<\/h1>)/s';
$content = preg_replace($heroPattern, '$1<div id="hero-effect-root" class="overflow-visible flex items-center justify-center"></div>$2', $content);

// Force sub-header text to be solid brand color and bold
$content = str_replace('text-brand-300', 'text-brand-500 font-bold', $content);
$content = str_replace('text-brand-400', 'text-brand-500 font-bold', $content);

// Feature: Rounder Hero Buttons
$content = str_replace('rounded-2xl', 'rounded-full', $content);

// Force button shadows to be premium brand shadows
$content = str_replace('shadow-[0_20px_50px_rgba(14,165,233,0.4)]', 'shadow-2xl shadow-brand-500/50', $content);

// FIX: Add top padding to Hero container on mobile to prevent overlap with fixed header
// identifying class: "relative z-10 container mx-auto px-4 overflow-visible"
$content = str_replace(
    'class="relative z-10 container mx-auto px-4 overflow-visible"',
    'class="relative z-10 container mx-auto px-4 overflow-visible pt-40 pb-20 md:pt-0 md:pb-0"',
    $content
);

// Remove the old GooeyText script from content
$newContent = preg_replace('/<script>.*?window\.initGooeyText.*?<\/script>/s', '', $content);
if ($newContent !== null) {
    $content = $newContent;
}

// Feature: Convert Process Section to Horizontal Scroll on Mobile
// Container
$content = str_replace(
    'class="grid md:grid-cols-4 gap-16 relative"',
    'class="flex md:grid md:grid-cols-4 overflow-x-auto snap-x gap-6 md:gap-16 pb-8 md:pb-0 relative -mx-4 px-4 md:mx-0 md:px-0"',
    $content
);
// Items
$content = str_replace(
    'class="relative z-10 text-center group"',
    'class="relative z-10 text-center group min-w-[280px] md:min-w-0 snap-center shrink-0"',
    $content
);

echo do_shortcode($content);
?>

<!-- Dynamic Slider Logic Footer -->
<script src="/assets/js/react/hero-effect.iife.js"></script>
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

<?php
// Only include the freelancer section if it is NOT already in the content
if (strpos($content, 'freelancer-basvuru') === false):
    ?>
    <!-- Freelancer CTA Section -->
    <section class="py-24 bg-slate-50 relative overflow-hidden" id="freelancer-basvuru">
        <!-- Decorative Elements -->
        <div
            class="absolute top-0 right-0 w-[500px] h-[500px] bg-brand-200/20 rounded-full blur-[120px] -mr-64 -mt-64 animate-pulse-subtle">
        </div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-accent-200/10 rounded-full blur-[120px] -ml-64 -mb-64 animate-pulse-subtle"
            style="animation-delay: 2s"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <span
                    class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 font-black tracking-[0.2em] uppercase text-[10px] mb-6 border border-brand-100">Ekibimize
                    Katıl</span>
                <h2 class="font-heading font-black text-4xl md:text-6xl text-slate-900 mb-8 tracking-tight">Freelancer
                    Olarak <span class="text-gradient">Sisteme Katıl</span></h2>
                <p class="text-slate-500 text-xl font-light leading-relaxed max-w-2xl mx-auto mb-12">
                    Profesyonel mekan fotoğrafçısı mısınız? Ekibimize katılın ve Türkiye'nin dört bir yanındaki projelerde
                    çözüm ortağımız olun.
                </p>

                <button onclick="openFreelancerModal()"
                    class="group relative px-12 py-5 bg-brand-600 hover:bg-brand-500 text-white rounded-full font-black text-lg shadow-xl shadow-brand-500/30 transition-all hover:scale-105 active:scale-95 overflow-hidden">
                    <span class="relative z-10 flex items-center gap-3">
                        Başvuru Formunu Aç
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                            class="group-hover:translate-x-1 transition-transform">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </section>

    <!-- Freelancer Modal -->
    <div id="freelancer-modal" class="fixed inset-0 z-[200] hidden opacity-0 transition-opacity duration-300"
        aria-modal="true">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeFreelancerModal()"></div>

        <!-- Modal Content -->
        <div
            class="absolute inset-x-0 bottom-0 md:inset-0 md:flex md:items-center md:justify-center pointer-events-none p-4 md:p-6">
            <div class="bg-white w-full max-w-5xl max-h-[90vh] md:max-h-[85vh] rounded-t-4xl md:rounded-4xl shadow-2xl overflow-hidden pointer-events-auto transform translate-y-full md:translate-y-10 scale-95 transition-all duration-300 flex flex-col"
                id="freelancer-modal-content">

                <!-- Modal Header -->
                <div
                    class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-white/80 backdrop-blur-md z-10">
                    <h3 class="font-heading font-black text-xl text-slate-900">Freelancer Başvurusu</h3>
                    <button onclick="closeFreelancerModal()"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 18 18" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-6 md:p-10">
                    <form id="freelancer-form" class="space-y-8">
                        <!-- Name and Email Row -->
                        <div class="grid md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="freelancer-name" class="block text-sm font-bold text-slate-700 ml-1">Ad Soyad
                                    <span class="text-brand-500">*</span></label>
                                <input type="text" id="freelancer-name" name="name" required
                                    class="w-full px-6 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none font-medium text-slate-900"
                                    placeholder="Adınız Soyadınız">
                            </div>
                            <div class="space-y-2">
                                <label for="freelancer-email" class="block text-sm font-bold text-slate-700 ml-1">E-posta
                                    <span class="text-brand-500">*</span></label>
                                <input type="email" id="freelancer-email" name="email" required
                                    class="w-full px-6 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none font-medium text-slate-900"
                                    placeholder="ornek@email.com">
                            </div>
                        </div>

                        <!-- Phone and City Row -->
                        <div class="grid md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="freelancer-phone" class="block text-sm font-bold text-slate-700 ml-1">Telefon
                                    <span class="text-brand-500">*</span></label>
                                <input type="tel" id="freelancer-phone" name="phone" required
                                    class="w-full px-6 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none font-medium text-slate-900"
                                    placeholder="0555 123 45 67">
                            </div>
                            <div class="space-y-2">
                                <label for="freelancer-city" class="block text-sm font-bold text-slate-700 ml-1">Şehir <span
                                        class="text-brand-500">*</span></label>
                                <input type="text" id="freelancer-city" name="city" required
                                    class="w-full px-6 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none font-medium text-slate-900"
                                    placeholder="Bulunduğunuz Şehir">
                            </div>
                        </div>

                        <!-- Experience -->
                        <div class="space-y-2">
                            <label for="freelancer-experience" class="block text-sm font-bold text-slate-700 ml-1">Deneyim
                                Yılı <span class="text-brand-500">*</span></label>
                            <div class="relative">
                                <select id="freelancer-experience" name="experience" required
                                    class="w-full px-6 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none font-medium text-slate-900 appearance-none">
                                    <option value="">Seçiniz...</option>
                                    <option value="0-1">0-1 yıl</option>
                                    <option value="1-3">1-3 yıl</option>
                                    <option value="3-5">3-5 yıl</option>
                                    <option value="5-10">5-10 yıl</option>
                                    <option value="10+">10+ yıl</option>
                                </select>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </div>
                        </div>

                        <!-- Specialization -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-slate-700 ml-1">Uzmanlık Alanlarınız <span
                                    class="text-brand-500">*</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <?php
                                $specialties = ['mimari' => 'Mimari', 'ic-mekan' => 'İç Mekan', 'otel' => 'Otel', 'emlak' => 'Emlak', 'yemek' => 'Yemek', 'drone' => 'Drone'];
                                foreach ($specialties as $val => $label): ?>
                                    <label
                                        class="group flex items-center gap-3 p-4 rounded-xl border-2 border-slate-100 bg-slate-50/30 hover:bg-white hover:border-brand-200 cursor-pointer transition-all active:scale-95">
                                        <div class="relative flex items-center justify-center">
                                            <input type="checkbox" name="specialization[]" value="<?= $val ?>"
                                                class="peer appearance-none w-5 h-5 rounded-md border-2 border-slate-200 checked:bg-brand-500 checked:border-brand-500 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                                fill="none" stroke="white" stroke-width="4" stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                        <span
                                            class="text-sm font-bold text-slate-600 group-hover:text-slate-900"><?= $label ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Portfolio URL -->
                        <div class="space-y-2">
                            <label for="freelancer-portfolio" class="block text-sm font-bold text-slate-700 ml-1">Portfolio
                                / Instagram</label>
                            <input type="url" id="freelancer-portfolio" name="portfolio"
                                class="w-full px-6 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none font-medium text-slate-900 placeholder:text-slate-400"
                                placeholder="https://...">
                        </div>

                        <!-- Message -->
                        <div class="space-y-2">
                            <label for="freelancer-message"
                                class="block text-sm font-bold text-slate-700 ml-1">Hakkınızda</label>
                            <textarea id="freelancer-message" name="message" rows="4"
                                class="w-full px-6 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none font-medium text-slate-900 resize-none placeholder:text-slate-400"
                                placeholder="Kısaca kendinizden bahsedin..."></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full py-5 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl font-black text-lg shadow-xl shadow-brand-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]">Başvuruyu
                                Gönder</button>
                        </div>
                    </form>

                    <!-- Success Message -->
                    <div id="freelancer-success"
                        class="hidden flex-col items-center justify-center text-center py-10 h-full">
                        <div
                            class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-6 animate-bounce">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                class="text-green-600">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <h3 class="text-3xl font-black text-slate-900 mb-2">Başvurunuz Alındı!</h3>
                        <p class="text-slate-500 text-lg max-w-md">Teşekkürler. Başvurunuz ekibimiz tarafından incelenip en
                            kısa sürede dönüş yapılacaktır.</p>
                        <button onclick="closeFreelancerModal()"
                            class="mt-8 px-8 py-3 bg-slate-100 text-slate-700 font-bold rounded-full hover:bg-slate-200 transition-colors">Kapat</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal Logic
        const modal = document.getElementById('freelancer-modal');
        const modalContent = document.getElementById('freelancer-modal-content');

        function openFreelancerModal() {
            modal.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('translate-y-full', 'scale-95');
                modalContent.classList.add('md:translate-y-0', 'scale-100');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeFreelancerModal() {
            modal.classList.add('opacity-0');
            modalContent.classList.add('translate-y-full', 'scale-95');
            modalContent.classList.remove('md:translate-y-0', 'scale-100');

            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }

        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("freelancer-form");
            const successMessage = document.getElementById("freelancer-success");

            if (form) {
                form.addEventListener("submit", async function (e) {
                    e.preventDefault();
                    // Original submission logic...
                    const formData = new FormData(form);
                    const data = {
                        name: formData.get("name"),
                        email: formData.get("email"),
                        phone: formData.get("phone"),
                        city: formData.get("city"),
                        experience: formData.get("experience"),
                        specialization: formData.getAll("specialization[]"),
                        portfolio: formData.get("portfolio"),
                        message: formData.get("message"),
                        type: "freelancer_application"
                    };

                    if (data.specialization.length === 0) {
                        alert("Lütfen en az bir uzmanlık alanı seçiniz.");
                        return;
                    }

                    try {
                        const response = await fetch("/api/freelancer-application.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify(data)
                        });

                        if (response.ok) {
                            form.style.display = "none";
                            successMessage.classList.remove("hidden");
                            successMessage.classList.add("flex");
                        } else {
                            alert("Bir hata oluştu.");
                        }
                    } catch (error) {
                        console.error(error);
                        alert("Bir hata oluştu.");
                    }
                });
            }
        });
    </script>
<?php endif; ?>

<?php include __DIR__ . '/../page-footer.php'; ?>