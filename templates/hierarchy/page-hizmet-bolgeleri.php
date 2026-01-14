<?php
/**
 * Locations Archive Template - Modern CTA-Focused Design
 * Optimized for lead generation and quote requests
 */
include __DIR__ . '/../page-header.php';
global $db;

// 1. Fetch Provinces
$allProvinces = $db->select('locations_province', ['limit' => 200, 'order' => 'name ASC']);
$activeProvinces = [];
$provinceMap = [];

foreach ($allProvinces as $p) {
    $isActive = $p['is_active'];
    if ($isActive === true || $isActive === 't' || $isActive === 'true' || $isActive === 1 || $isActive === '1') {
        $activeProvinces[$p['id']] = $p;
        $provinceMap[$p['id']] = $p['name'];
    }
}

// 2. Fetch Districts for Active Provinces
$hierarchy = [];
if (!empty($activeProvinces)) {
    $allDistricts = $db->select('locations_district', ['limit' => 2000, 'order' => 'name ASC']);

    foreach ($allDistricts as $d) {
        $isDistActive = $d['is_active'];
        $isActive = ($isDistActive === true || $isDistActive === 't' || $isDistActive === 'true' || $isDistActive === 1 || $isDistActive === '1');

        if ($isActive && isset($activeProvinces[$d['province_id']])) {
            $provName = $activeProvinces[$d['province_id']]['name'];
            $d['clean_slug'] = '/hizmet-bolgeleri/' . $activeProvinces[$d['province_id']]['slug'] . '/' . $d['slug'];
            $d['location_name'] = $d['name'];
            $hierarchy[$provName][] = $d;
        }
    }
}

foreach ($activeProvinces as $p) {
    if (!isset($hierarchy[$p['name']])) {
        $hierarchy[$p['name']] = [];
    }
}

ksort($hierarchy);

$randomPhoto = get_random_pexels_photo();
$heroImage = $randomPhoto ? $randomPhoto['src'] : '/assets/images/hero-bg.jpg';

$totalProvinces = count($activeProvinces);
$totalDistricts = array_sum(array_map('count', $hierarchy));
?>

