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

<!-- Freelancer Application Section -->
<section class="py-32 bg-gradient-to-br from-slate-50 to-white relative overflow-hidden" id="freelancer-basvuru">
    <!-- Decorative Elements -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-brand-100/30 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-100/30 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-16">
                <span class="text-brand-600 font-black tracking-[0.2em] uppercase text-xs mb-6 block">Ekibimize
                    Katıl</span>
                <h2 class="font-heading font-black text-4xl md:text-6xl text-slate-900 mb-8">Freelancer Olarak Sisteme
                    Katıl</h2>
                <p class="text-slate-600 text-xl lg:text-2xl font-light leading-relaxed max-w-3xl mx-auto">
                    Profesyonel mekan fotoğrafçısı mısınız? Ekibimize katılın ve Türkiye'nin dört bir yanındaki
                    projelerden pay alın.
                </p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-[3rem] shadow-2xl p-8 md:p-12 border border-slate-100">
                <form id="freelancer-form" class="space-y-8">
                    <!-- Name and Email Row -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="freelancer-name" class="block text-sm font-bold text-slate-700 mb-3">
                                Ad Soyad <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="freelancer-name" name="name" required
                                class="w-full px-6 py-4 rounded-2xl border-2 border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium"
                                placeholder="Adınız ve soyadınız">
                        </div>
                        <div>
                            <label for="freelancer-email" class="block text-sm font-bold text-slate-700 mb-3">
                                E-posta <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="freelancer-email" name="email" required
                                class="w-full px-6 py-4 rounded-2xl border-2 border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium"
                                placeholder="ornek@email.com">
                        </div>
                    </div>

                    <!-- Phone and City Row -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="freelancer-phone" class="block text-sm font-bold text-slate-700 mb-3">
                                Telefon <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="freelancer-phone" name="phone" required
                                class="w-full px-6 py-4 rounded-2xl border-2 border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium"
                                placeholder="0555 123 45 67">
                        </div>
                        <div>
                            <label for="freelancer-city" class="block text-sm font-bold text-slate-700 mb-3">
                                Bulunduğunuz Şehir <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="freelancer-city" name="city" required
                                class="w-full px-6 py-4 rounded-2xl border-2 border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium"
                                placeholder="Örn: Antalya">
                        </div>
                    </div>

                    <!-- Experience -->
                    <div>
                        <label for="freelancer-experience" class="block text-sm font-bold text-slate-700 mb-3">
                            Deneyim Yılı <span class="text-red-500">*</span>
                        </label>
                        <select id="freelancer-experience" name="experience" required
                            class="w-full px-6 py-4 rounded-2xl border-2 border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium">
                            <option value="">Seçiniz...</option>
                            <option value="0-1">0-1 yıl</option>
                            <option value="1-3">1-3 yıl</option>
                            <option value="3-5">3-5 yıl</option>
                            <option value="5-10">5-10 yıl</option>
                            <option value="10+">10+ yıl</option>
                        </select>
                    </div>

                    <!-- Specialization -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">
                            Uzmanlık Alanlarınız <span class="text-red-500">*</span>
                        </label>
                        <div class="grid md:grid-cols-3 gap-4">
                            <label
                                class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-brand-300 cursor-pointer transition-all">
                                <input type="checkbox" name="specialization[]" value="mimari"
                                    class="w-5 h-5 text-brand-600 rounded focus:ring-brand-500">
                                <span class="text-slate-700 font-medium">Mimari</span>
                            </label>
                            <label
                                class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-brand-300 cursor-pointer transition-all">
                                <input type="checkbox" name="specialization[]" value="ic-mekan"
                                    class="w-5 h-5 text-brand-600 rounded focus:ring-brand-500">
                                <span class="text-slate-700 font-medium">İç Mekan</span>
                            </label>
                            <label
                                class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-brand-300 cursor-pointer transition-all">
                                <input type="checkbox" name="specialization[]" value="otel"
                                    class="w-5 h-5 text-brand-600 rounded focus:ring-brand-500">
                                <span class="text-slate-700 font-medium">Otel</span>
                            </label>
                            <label
                                class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-brand-300 cursor-pointer transition-all">
                                <input type="checkbox" name="specialization[]" value="emlak"
                                    class="w-5 h-5 text-brand-600 rounded focus:ring-brand-500">
                                <span class="text-slate-700 font-medium">Emlak</span>
                            </label>
                            <label
                                class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-brand-300 cursor-pointer transition-all">
                                <input type="checkbox" name="specialization[]" value="yemek"
                                    class="w-5 h-5 text-brand-600 rounded focus:ring-brand-500">
                                <span class="text-slate-700 font-medium">Yemek</span>
                            </label>
                            <label
                                class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 hover:border-brand-300 cursor-pointer transition-all">
                                <input type="checkbox" name="specialization[]" value="drone"
                                    class="w-5 h-5 text-brand-600 rounded focus:ring-brand-500">
                                <span class="text-slate-700 font-medium">Drone</span>
                            </label>
                        </div>
                    </div>

                    <!-- Portfolio URL -->
                    <div>
                        <label for="freelancer-portfolio" class="block text-sm font-bold text-slate-700 mb-3">
                            Portfolio / Instagram Linki
                        </label>
                        <input type="url" id="freelancer-portfolio" name="portfolio"
                            class="w-full px-6 py-4 rounded-2xl border-2 border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium"
                            placeholder="https://instagram.com/kullaniciadi veya portfolio linki">
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="freelancer-message" class="block text-sm font-bold text-slate-700 mb-3">
                            Kendinizden Bahsedin
                        </label>
                        <textarea id="freelancer-message" name="message" rows="5"
                            class="w-full px-6 py-4 rounded-2xl border-2 border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium resize-none"
                            placeholder="Deneyimleriniz, ekipmanlarınız ve neden ekibimize katılmak istediğiniz hakkında kısaca bilgi verin..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center pt-4">
                        <button type="submit"
                            class="group relative px-16 py-6 bg-brand-600 hover:bg-brand-500 text-white rounded-[2rem] font-black text-xl shadow-[0_20px_50px_rgba(14,165,233,0.4)] transition-all hover:scale-105 active:scale-95 overflow-hidden">
                            <span class="relative z-10">Başvurumu Gönder</span>
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
                            </div>
                        </button>
                        <p class="text-sm text-slate-500 mt-6">
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
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("freelancer-form");
    const successMessage = document.getElementById("freelancer-success");
    
    if (form) {
        form.addEventListener("submit", async function(e) {
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

<?php include __DIR__ . '/../page-footer.php'; ?>