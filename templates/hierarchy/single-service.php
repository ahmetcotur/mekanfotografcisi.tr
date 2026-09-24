<?php
/**
 * Single service page (/hizmetlerimiz/{slug})
 */
global $db;
$serviceName = $post->title;
$serviceSummary = $post->excerpt;
if (!$serviceSummary) {
    foreach (published_services($db) as $svc) {
        if (mb_strtolower(trim($svc['title'])) === mb_strtolower(trim($serviceName))) {
            $serviceSummary = $svc['excerpt'] ?? '';
        }
    }
}
$serviceSummary = $serviceSummary ?: content_excerpt($post->content) ?: $serviceName . ' için bölgendeki deneyimli fotoğrafçılardan ücretsiz teklif al.';
$pageDescription = $pageDescription ?? $serviceSummary;
include __DIR__ . '/../page-header.php';

$heroImage = $post->getMeta('hero_image') ?: (photo_src(get_random_pexels_photo()) ?: '/assets/images/hero-bg.jpg');
$serviceContent = prepare_page_content(do_shortcode($post->content), $serviceName);
$isRichContent = strpos($serviceContent, 'class=') !== false;

$provinces = [];
try {
    $provinces = $db->select('locations_province', ['is_active' => true, 'order' => 'name ASC', 'limit' => 100]);
} catch (Exception $e) {
    error_log('single-service provinces fetch failed: ' . $e->getMessage());
}

$heroEyebrow = 'Hizmet';
$heroTitle = $serviceName;
$heroLead = $serviceSummary;
$heroCrumbs = [['href' => '/hizmetlerimiz', 'label' => 'Hizmetler'], ['href' => '', 'label' => $serviceName]];
$heroActions = '<button type="button" onclick=\'openQuoteWizard(' . e(json_encode($post->slug)) . ')\' class="btn btn-primary btn-lg">Bu hizmet için teklif al ' . icon('arrow-right', 'h-4 w-4') . '</button>'
    . '<a href="/fotografcilar?uzmanlik=' . e(service_specialty($post->slug)) . '" class="btn btn-outline btn-lg">Fotoğrafçıları gör</a>';
?>

<main id="main">
    <?php include __DIR__ . '/../partials/page-hero.php'; ?>

    <section class="section pt-10 md:pt-14">
        <div class="container-page grid gap-10 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-8">
                <?php if ($serviceContent): ?>
                    <div class="<?= $isRichContent ? '' : 'prose prose-lg max-w-none' ?>">
                        <?= $serviceContent ?>
                    </div>
                <?php endif; ?>

                <div class="mt-12">
                    <h2 class="h-section">Süreç nasıl işler?</h2>
                    <ol class="mt-6 grid gap-4 md:grid-cols-3">
                        <?php foreach ([
                            ['Talep', 'Mekanını ve beklentini anlat; uygun fotoğrafçılar teklif versin.'],
                            ['Çekim', 'Seçtiğin fotoğrafçı ışığı ve açıları planlayıp çekimi yapar.'],
                            ['Teslim', 'Düzenlenmiş, yayına hazır fotoğraflar birkaç gün içinde teslim edilir.'],
                        ] as $i => [$title, $text]): ?>
                            <li class="card p-5">
                                <span class="text-sm font-semibold text-brand-700"><?= $i + 1 ?>. adım</span>
                                <h3 class="mt-2 font-semibold"><?= e($title) ?></h3>
                                <p class="mt-1 text-sm text-ink-soft"><?= e($text) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>

            <aside class="space-y-6 lg:col-span-4">
                <?php
                $sidebarTitle = $serviceName . ' için teklif al';
                $sidebarService = $post->slug;
                include __DIR__ . '/../partials/quote-sidebar.php';
                ?>
                <?php if (!empty($provinces)): ?>
                    <div class="card p-6">
                        <h2 class="font-semibold">Hizmet verilen iller</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <?php foreach ($provinces as $province): ?>
                                <a href="/hizmet-bolgeleri/<?= e($province['slug']) ?>" class="chip hover:bg-stone-200"><?= e($province['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </section>

    <?php
    if (!empty($post->gallery_folder_id)) {
        echo render_media_gallery($post->gallery_folder_id, 'Portfolyo');
    }
    $relatedSpecialty = service_specialty($post->slug);
    include __DIR__ . '/../partials/related-photographers.php';
    include __DIR__ . '/../partials/services-grid.php';
    $ctaService = $post->slug;
    include __DIR__ . '/../partials/cta-band.php';
    ?>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
