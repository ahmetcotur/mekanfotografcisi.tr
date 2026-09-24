<?php
/**
 * Locations archive (/hizmet-bolgeleri)
 */
$pageTitle = 'Hizmet Bölgeleri';
$pageDescription = 'Mekan fotoğrafçılarımızın hizmet verdiği il ve ilçeler. Bölgeni seç, yerel fotoğrafçılardan ücretsiz teklif al.';
include __DIR__ . '/../page-header.php';
global $db;

$isTrue = function ($v) {
    return $v === true || $v === 't' || $v === 'true' || $v === 1 || $v === '1';
};

$activeProvinces = [];
foreach ($db->select('locations_province', ['limit' => 200, 'order' => 'name ASC']) as $p) {
    if ($isTrue($p['is_active'])) {
        $activeProvinces[$p['id']] = $p + ['districts' => []];
    }
}
if ($activeProvinces) {
    foreach ($db->select('locations_district', ['limit' => 2000, 'order' => 'name ASC']) as $d) {
        if ($isTrue($d['is_active']) && isset($activeProvinces[$d['province_id']])) {
            $activeProvinces[$d['province_id']]['districts'][] = $d;
        }
    }
}
$totalDistricts = array_sum(array_map(function ($p) {
    return count($p['districts']);
}, $activeProvinces));

$heroEyebrow = 'Bölgeler';
$heroTitle = 'Hizmet verdiğimiz bölgeler';
$heroLead = count($activeProvinces) . ' il ve ' . $totalDistricts . ' ilçede mekan fotoğrafçılarıyla çalışıyoruz. İlini ya da ilçeni seç.';
$heroCrumbs = [['href' => '/hizmet-bolgeleri', 'label' => 'Bölgeler']];
?>

<main id="main">
    <?php include __DIR__ . '/../partials/page-hero.php'; ?>

    <section class="section pt-10 md:pt-14" id="bolge-listesi">
        <div class="container-page">
            <?php if (empty($activeProvinces)): ?>
                <div class="card p-10 text-center text-ink-muted">Henüz aktif bölge bulunmuyor.</div>
            <?php else: ?>
                <div class="max-w-xl">
                    <label for="location-search" class="sr-only">İl veya ilçe ara</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted"><?= icon('search') ?></span>
                        <input type="search" id="location-search" class="input pl-12" placeholder="İl veya ilçe ara (örn. Belek, Bodrum)" autocomplete="off">
                    </div>
                    <p id="location-empty" class="mt-4 text-sm text-ink-muted" hidden>
                        Eşleşen bölge bulunamadı. Yine de <button type="button" onclick="openQuoteWizard(null, document.getElementById('location-search').value)" class="font-semibold text-brand-700 underline underline-offset-2">teklif iste</button> — uygun fotoğrafçıyı biz bulalım.
                    </p>
                </div>

                <div class="mt-8 grid items-start gap-5 lg:grid-cols-2">
                    <?php foreach ($activeProvinces as $province): ?>
                        <div class="location-card card p-6" data-city="<?= e(mb_strtolower($province['name'])) ?>">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="h-card text-2xl"><a href="/hizmet-bolgeleri/<?= e($province['slug']) ?>" class="hover:text-brand-700"><?= e($province['name']) ?></a></h2>
                                    <p class="mt-1 text-sm text-ink-muted"><?= count($province['districts']) ?> ilçe</p>
                                </div>
                                <button type="button" onclick='openQuoteWizard(null, <?= e(json_encode($province['name'])) ?>)' class="btn btn-outline btn-sm">Teklif al</button>
                            </div>
                            <?php if ($province['districts']): ?>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <?php foreach ($province['districts'] as $d): ?>
                                        <a href="/hizmet-bolgeleri/<?= e($province['slug'] . '/' . $d['slug']) ?>" class="district-link chip hover:bg-stone-200" data-district="<?= e(mb_strtolower($d['name'])) ?>"><?= e($d['name']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php
    $ctaTitle = 'Bölgen listede yok mu?';
    $ctaLead = 'Kolektif her ay yeni bölgelere açılıyor. Talebini ilet, bölgene en yakın fotoğrafçıyı birlikte bulalım.';
    include __DIR__ . '/../partials/cta-band.php';
    ?>
</main>

<script>
    (function () {
        const input = document.getElementById('location-search');
        if (!input) return;
        const cards = document.querySelectorAll('.location-card');
        const empty = document.getElementById('location-empty');
        const norm = s => s.toLocaleLowerCase('tr').normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/ı/g, 'i');

        input.addEventListener('input', function () {
            const q = norm(this.value.trim());
            let visibleCards = 0;
            cards.forEach(card => {
                const cityMatch = !q || norm(card.dataset.city).includes(q);
                let anyDistrict = false;
                card.querySelectorAll('.district-link').forEach(link => {
                    const show = cityMatch || norm(link.dataset.district).includes(q);
                    link.hidden = !show;
                    anyDistrict = anyDistrict || show;
                });
                card.hidden = !(cityMatch || anyDistrict);
                if (!card.hidden) visibleCards++;
            });
            empty.hidden = visibleCards > 0;
        });
    })();
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
