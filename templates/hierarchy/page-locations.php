<?php
/**
 * Locations Archive Template (Hierarchical)
 * Lists all available coverage areas Grouped by City -> District
 */
include __DIR__ . '/../page-header.php';
global $db;

// Fetch all published SEO pages from 'posts' table
$seoPages = $db->select('posts', ['post_type' => 'seo_page', 'post_status' => 'publish', 'limit' => 500, 'order' => 'title ASC']);

// Process hierarchy based on slugs or structure
// Assuming slug format: locations/city/district
$hierarchy = [];

foreach ($seoPages as $page) {
    // Add location_name dynamically for display
    $page['location_name'] = str_replace([' Mekan Fotoğrafçısı', ' Fotoğrafçısı'], '', $page['title']);

    // Ensure slug is clean
    $cleanSlug = trim($page['slug'], '/');
    $slugParts = explode('/', $cleanSlug);
    // Parts: 0=>locations, 1=>city, 2=>district (optional)

    if (count($slugParts) >= 3) {
        $city = ucfirst($slugParts[1]);
        $page['clean_slug'] = '/' . $cleanSlug; // Ensure absolute path
        $hierarchy[$city][] = $page;
    }
}
// Sort cities
ksort($hierarchy);

$randomPhoto = get_random_pexels_photo();
$heroImage = $randomPhoto ? $randomPhoto['src'] : '/assets/images/hero-bg.jpg';
?>

<!-- Locations Hero -->
<section
    class="relative h-[50vh] min-h-[400px] flex items-center justify-center overflow-hidden bg-slate-900 border-b-4 border-brand-500">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Locations Map"
            class="w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
    </div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <span
            class="inline-block py-1 px-3 rounded-full bg-brand-500/20 text-brand-300 text-sm font-semibold mb-4 backdrop-blur-md border border-brand-500/30">
            Kapsama Alanımız
        </span>
        <h1 class="font-heading font-extrabold text-5xl md:text-6xl text-white mb-6 tracking-tight drop-shadow-2xl">
            Hizmet Bölgelerimiz
        </h1>
        <p class="text-xl text-slate-200 max-w-2xl mx-auto font-light leading-relaxed">
            Türkiye'nin dört bir yanında, mekanınıza değer katmak için oradayız.
        </p>
    </div>
</section>

<!-- Regions Grid -->
<main class="py-24 bg-slate-50 relative">
    <!-- Background Decoration -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-5">
        <div class="absolute top-10 left-10 w-96 h-96 bg-brand-500 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-cyan-500 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">

        <?php if (empty($hierarchy)): ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-lg">Henüz bölge eklenmemiş.</p>
            </div>
        <?php else: ?>

            <div class="grid lg:grid-cols-2 gap-12">
                <?php foreach ($hierarchy as $city => $districts): ?>
                    <!-- City Card -->
                    <div
                        class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-300 group">
                        <!-- City Header -->
                        <div class="bg-slate-900 p-8 relative overflow-hidden">
                            <!-- Abstract City Pattern -->
                            <div class="absolute inset-0 opacity-10 bg-[url('/assets/images/pattern.svg')]"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <h2 class="font-heading font-bold text-3xl text-white">
                                    <?= htmlspecialchars($city) ?>
                                </h2>
                                <span
                                    class="px-4 py-1 bg-white/20 text-white text-sm font-semibold rounded-full backdrop-blur-sm">
                                    <?= count($districts) ?> Bölge
                                </span>
                            </div>
                        </div>

                        <!-- Districts List -->
                        <div class="p-8">
                            <div class="grid sm:grid-cols-2 gap-4">
                                <?php foreach ($districts as $page): ?>
                                    <a href="<?= htmlspecialchars($page['clean_slug']) ?>"
                                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group/item">
                                        <div
                                            class="w-2 h-2 rounded-full bg-brand-200 group-hover/item:bg-brand-500 transition-colors">
                                        </div>
                                        <span class="font-medium text-slate-700 group-hover/item:text-brand-700 transition-colors">
                                            <?= htmlspecialchars($page['location_name']) ?>
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="ml-auto text-slate-300 opacity-0 group-hover/item:opacity-100 transition-all transform translate-x-3 group-hover/item:translate-x-0">
                                            <path d="M5 12h14"></path>
                                            <path d="m12 5 7 7-7 7"></path>
                                        </svg>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <!-- CTA -->
        <div
            class="mt-24 bg-gradient-to-r from-brand-900 to-slate-900 rounded-3xl p-12 text-center text-white relative overflow-hidden shadow-2xl">
            <div class="absolute inset-0 bg-[url('/assets/images/pattern.svg')] opacity-10"></div>
            <div class="relative z-10">
                <h2 class="font-heading font-bold text-3xl md:text-4xl mb-6">Listede Olmayan Bir Yer mi?</h2>
                <p class="text-brand-100 mb-10 text-xl font-light max-w-2xl mx-auto">
                    Türkiye'nin her yerine hizmet veriyoruz. Özel çekim talepleriniz ve proje bazlı çalışmalarınız için
                    bize ulaşın.
                </p>
                <a href="/#iletisim"
                    class="inline-flex items-center justify-center px-10 py-4 bg-brand-500 text-white rounded-xl font-bold hover:bg-brand-400 transition-all shadow-lg hover:scale-105">
                    İletişime Geçin
                </a>
            </div>
        </div>

    </div>
</main>

<?php
// Include Other Services
include __DIR__ . '/../partials/services-grid.php';

include __DIR__ . '/../page-footer.php'; ?>