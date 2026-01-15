<?php
/**
 * Portfolio Template
 * Redesigned with Tailwind CSS
 */
$pexelsService = new \Core\PexelsService();
$allPhotos = $pexelsService->getActivePhotos();

// Only fallback to raw Pexels cache if the database lookup returned absolutely nothing 
// (which usually means the table is empty or doesn't exist yet)
if (empty($allPhotos)) {
    // Check if table is truly empty or doesn't exist
    $db = new \DatabaseClient();
    $dbCount = $db->query("SELECT count(*) as total FROM pexels_images");
    $totalInDb = $dbCount[0]['total'] ?? 0;

    if ($totalInDb == 0) {
        $allPhotos = $pexelsService->getPhotos();
    }
}

// Get a random photo for hero background
$heroPhoto = get_random_pexels_photo();
$heroImage = $heroPhoto ? $heroPhoto['src'] : 'https://images.pexels.com/photos/313782/pexels-photo-313782.jpeg';

include __DIR__ . '/../page-header.php';
?>

<!-- Hero Section -->
<section class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-950">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Portfolio Hero"
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
                Seçkin Portfolyo
            </span>
            <h1 class="font-heading font-black text-5xl md:text-8xl text-white mb-8 tracking-tighter drop-shadow-2xl">
                Yaratıcı Çalışmalarımız
            </h1>
            <p class="text-xl md:text-2xl text-slate-400 max-w-2xl mx-auto font-light leading-relaxed">
                Mimari, iç mekan ve otel fotoğrafçılığında <span class="text-white font-medium">estetik ve
                    tekniği</span> buluşturduğumuz seçkin projeler.
            </p>
        </div>
    </div>
</section>

<!-- Portfolio Grid -->
<section class="py-24 bg-white">
    <div class="container mx-auto px-4">

        <?php if (empty($allPhotos)): ?>
            <div class="text-center py-20 bg-slate-50 rounded-2xl border border-slate-100">
                <p class="text-slate-500 text-lg">Şu an için gösterilecek fotoğraf bulunamadı.</p>
            </div>
        <?php else: ?>
            <div class="columns-1 md:columns-2 lg:columns-3 gap-8 space-y-8">
                <?php foreach ($allPhotos as $photo): ?>
                    <div
                        class="break-inside-avoid group relative rounded-4xl overflow-hidden cursor-pointer shadow-2xl hover-lift border border-slate-100">
                        <a href="<?= htmlspecialchars($photo['url']) ?>" target="_blank" rel="noopener" class="block">
                            <img src="<?= htmlspecialchars($photo['src']) ?>" alt="<?= htmlspecialchars($photo['alt']) ?>"
                                loading="lazy"
                                class="w-full h-auto object-cover transform transition-transform duration-1000 group-hover:scale-110">

                            <div
                                class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-end justify-center p-8">
                                <span
                                    class="px-8 py-3 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-2xl font-black text-sm uppercase tracking-widest transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                                    İncele
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- CTA -->
        <div
            class="mt-32 bg-slate-900 rounded-5xl p-12 md:p-20 text-center text-white relative overflow-hidden shadow-2xl group animate-slide-up">
            <div class="absolute inset-0 z-0 opacity-40">
                <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg" alt="CTA BG"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-[2s]">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-900/80 to-slate-900/90 backdrop-blur-sm">
                </div>
            </div>
            <div class="relative z-10">
                <h2 class="font-heading font-black text-4xl md:text-6xl mb-8 tracking-tight">Sizin Mekanınızı Da
                    Güzelleştirelim</h2>
                <p class="text-brand-100 mb-12 text-xl md:text-2xl font-light max-w-3xl mx-auto leading-relaxed">
                    Eşsiz çekimler ve profesyonel sunum için <span class="text-white font-bold">doğru
                        adrestesiniz.</span> Projenizi birlikte hayata geçirelim.
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
</section>

<?php include __DIR__ . '/../page-footer.php'; ?>