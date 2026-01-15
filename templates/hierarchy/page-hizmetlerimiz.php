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
<section class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-950">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Hizmetlerimiz"
            class="w-full h-full object-cover opacity-30 animate-pulse-subtle">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
        <div
            class="absolute inset-0 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:40px_40px] opacity-10">
        </div>
    </div>

    <div class="relative z-10 container mx-auto px-4 text-center">
        <div class="animate-slide-up">
            <span
                class="inline-block px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-[10px] font-black tracking-[0.2em] uppercase mb-8 backdrop-blur-xl">
                Profesyonel Çözümler
            </span>
            <h1 class="font-heading font-black text-5xl md:text-8xl text-white mb-8 tracking-tighter drop-shadow-2xl">
                Hizmetlerimiz
            </h1>
            <p class="text-xl md:text-2xl text-slate-400 max-w-2xl mx-auto font-light leading-relaxed">
                Her mekanın kendine has bir dili vardır. <span class="text-white font-medium">Biz o dili
                    görselleştiriyoruz.</span>
            </p>
        </div>
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
                    <div class="group relative bg-slate-900 rounded-5xl h-[500px] overflow-hidden shadow-2xl hover-lift">
                        <img src="<?= $serviceImage ?>" alt="<?= $serviceName ?>"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-60">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/40 to-transparent"></div>

                        <div
                            class="absolute inset-0 p-10 flex flex-col justify-end transform translate-y-8 group-hover:translate-y-0 transition-transform duration-500">
                            <div class="glass-panel p-8 rounded-4xl border-white/10 backdrop-blur-md">
                                <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                                    <div
                                        class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700">
                                    </div>
                                </div>
                                <h3 class="text-3xl font-black text-white mb-4 tracking-tight"><?= $serviceName ?></h3>
                                <p
                                    class="text-slate-200 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">
                                    <?= $serviceIntro ?></p>
                                <a href="/hizmetlerimiz/<?= $serviceSlug ?>"
                                    class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn border border-white/20 px-6 py-3 rounded-full hover:bg-white hover:text-brand-900 transition-all">
                                    Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                        stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform">
                                        <path d="M5 12h14" />
                                        <path d="m12 5 7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <!-- CTA Section -->
        <div
            class="mt-24 bg-slate-900 rounded-5xl p-12 md:p-20 text-center text-white relative overflow-hidden shadow-2xl group animate-slide-up">
            <div class="absolute inset-0 z-0 opacity-40">
                <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg" alt="CTA BG"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-[2s]">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-900/80 to-slate-900/90 backdrop-blur-sm">
                </div>
            </div>
            <div class="relative z-10">
                <h2 class="font-heading font-black text-4xl md:text-6xl mb-8 tracking-tight">Hangi Hizmet Size Uygun?
                </h2>
                <p class="text-brand-100 mb-12 text-xl md:text-2xl font-light max-w-3xl mx-auto leading-relaxed">
                    Projeniz için en uygun çözümü birlikte belirleyelim. <span class="text-white font-bold">Ücretsiz
                        danışmanlık</span> için hemen iletişime geçin.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <button onclick="openQuoteWizard()"
                        class="px-12 py-6 bg-white text-slate-900 rounded-3xl font-black text-xl hover:bg-brand-50 transition-all hover:scale-105 active:scale-95 shadow-2xl">
                        Hemen Teklif Al
                    </button>
                    <a href="tel:<?= get_setting('phone_url') ?>"
                        class="px-12 py-6 bg-brand-600/20 backdrop-blur-md border border-brand-500/30 text-white rounded-3xl font-black text-xl hover:bg-brand-600/40 transition-all hover:scale-105 active:scale-95 flex items-center justify-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        Bizi Arayın
                    </a>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>