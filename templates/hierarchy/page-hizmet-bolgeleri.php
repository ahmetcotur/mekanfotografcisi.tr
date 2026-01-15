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
<section class="relative min-h-[80vh] flex items-center justify-center overflow-hidden bg-slate-950">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Türkiye Geneli Hizmet"
            class="w-full h-full object-cover opacity-20 animate-pulse-subtle">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:30px_30px]"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4 text-center py-24 animate-slide-up">
        <span class="inline-block px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-[10px] font-black tracking-[0.2em] uppercase mb-8 backdrop-blur-xl">
            🇹🇷 Hizmet Ağımız
        </span>

        <h1 class="font-heading font-black text-5xl md:text-8xl text-white mb-8 tracking-tighter drop-shadow-2xl">
            Nerede Olursanız Olun,<br>
            <span class="text-gradient">Yanınızdayız</span>
        </h1>

        <p class="text-xl md:text-2xl text-slate-400 max-w-4xl mx-auto font-light leading-relaxed mb-16">
            <span class="text-white font-bold"><?= $totalProvinces ?> ilde</span> ve <span class="text-white font-bold"><?= $totalDistricts ?> ilçede</span> mekanlarınızın vizyonunu dijital dünyaya en profesyonel şekilde yansıtıyoruz.
        </p>

        <!-- Primary CTA -->
        <div class="flex flex-col sm:flex-row gap-8 justify-center items-center">
            <button onclick="openQuoteWizard()" class="group relative px-12 py-6 bg-brand-600 hover:bg-brand-500 text-white rounded-3xl font-black text-xl shadow-[0_20px_50px_rgba(14,165,233,0.3)] transition-all hover:scale-110 active:scale-95 overflow-hidden">
                <span class="relative z-10 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                    Hemen Fiyat Teklifi Al
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            </button>

            <a href="#bolge-listesi" class="px-12 py-6 bg-white/5 hover:bg-white/10 text-white border border-white/20 rounded-3xl font-black text-xl backdrop-blur-xl transition-all hover:scale-110 active:scale-95">
                Bölgeleri Görüntüle
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-24 max-w-4xl mx-auto">
            <div class="glass-panel p-8 rounded-4xl border-white/5 hover:border-white/10 transition-all hover-lift">
                <div class="text-4xl font-black text-brand-400 mb-2"><?= $totalProvinces ?></div>
                <div class="text-white text-[10px] font-black uppercase tracking-widest opacity-60">Aktif İl</div>
            </div>
            <div class="glass-panel p-8 rounded-4xl border-white/5 hover:border-white/10 transition-all hover-lift">
                <div class="text-4xl font-black text-brand-400 mb-2"><?= $totalDistricts ?></div>
                <div class="text-white text-[10px] font-black uppercase tracking-widest opacity-60">Aktif İlçe</div>
            </div>
            <div class="glass-panel p-8 rounded-4xl border-white/5 hover:border-white/10 transition-all hover-lift">
                <div class="text-4xl font-black text-brand-400 mb-2">24/7</div>
                <div class="text-white text-[10px] font-black uppercase tracking-widest opacity-60">Destek</div>
            </div>
            <div class="glass-panel p-8 rounded-4xl border-white/5 hover:border-white/10 transition-all hover-lift">
                <div class="text-4xl font-black text-brand-400 mb-2">48h</div>
                <div class="text-white text-[10px] font-black uppercase tracking-widest opacity-60">Teslimat</div>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="py-20 bg-white border-b border-slate-100 relative z-20 -mt-10">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <div class="relative group">
                <input type="text" id="location-search" placeholder="İl veya ilçe adı yazın... (Örn: Antalya, Belek)"
                    class="w-full px-12 py-8 pr-20 rounded-4xl border-2 border-slate-100 bg-slate-50/50 focus:bg-white focus:border-brand-500 focus:ring-8 focus:ring-brand-100/50 transition-all outline-none text-slate-900 font-bold text-xl shadow-[0_20px_50px_rgba(0,0,0,0.05)] placeholder:text-slate-400">
                <div
                    class="absolute right-8 top-1/2 -translate-y-1/2 w-12 h-12 bg-brand-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-brand-500/30 group-focus-within:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                </div>
            </div>
            <p class="text-center text-slate-400 text-xs font-black uppercase tracking-[0.2em] mt-8 opacity-60">Hizmet
                verdiğimiz bölgelerde anında arama yapın</p>
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
                    <!-- City Card -->
                    <div class="location-card bg-white rounded-5xl overflow-hidden shadow-[0_32px_64px_-20px_rgba(0,0,0,0.06)] border border-slate-100 hover:shadow-2xl hover:border-brand-200 transition-all duration-500 group animate-slide-up"
                        data-city="<?= strtolower($city) ?>">
                        <!-- City Header -->
                        <div class="bg-slate-900 p-10 relative overflow-hidden">
                            <div class="absolute inset-0 opacity-20 bg-gradient-to-br from-brand-900/50 to-slate-950/50"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <div>
                                    <h3
                                        class="font-heading font-black text-4xl text-white mb-2 tracking-tight group-hover:text-brand-400 transition-colors">
                                        <?= htmlspecialchars($city) ?></h3>
                                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest opacity-80">
                                        <?= count($districts) ?> ilçede profesyonel çekim</p>
                                </div>
                                <button onclick="openQuoteWizard()"
                                    class="h-12 w-12 bg-white/10 hover:bg-brand-600 text-white rounded-2xl flex items-center justify-center backdrop-blur-md transition-all group-hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M5 12h14" />
                                        <path d="m12 5 7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Districts List -->
                        <div class="p-10">
                            <?php if (empty($districts)): ?>
                                <p class="text-sm text-slate-400 italic font-medium">Bu şehirde henüz aktif hizmet bölgesi
                                    bulunmamaktadır.</p>
                            <?php else: ?>
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <?php foreach ($districts as $page): ?>
                                        <a href="<?= htmlspecialchars($page['clean_slug']) ?>"
                                            class="district-link flex items-center gap-4 p-5 rounded-3xl bg-slate-50/50 hover:bg-brand-50 transition-all group/item border-2 border-transparent hover:border-brand-100"
                                            data-district="<?= strtolower($page['location_name']) ?>">
                                            <div
                                                class="w-2.5 h-2.5 rounded-full bg-slate-200 group-hover/item:bg-brand-500 transition-all group-hover/item:scale-125">
                                            </div>
                                            <span
                                                class="font-bold text-slate-700 group-hover/item:text-slate-900 transition-colors flex-1"><?= htmlspecialchars($page['location_name']) ?></span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="text-brand-500 opacity-0 group-hover/item:opacity-100 transition-all transform -translate-x-2 group-hover/item:translate-x-0">
                                                <path d="m9 18 6-6-6-6" />
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
            class="mt-24 bg-slate-900 rounded-5xl p-12 md:p-20 text-center text-white relative overflow-hidden shadow-2xl group animate-slide-up">
            <div class="absolute inset-0 z-0 opacity-40">
                <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg" alt="CTA BG"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-[2s]">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-900/80 to-slate-900/90 backdrop-blur-sm">
                </div>
            </div>
            <div class="relative z-10">
                <h2 class="font-heading font-black text-4xl md:text-6xl mb-8 tracking-tight">Bölgeniz Listede Yok mu?
                </h2>
                <p class="text-brand-100 mb-12 text-xl md:text-2xl font-light max-w-3xl mx-auto leading-relaxed">
                    Türkiye'nin her yerine hizmet veriyoruz. <span class="text-white font-bold">Özel çekim
                        talepleriniz</span> ve proje bazlı çalışmalarınız için bizimle iletişime geçin.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <button onclick="openQuoteWizard()"
                        class="px-12 py-6 bg-white text-slate-900 rounded-3xl font-black text-xl hover:bg-brand-50 transition-all hover:scale-105 active:scale-95 shadow-2xl">
                        Hemen Teklif İste
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