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

    // Replace service card glass styling
    $servicesHtml .= <<<HTML
            <!-- Service: {$serviceName} -->
            <div class="group relative bg-slate-900 rounded-5xl h-[500px] overflow-hidden shadow-2xl hover-lift">
                <img src="{$serviceImage}" alt="{$serviceName}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-60">
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
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
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

// Replace the Hero morph divs with the React mount point
$heroPattern = '/<div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6 mb-8 overflow-visible">.*?<div class="text-white mt-8 drop-shadow-2xl">Dönüştürüyoruz<\/div>/s';
$newContent = preg_replace($heroPattern, '<div id="hero-effect-root" class="overflow-visible min-h-[300px] flex items-center justify-center"></div><div class="text-white mt-8 drop-shadow-2xl">Dönüştürüyoruz</div>', $content);
if ($newContent !== null) {
    $content = $newContent;
}

// Remove the old GooeyText script from content
$newContent = preg_replace('/<script>.*?window\.initGooeyText.*?<\/script>/s', '', $content);
if ($newContent !== null) {
    $content = $newContent;
}

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
    <!-- Freelancer Application Section -->
    <section class="py-32 bg-slate-50 relative overflow-hidden" id="freelancer-basvuru">
        <!-- Decorative Elements -->
        <div
            class="absolute top-0 right-0 w-[500px] h-[500px] bg-brand-200/20 rounded-full blur-[120px] -mr-64 -mt-64 animate-pulse-subtle">
        </div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-accent-200/10 rounded-full blur-[120px] -ml-64 -mb-64 animate-pulse-subtle"
            style="animation-delay: 2s"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-20 animate-slide-up">
                    <span
                        class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 font-black tracking-[0.2em] uppercase text-[10px] mb-6 border border-brand-100">Ekibimize
                        Katıl</span>
                    <h2 class="font-heading font-black text-4xl md:text-7xl text-slate-900 mb-8 tracking-tight">Freelancer
                        Olarak <br><span class="text-gradient">Sisteme Katıl</span></h2>
                    <p class="text-slate-500 text-xl lg:text-2xl font-light leading-relaxed max-w-3xl mx-auto">
                        Profesyonel mekan fotoğrafçısı mısınız? Ekibimize katılın ve Türkiye'nin dört bir yanındaki
                        projelerde çözüm ortağımız olun.
                    </p>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-5xl shadow-[0_32px_80px_-20px_rgba(0,0,0,0.08)] p-8 md:p-16 border border-slate-100 animate-slide-up"
                    style="animation-delay: 0.2s">
                    <form id="freelancer-form" class="space-y-8">
                        <!-- Name and Email Row -->
                        <div class="grid md:grid-cols-2 gap-10">
                            <div class="space-y-3">
                                <label for="freelancer-name" class="block text-sm font-bold text-slate-700 ml-2">
                                    Ad Soyad <span class="text-brand-500">*</span>
                                </label>
                                <input type="text" id="freelancer-name" name="name" required
                                    class="w-full px-8 py-5 rounded-3xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium placeholder:text-slate-400"
                                    placeholder="Adınız ve soyadınız">
                            </div>
                            <div class="space-y-3">
                                <label for="freelancer-email" class="block text-sm font-bold text-slate-700 ml-2">
                                    E-posta <span class="text-brand-500">*</span>
                                </label>
                                <input type="email" id="freelancer-email" name="email" required
                                    class="w-full px-8 py-5 rounded-3xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium placeholder:text-slate-400"
                                    placeholder="ornek@email.com">
                            </div>
                        </div>

                        <!-- Phone and City Row -->
                        <div class="grid md:grid-cols-2 gap-10">
                            <div class="space-y-3">
                                <label for="freelancer-phone" class="block text-sm font-bold text-slate-700 ml-2">
                                    Telefon <span class="text-brand-500">*</span>
                                </label>
                                <input type="tel" id="freelancer-phone" name="phone" required
                                    class="w-full px-8 py-5 rounded-3xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium placeholder:text-slate-400"
                                    placeholder="0555 123 45 67">
                            </div>
                            <div class="space-y-3">
                                <label for="freelancer-city" class="block text-sm font-bold text-slate-700 ml-2">
                                    Bulunduğunuz Şehir <span class="text-brand-500">*</span>
                                </label>
                                <input type="text" id="freelancer-city" name="city" required
                                    class="w-full px-8 py-5 rounded-3xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium placeholder:text-slate-400"
                                    placeholder="Örn: Antalya">
                            </div>
                        </div>

                        <!-- Experience -->
                        <div class="space-y-3">
                            <label for="freelancer-experience" class="block text-sm font-bold text-slate-700 ml-2">
                                Deneyim Yılı <span class="text-brand-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="freelancer-experience" name="experience" required
                                    class="w-full px-8 py-5 rounded-3xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium appearance-none">
                                    <option value="">Seçiniz...</option>
                                    <option value="0-1">0-1 yıl</option>
                                    <option value="1-3">1-3 yıl</option>
                                    <option value="3-5">3-5 yıl</option>
                                    <option value="5-10">5-10 yıl</option>
                                    <option value="10+">10+ yıl</option>
                                </select>
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Specialization -->
                        <div class="space-y-4">
                            <label class="block text-sm font-bold text-slate-700 ml-2">
                                Uzmanlık Alanlarınız <span class="text-brand-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <?php
                                $specialties = [
                                    'mimari' => 'Mimari',
                                    'ic-mekan' => 'İç Mekan',
                                    'otel' => 'Otel',
                                    'emlak' => 'Emlak',
                                    'yemek' => 'Yemek',
                                    'drone' => 'Drone'
                                ];
                                foreach ($specialties as $val => $label):
                                    ?>
                                    <label
                                        class="group flex items-center gap-4 p-5 rounded-3xl border-2 border-slate-100 bg-slate-50/30 hover:bg-white hover:border-brand-200 cursor-pointer transition-all active:scale-95">
                                        <div class="relative flex items-center justify-center">
                                            <input type="checkbox" name="specialization[]" value="<?= $val ?>"
                                                class="peer appearance-none w-6 h-6 rounded-lg border-2 border-slate-200 checked:bg-brand-500 checked:border-brand-500 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="white" stroke-width="4" stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="absolute opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </div>
                                        <span
                                            class="text-slate-600 font-bold group-hover:text-slate-900 transition-colors"><?= $label ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Portfolio URL -->
                        <div class="space-y-3">
                            <label for="freelancer-portfolio" class="block text-sm font-bold text-slate-700 ml-2">
                                Portfolio / Instagram Linki
                            </label>
                            <input type="url" id="freelancer-portfolio" name="portfolio"
                                class="w-full px-8 py-5 rounded-3xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium placeholder:text-slate-400"
                                placeholder="https://instagram.com/kullaniciadi veya portfolio linki">
                        </div>

                        <!-- Message -->
                        <div class="space-y-3">
                            <label for="freelancer-message" class="block text-sm font-bold text-slate-700 ml-2">
                                Kendinizden Bahsedin
                            </label>
                            <textarea id="freelancer-message" name="message" rows="5"
                                class="w-full px-8 py-5 rounded-3xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium resize-none placeholder:text-slate-400"
                                placeholder="Deneyimleriniz, ekipmanlarınız ve neden ekibimize katılmak istediğiniz hakkında kısaca bilgi verin..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center pt-8">
                            <button type="submit"
                                class="group relative px-16 py-6 bg-brand-600 hover:bg-brand-500 text-white rounded-3xl font-black text-xl shadow-[0_20px_50px_rgba(14,165,233,0.3)] transition-all hover:scale-105 active:scale-95 overflow-hidden">
                                <span class="relative z-10">Başvurumu Gönder</span>
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
                                </div>
                            </button>
                            <p class="text-sm font-medium text-slate-400 mt-8">
                                Başvurunuz en kısa sürede değerlendirilecek ve size dönüş yapılacaktır.
                            </p>
                        </div>
                    </form>

                    <!-- Success Message (Hidden by default) -->
                    <div id="freelancer-success" class="hidden text-center py-12">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                class="text-green-600">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <h3 class="text-3xl font-black text-slate-900 mb-4">Başvurunuz Alındı!</h3>
                        <p class="text-slate-600 text-lg">Teşekkür ederiz. En kısa sürede sizinle iletişime geçeceğiz.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("freelancer-form");
            const successMessage = document.getElementById("freelancer-success");

            if (form) {
                form.addEventListener("submit", async function (e) {
                    e.preventDefault();

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

                    // Validate specialization
                    if (data.specialization.length === 0) {
                        alert("Lütfen en az bir uzmanlık alanı seçiniz.");
                        return;
                    }

                    try {
                        const response = await fetch("/api/freelancer-application.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify(data)
                        });

                        if (response.ok) {
                            form.style.display = "none";
                            successMessage.classList.remove("hidden");

                            // Scroll to success message
                            successMessage.scrollIntoView({ behavior: "smooth", block: "center" });
                        } else {
                            alert("Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.");
                        }
                    } catch (error) {
                        console.error("Form submission error:", error);
                        alert("Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.");
                    }
                });
            }
        });
    </script>

<?php endif; ?>
<?php include __DIR__ . '/../page-footer.php'; ?>