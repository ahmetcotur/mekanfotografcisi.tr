<?php
/**
 * Locations Archive Template (Hierarchical)
 * Lists all ACTIVE coverage areas Grouped by City -> District
 */
include __DIR__ . '/../page-header.php';
global $db;

// 1. Fetch Provinces
// We fetch all and filter in PHP to be robust against IS_ACTIVE column type variations (bool/string/int)
$allProvinces = $db->select('locations_province', ['limit' => 200, 'order' => 'name ASC']);
$activeProvinces = [];
$provinceMap = [];

foreach ($allProvinces as $p) {
    // Robust active check
    $isActive = $p['is_active'];
    if ($isActive === true || $isActive === 't' || $isActive === 'true' || $isActive === 1 || $isActive === '1') {
        $activeProvinces[$p['id']] = $p;
        $provinceMap[$p['id']] = $p['name'];
    }
}

// 2. Fetch Districts for Active Provinces
$hierarchy = [];
if (!empty($activeProvinces)) {
    // Determine IDs to fetch districts for
    $provIds = array_keys($activeProvinces);

    // Fetch all districts (optimization: could filter by province_id IN (...) but DatabaseClient might not support IN array easily)
    // We'll fetch all districts and filter. The table has ~973 rows, which is manageable.
    $allDistricts = $db->select('locations_district', ['limit' => 2000, 'order' => 'name ASC']);

    foreach ($allDistricts as $d) {
        // Check if district itself is active
        $isDistActive = $d['is_active'];
        $isActive = ($isDistActive === true || $isDistActive === 't' || $isDistActive === 'true' || $isDistActive === 1 || $isDistActive === '1');

        if ($isActive && isset($activeProvinces[$d['province_id']])) {
            $provName = $activeProvinces[$d['province_id']]['name'];

            // Format data for display
            $d['clean_slug'] = '/hizmet-bolgeleri/' . $activeProvinces[$d['province_id']]['slug'] . '/' . $d['slug'];
            $d['location_name'] = $d['name'];

            $hierarchy[$provName][] = $d;
        }
    }
}

// Also add provinces that have no active districts but are active themselves?
// Usually if a province is active, we list it. If it has no districts, we might still want to show the province page.
foreach ($activeProvinces as $p) {
    if (!isset($hierarchy[$p['name']])) {
        // Create an entry for the province itself if needed, or leave empty array if we only list districts under it.
        // The UI loops: foreach ($hierarchy as $city => $districts).
        // If $districts is empty, the card shows "0 Bölge".
        $hierarchy[$p['name']] = [];
    }
}

// Sort cities alphabetically
ksort($hierarchy);

$randomPhoto = get_random_pexels_photo();
$heroImage = $randomPhoto ? $randomPhoto['src'] : '/assets/images/hero-bg.jpg';
?>

<!-- Locations Hero -->
<section
    class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-900 border-b-4 border-brand-500">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Locations Map"
            class="w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
    </div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <span
            class="inline-block py-2 px-5 rounded-full bg-brand-600/90 border-2 border-brand-400 text-white text-sm font-black tracking-widest uppercase mb-6 backdrop-blur-xl shadow-2xl shadow-brand-500/50">
            Kapsama Alanımız
        </span>
        <h1 class="font-heading font-extrabold text-5xl md:text-7xl text-white mb-6 tracking-tight drop-shadow-2xl">
            Hizmet Bölgelerimiz
        </h1>
        <p class="text-xl md:text-2xl text-slate-200 max-w-3xl mx-auto font-light leading-relaxed mb-12">
            Türkiye'nin dört bir yanında, mekanınıza değer katmak için oradayız.
        </p>

        <!-- Quick Stats -->
        <div class="flex flex-wrap justify-center gap-8 mt-12">
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl px-8 py-4">
                <div class="text-4xl font-black text-brand-400 mb-1"><?= count($activeProvinces) ?></div>
                <div class="text-white text-sm font-semibold uppercase tracking-wider">Aktif İl</div>
            </div>
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl px-8 py-4">
                <div class="text-4xl font-black text-brand-400 mb-1"><?= array_sum(array_map('count', $hierarchy)) ?>
                </div>
                <div class="text-white text-sm font-semibold uppercase tracking-wider">Aktif İlçe</div>
            </div>
        </div>
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
                <p class="text-slate-500 text-lg">Henüz aktif bölge bulunmuyor.</p>
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
                            <?php if (empty($districts)): ?>
                                <p class="text-sm text-slate-400 italic">Bu şehirde henüz aktif ilçe yok.</p>
                            <?php else: ?>
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
                            <?php endif; ?>
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