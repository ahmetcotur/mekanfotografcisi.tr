<?php
/**
 * Location page (/hizmet-bolgeleri/{il}[/{ilce}])
 */
global $db;
$locationName = $post->getMeta('location_name') ?: preg_replace('/\s+Mekan Fotoğrafçısı$/u', '', $post->title);
$pageDescription = $pageDescription ?? ($post->getMeta('meta_description') ?: null);
include __DIR__ . '/../page-header.php';

$heroImage = $post->getMeta('hero_image') ?: (photo_src(get_random_pexels_photo()) ?: '/assets/images/hero-bg.jpg');
$locationContent = prepare_page_content(do_shortcode($post->content), $post->title);
$isRichContent = strpos($locationContent, 'class=') !== false;

// Province (and district) for breadcrumbs + nearby links.
$province = null;
$districts = [];
$districtName = null;
if (preg_match('#^(?:locations|hizmet-bolgeleri)/([a-z0-9-]+)(?:/([a-z0-9-]+))?$#', $post->slug, $m)) {
    try {
        $rows = $db->select('locations_province', ['slug' => $m[1]]);
        $province = $rows[0] ?? null;
        if ($province) {
            $districts = $db->select('locations_district', ['province_id' => $province['id'], 'is_active' => true, 'order' => 'name ASC', 'limit' => 200]);
            if (!empty($m[2])) {
                foreach ($districts as $d) {
                    if ($d['slug'] === $m[2]) {
                        $districtName = $d['name'];
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log('single-seo_page location lookup failed: ' . $e->getMessage());
    }
}
$cityForMatch = $province['name'] ?? $locationName;
$wizardLocation = $districtName && $province ? $districtName . ', ' . $province['name'] : $locationName;

$faqs = json_decode((string) $post->getMeta('faq_json'), true);
if (is_string($faqs)) {
    $faqs = json_decode($faqs, true);
}
$faqs = is_array($faqs) ? array_values(array_filter($faqs, function ($f) {
    return is_array($f) && !empty($f['question']) && !empty($f['answer']);
})) : [];
if (!$faqs) {
    $faqs = [
        ['question' => $locationName . ' için nasıl fotoğrafçı bulurum?', 'answer' => 'Teklif formunda çekim yerini ve mekan türünü belirt; talebin ' . $locationName . ' bölgesinde çalışan uygun fotoğrafçılara iletilir ve teklifleriyle sana dönerler.'],
        ['question' => 'Teklif almak ücretli mi?', 'answer' => 'Hayır, talep oluşturmak ve teklif almak ücretsizdir. Gelen tekliflerden birini kabul etmek zorunda değilsin.'],
        ['question' => 'Hangi mekanlar çekiliyor?', 'answer' => 'Otel, villa, restoran, ofis, emlak ve ticari alanlar başta olmak üzere her türlü mekan çekimi; gerekirse drone ile havadan çekim.'],
    ];
}

$heroEyebrow = $locationName;
$heroTitle = $post->getMeta('h1') ?: $post->title;
$heroLead = $post->excerpt ?: $locationName . ' bölgesinde otel, villa, restoran ve ticari alanlar için profesyonel mekan fotoğrafçıları. Talebini ilet, ücretsiz teklif al.';
$heroCrumbs = [['href' => '/hizmet-bolgeleri', 'label' => 'Bölgeler']];
if ($province && $districtName) {
    $heroCrumbs[] = ['href' => '/hizmet-bolgeleri/' . $province['slug'], 'label' => $province['name']];
}
$heroCrumbs[] = ['href' => '', 'label' => $locationName];
$heroActions = '<button type="button" onclick=\'openQuoteWizard(null, ' . e(json_encode($wizardLocation)) . ')\' class="btn btn-primary btn-lg">' . e($locationName) . ' için teklif al ' . icon('arrow-right', 'h-4 w-4') . '</button>'
    . '<a href="/fotografcilar?bolge=' . e(rawurlencode($cityForMatch)) . '" class="btn btn-outline btn-lg">Bölgedeki fotoğrafçılar</a>';
?>

<main id="main">
    <?php include __DIR__ . '/../partials/page-hero.php'; ?>

    <section class="section pt-10 md:pt-14">
        <div class="container-page grid gap-10 lg:grid-cols-12">
            <div class="min-w-0 space-y-12 lg:col-span-8">
                <?php if ($locationContent): ?>
                    <div class="<?= $isRichContent ? '' : 'prose prose-lg max-w-none' ?>">
                        <?= $locationContent ?>
                    </div>
                <?php endif; ?>

                <div>
                    <h2 class="h-section"><?= e($locationName) ?> için sık sorulanlar</h2>
                    <div class="mt-6 divide-y divide-line border-y border-line">
                        <?php foreach ($faqs as $faq): ?>
                            <details class="group py-5">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold [&::-webkit-details-marker]:hidden">
                                    <?= e($faq['question']) ?>
                                    <span class="text-ink-muted transition group-open:rotate-180"><?= icon('chevron-down') ?></span>
                                </summary>
                                <p class="mt-3 text-ink-soft"><?= e($faq['answer']) ?></p>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($province && $districts): ?>
                    <div>
                        <h2 class="h-section"><?= e($province['name']) ?> ilçeleri</h2>
                        <div class="mt-6 flex flex-wrap gap-2">
                            <?php foreach ($districts as $d): ?>
                                <a href="/hizmet-bolgeleri/<?= e($province['slug'] . '/' . $d['slug']) ?>"
                                    class="rounded-full border px-4 py-2 text-sm font-medium transition <?= $d['name'] === $districtName ? 'border-ink bg-ink text-white' : 'border-line bg-white hover:border-stone-300' ?>">
                                    <?= e($d['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="lg:col-span-4">
                <?php
                $sidebarTitle = $locationName . ' için teklif al';
                $sidebarLocation = $wizardLocation;
                include __DIR__ . '/../partials/quote-sidebar.php';
                ?>
            </aside>
        </div>
    </section>

    <?php
    if (!empty($post->gallery_folder_id)) {
        echo render_media_gallery($post->gallery_folder_id, 'Portfolyo');
    }
    $relatedCity = $cityForMatch;
    include __DIR__ . '/../partials/related-photographers.php';
    include __DIR__ . '/../partials/example-works.php';
    $ctaTitle = $locationName . ' mekanını çektirmeye hazır mısın?';
    include __DIR__ . '/../partials/cta-band.php';
    ?>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
