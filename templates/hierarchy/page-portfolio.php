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
<section class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-900">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Portfolio Hero"
            class="w-full h-full object-cover opacity-50">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
    </div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <span
            class="inline-block py-1 px-3 rounded-full bg-white/10 border border-white/20 text-white text-sm font-medium tracking-wide mb-6 backdrop-blur-md">
            Portfolio
        </span>
        <h1 class="font-heading font-bold text-5xl md:text-6xl text-white mb-6 tracking-tight">
            Yaratıcı Çalışmalarımız
        </h1>
        <p class="text-lg text-slate-200 max-w-2xl mx-auto font-light leading-relaxed">
            Mimari, iç mekan ve otel fotoğrafçılığında estetik ve tekniği buluşturduğumuz seçkin projeler.
        </p>
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
                        class="break-inside-avoid group relative rounded-2xl overflow-hidden cursor-pointer shadow-sm hover:shadow-xl transition-all duration-500">
                        <a href="<?= htmlspecialchars($photo['url']) ?>" target="_blank" rel="noopener" class="block">
                            <img src="<?= htmlspecialchars($photo['src']) ?>" alt="<?= htmlspecialchars($photo['alt']) ?>"
                                loading="lazy"
                                class="w-full h-auto object-cover transform transition-transform duration-700 group-hover:scale-110">

                            <div
                                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <div
                                    class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                    <span
                                        class="inline-flex px-6 py-3 bg-white text-slate-900 rounded-full font-bold text-sm tracking-wide">
                                        İncele
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- CTA -->
        <div class="mt-24 p-12 bg-slate-900 rounded-3xl text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
                </svg>
            </div>
            <div class="relative z-10">
                <h2 class="font-heading font-bold text-3xl md:text-4xl text-white mb-6">Sizin Mekanınızı Da
                    Güzelleştirelim</h2>
                <p class="text-slate-300 text-lg mb-10 max-w-xl mx-auto">Eşsiz çekimler ve profesyonel sunum için hemen
                    iletişime geçin.</p>
                <a href="/#iletisim"
                    class="inline-flex px-8 py-4 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-bold shadow-lg shadow-brand-500/25 transition-all hover:scale-105">
                    Hemen Teklif Al
                </a>
            </div>
        </div>

    </div>
</section>

<?php include __DIR__ . '/../page-footer.php'; ?>