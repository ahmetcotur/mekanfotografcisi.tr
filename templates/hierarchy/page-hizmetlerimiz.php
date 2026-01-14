<?php
/**
 * Services Archive Page - Modern UI
 */
include __DIR__ . '/../page-header.php';
global $db;

// Fetch all active services
$services = $db->select('services', ['limit' => 50, 'order' => 'name ASC']);
$activeServices = array_filter($services, function ($s) {
    $isActive = $s['is_active'];
    return ($isActive === true || $isActive === 't' || $isActive === 'true' || $isActive === 1 || $isActive === '1');
});

// Service images mapping
$serviceImages = [
    'mimari-fotografcilik' => 'https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg',
    'ic-mekan-fotografciligi' => 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg',
    'otel-fotografciligi' => 'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg',
    'emlak-fotografciligi' => 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg',
    'otel-restoran-fotografciligi' => 'https://images.pexels.com/photos/67468/pexels-photo-67468.jpeg',
];
$defaultImage = 'https://images.pexels.com/photos/7045926/pexels-photo-7045926.jpeg';

$randomPhoto = get_random_pexels_photo();
$heroImage = $randomPhoto ? $randomPhoto['src'] : '/assets/images/hero-bg.jpg';
?>

<!-- Hero Section -->
<section
    class="relative h-[50vh] min-h-[400px] flex items-center justify-center overflow-hidden bg-slate-900 border-b-4 border-brand-500">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Hizmetlerimiz"
            class="w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
    </div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <span
            class="inline-block py-2 px-5 rounded-full bg-brand-600/90 border-2 border-brand-400 text-white text-sm font-black tracking-widest uppercase mb-6 backdrop-blur-xl shadow-2xl shadow-brand-500/50">
            Profesyonel Hizmetler
        </span>
        <h1 class="font-heading font-extrabold text-5xl md:text-6xl text-white mb-6 tracking-tight drop-shadow-2xl">
            Hizmetlerimiz
        </h1>
        <p class="text-xl text-slate-200 max-w-2xl mx-auto font-light leading-relaxed">
            Her mekan türü için özel çözümler sunuyoruz. İşletmenize değer katan profesyonel fotoğrafçılık hizmetleri.
        </p>
    </div>
</section>

<!-- Services Grid -->
<main class="py-32 bg-slate-50">
    <div class="container mx-auto px-4">

        <?php if (empty($activeServices)): ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-lg">Henüz aktif hizmet bulunmuyor.</p>
            </div>
        <?php else: ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php foreach ($activeServices as $service):
                    $serviceName = htmlspecialchars($service['name']);
                    $serviceSlug = htmlspecialchars($service['slug']);
                    $serviceIntro = htmlspecialchars($service['short_intro'] ?? 'Profesyonel fotoğrafçılık hizmeti.');
                    $serviceImage = $serviceImages[$serviceSlug] ?? $defaultImage;
                    ?>
                    <!-- Service Card -->
                    <div
                        class="group relative bg-slate-900 rounded-[2.5rem] h-[500px] overflow-hidden shadow-2xl hover:shadow-brand-500/20 transition-all duration-500">
                        <img src="<?= $serviceImage ?>" alt="<?= $serviceName ?>"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
                        <div
                            class="absolute bottom-0 left-0 w-full p-10 transform translate-y-6 group-hover:translate-y-0 transition-transform duration-500">
                            <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                                <div
                                    class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700">
                                </div>
                            </div>
                            <h3 class="text-3xl font-black text-white mb-4 tracking-tight">
                                <?= $serviceName ?>
                            </h3>
                            <p
                                class="text-slate-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">
                                <?= $serviceIntro ?>
                            </p>
                            <a href="/hizmetlerimiz/<?= $serviceSlug ?>"
                                class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn">
                                Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform">
                                    <path d="M5 12h14" />
                                    <path d="m12 5 7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <!-- CTA Section -->
        <div
            class="mt-24 bg-gradient-to-r from-brand-900 to-slate-900 rounded-3xl p-12 text-center text-white relative overflow-hidden shadow-2xl">
            <div class="absolute inset-0 bg-[url('/assets/images/pattern.svg')] opacity-10"></div>
            <div class="relative z-10">
                <h2 class="font-heading font-bold text-3xl md:text-4xl mb-6">Hangi Hizmet Size Uygun?</h2>
                <p class="text-brand-100 mb-10 text-xl font-light max-w-2xl mx-auto leading-relaxed">
                    Projeniz için en uygun çözümü birlikte belirleyelim. Ücretsiz danışmanlık için hemen iletişime
                    geçin.
                </p>
                <button onclick="openQuoteWizard()"
                    class="inline-flex items-center justify-center px-10 py-4 bg-brand-500 text-white rounded-xl font-bold hover:bg-brand-400 transition-all shadow-lg hover:scale-105">
                    Hemen Teklif Al
                </button>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>