<!-- Hero Section -->
<section
    class="relative min-h-[70vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-brand-900">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Türkiye Geneli Hizmet"
            class="w-full h-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/80 to-transparent"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4 text-center py-20">
        <span
            class="inline-block py-2 px-5 rounded-full bg-brand-600/90 border-2 border-brand-400 text-white text-sm font-black tracking-widest uppercase mb-8 backdrop-blur-xl shadow-2xl shadow-brand-500/50 animate-pulse">
            🇹🇷 Türkiye Geneli Hizmet
        </span>

        <h1
            class="font-heading font-black text-5xl md:text-7xl lg:text-8xl text-white mb-8 tracking-tight drop-shadow-2xl">
            Nerede Olursanız Olun,<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-brand-300">Yanınızdayız</span>
        </h1>

        <p class="text-xl md:text-2xl text-slate-200 max-w-4xl mx-auto font-light leading-relaxed mb-12">
            <span class="font-bold text-brand-300"><?= $totalProvinces ?> ilde</span> ve <span
                class="font-bold text-brand-300"><?= $totalDistricts ?> ilçede</span> profesyonel mekan fotoğrafçılığı
            hizmeti veriyoruz.
        </p>

        <!-- Primary CTA -->
        <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
            <button onclick="openQuoteWizard()"
                class="group relative px-12 py-6 bg-brand-600 hover:bg-brand-500 text-white rounded-[2rem] font-black text-xl shadow-[0_20px_50px_rgba(14,165,233,0.4)] transition-all hover:scale-110 active:scale-95 overflow-hidden">
                <span class="relative z-10 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                        <circle cx="12" cy="13" r="3" />
                    </svg>
                    Hemen Fiyat Teklifi Al
                </span>
                <div
                    class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
                </div>
            </button>

            <a href="#bolge-listesi"
                class="px-12 py-6 bg-white/10 hover:bg-white/20 text-white border-2 border-white/30 rounded-[2rem] font-black text-xl backdrop-blur-xl transition-all hover:scale-110 active:scale-95">
                Bölgeleri Görüntüle
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-20 max-w-4xl mx-auto">
            <div
                class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6 hover:bg-white/20 transition-all">
                <div class="text-4xl font-black text-brand-400 mb-2"><?= $totalProvinces ?></div>
                <div class="text-white text-sm font-semibold uppercase tracking-wider">Aktif İl</div>
            </div>
            <div
                class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6 hover:bg-white/20 transition-all">
                <div class="text-4xl font-black text-brand-400 mb-2"><?= $totalDistricts ?></div>
                <div class="text-white text-sm font-semibold uppercase tracking-wider">Aktif İlçe</div>
            </div>
            <div
                class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6 hover:bg-white/20 transition-all">
                <div class="text-4xl font-black text-brand-400 mb-2">24/7</div>
                <div class="text-white text-sm font-semibold uppercase tracking-wider">Destek</div>
            </div>
            <div
                class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl p-6 hover:bg-white/20 transition-all">
                <div class="text-4xl font-black text-brand-400 mb-2">48h</div>
                <div class="text-white text-sm font-semibold uppercase tracking-wider">Hızlı Teslimat</div>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="py-16 bg-white border-b border-slate-100">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="relative">
                <input type="text" id="location-search"
                    placeholder="İl veya ilçe adı yazın... (Örn: Antalya, Muratpaşa)"
                    class="w-full px-8 py-6 pr-16 rounded-[2rem] border-2 border-slate-200 focus:border-brand-500 focus:ring-4 focus:ring-brand-100 transition-all outline-none text-slate-900 font-medium text-lg shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
            </div>
            <p class="text-center text-slate-500 text-sm mt-4">Hizmet verdiğimiz bölgelerde arama yapın</p>
        </div>
    </div>
</section>

<!-- Regions Grid -->
<main class="py-24 bg-slate-50" id="bolge-listesi">
    <div class="container mx-auto px-4">

        <?php if (empty($hierarchy)): ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-lg">Henüz aktif bölge bulunmuyor.</p>
            </div>
        <?php else: ?>

            <div class="text-center mb-16">
                <h2 class="font-heading font-black text-4xl md:text-5xl text-slate-900 mb-4">Hizmet Verdiğimiz Bölgeler</h2>
                <p class="text-slate-600 text-lg max-w-2xl mx-auto">Aşağıdaki illerde profesyonel mekan fotoğrafçılığı
                    hizmeti sunuyoruz. Bölgeniz listede yoksa bizimle iletişime geçin.</p>
            </div>

            <div id="locations-grid" class="grid lg:grid-cols-2 gap-8">
                <?php foreach ($hierarchy as $city => $districts): ?>
                    <!-- City Card -->
                    <div class="location-card bg-white rounded-3xl overflow-hidden shadow-lg border border-slate-100 hover:shadow-2xl hover:border-brand-200 transition-all duration-300 group"
                        data-city="<?= strtolower($city) ?>">
                        <!-- City Header -->
                        <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-8 relative overflow-hidden">
                            <div class="absolute inset-0 opacity-10 bg-[url('/assets/images/pattern.svg')]"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <div>
                                    <h3 class="font-heading font-bold text-3xl text-white mb-2"><?= htmlspecialchars($city) ?>
                                    </h3>
                                    <p class="text-brand-300 text-sm font-semibold"><?= count($districts) ?> ilçede hizmet
                                        veriyoruz</p>
                                </div>
                                <button onclick="openQuoteWizard()"
                                    class="px-6 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-bold text-sm transition-all hover:scale-105 shadow-lg">
                                    Teklif Al
                                </button>
                            </div>
                        </div>

                        <!-- Districts List -->
                        <div class="p-8">
                            <?php if (empty($districts)): ?>
                                <p class="text-sm text-slate-400 italic">Bu şehirde henüz aktif ilçe yok.</p>
                            <?php else: ?>
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <?php foreach ($districts as $page): ?>
                                        <a href="<?= htmlspecialchars($page['clean_slug']) ?>"
                                            class="district-link flex items-center gap-3 p-4 rounded-xl hover:bg-brand-50 transition-all group/item border border-transparent hover:border-brand-200"
                                            data-district="<?= strtolower($page['location_name']) ?>">
                                            <div
                                                class="w-2 h-2 rounded-full bg-brand-200 group-hover/item:bg-brand-500 transition-colors group-hover/item:scale-150">
                                            </div>
                                            <span
                                                class="font-semibold text-slate-700 group-hover/item:text-brand-700 transition-colors flex-1"><?= htmlspecialchars($page['location_name']) ?></span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="text-slate-300 opacity-0 group-hover/item:opacity-100 transition-all transform translate-x-3 group-hover/item:translate-x-0">
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

        <!-- Bottom CTA -->
        <div
            class="mt-24 bg-gradient-to-r from-brand-900 to-slate-900 rounded-[3rem] p-12 md:p-16 text-center text-white relative overflow-hidden shadow-2xl">
            <div class="absolute inset-0 bg-[url('/assets/images/pattern.svg')] opacity-10"></div>
            <div class="relative z-10">
                <h2 class="font-heading font-black text-4xl md:text-6xl mb-6">Bölgeniz Listede Yok mu?</h2>
                <p class="text-brand-100 mb-10 text-xl md:text-2xl font-light max-w-3xl mx-auto leading-relaxed">
                    Türkiye'nin her yerine hizmet veriyoruz. Özel çekim talepleriniz ve proje bazlı çalışmalarınız için
                    bizimle iletişime geçin.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                    <button onclick="openQuoteWizard()"
                        class="px-12 py-6 bg-brand-500 hover:bg-brand-400 text-white rounded-[2rem] font-black text-xl transition-all shadow-2xl hover:scale-105 active:scale-95">
                        Hemen Teklif İste
                    </button>
                    <a href="tel:+905551234567"
                        class="px-12 py-6 bg-white/10 hover:bg-white/20 text-white border-2 border-white/30 rounded-[2rem] font-black text-xl backdrop-blur-xl transition-all hover:scale-105 active:scale-95 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        Hemen Ara
                    </a>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Search Functionality -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("location-search");
        const locationCards = document.querySelectorAll(".location-card");
        const districtLinks = document.querySelectorAll(".district-link");

        if (searchInput) {
            searchInput.addEventListener("input", function () {
                const query = this.value.toLowerCase().trim();

                if (query === "") {
                    // Show all cards
                    locationCards.forEach(card => {
                        card.style.display = "block";
                        const districts = card.querySelectorAll(".district-link");
                        districts.forEach(d => d.style.display = "flex");
                    });
                    return;
                }

                // Filter cards and districts
                locationCards.forEach(card => {
                    const cityName = card.dataset.city;
                    const districts = card.querySelectorAll(".district-link");
                    let hasVisibleDistrict = false;

                    // Check if city matches
                    const cityMatches = cityName.includes(query);

                    // Check districts
                    districts.forEach(district => {
                        const districtName = district.dataset.district;
                        if (districtName.includes(query) || cityMatches) {
                            district.style.display = "flex";
                            hasVisibleDistrict = true;
                        } else {
                            district.style.display = "none";
                        }
                    });

                    // Show/hide card based on matches
                    card.style.display = (cityMatches || hasVisibleDistrict) ? "block" : "none";
                });
            });
        }
    });
</script>

<?php include __DIR__ . '/../partials/services-grid.php'; ?>
<?php include __DIR__ . '/../page-footer.php'; ?>