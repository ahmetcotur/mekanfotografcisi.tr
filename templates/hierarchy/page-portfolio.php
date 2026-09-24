<?php
/**
 * Portfolio (/portfolio) - curated photo set managed in the admin (Pexels images).
 */
$pexelsService = new \Core\PexelsService();
$allPhotos = $pexelsService->getActivePhotos();

// Only fall back to the raw Pexels cache when the curated table is empty or
// missing (getActivePhotos() returns [] on a query error too).
if (empty($allPhotos)) {
    $totalInDb = 0;
    try {
        $dbCount = (new \DatabaseClient())->query("SELECT count(*) as total FROM pexels_images");
        $totalInDb = $dbCount[0]['total'] ?? 0;
    } catch (Exception $e) {
        error_log('Portfolio: pexels_images count failed: ' . $e->getMessage());
    }
    if ($totalInDb == 0) {
        $allPhotos = $pexelsService->getPhotos();
    }
}

// Shuffle so photos from the same shoot don't clump together; cap for speed.
if (!empty($allPhotos)) {
    shuffle($allPhotos);
    $allPhotos = array_slice($allPhotos, 0, 45);
}

$pageTitle = 'Portfolyo';
$pageDescription = 'Mimari, iç mekan, otel ve restoran çekimlerinden seçkiler.';
include __DIR__ . '/../page-header.php';

$heroEyebrow = 'Portfolyo';
$heroTitle = 'Mekanlar, en iyi ışığında';
$heroLead = 'Mimari, iç mekan, otel ve restoran çekimlerinden bir seçki.';
$heroCrumbs = [['href' => '/portfolio', 'label' => 'Portfolyo']];
$heroActions = '<button type="button" onclick="openQuoteWizard()" class="btn btn-primary btn-lg">Mekanın için teklif al ' . icon('arrow-right', 'h-4 w-4') . '</button>';
?>

<main id="main">
    <?php include __DIR__ . '/../partials/page-hero.php'; ?>

    <section class="section pt-10 md:pt-14">
        <div class="container-page">
            <?php if (empty($allPhotos)): ?>
                <div class="card p-10 text-center text-ink-muted">Şu an gösterilecek fotoğraf yok.</div>
            <?php else: ?>
                <div class="columns-2 gap-3 md:columns-3 md:gap-4 [&>*]:mb-3 md:[&>*]:mb-4">
                    <?php foreach ($allPhotos as $photo):
                        $src = photo_src($photo);
                        if (!$src) {
                            continue;
                        }
                        ?>
                        <a href="<?= e($src) ?>" data-lightbox="portfolio" data-title="<?= e($photo['alt'] ?? '') ?>"
                            class="photo-placeholder group block break-inside-avoid overflow-hidden rounded-2xl">
                            <img src="<?= e($src) ?>" alt="<?= e($photo['alt'] ?? 'Mekan fotoğrafı') ?>" loading="lazy"
                                class="h-auto w-full transition duration-500 group-hover:scale-[1.03]">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include __DIR__ . '/../partials/cta-band.php'; ?>
</main>

<script src="<?= asset_url('assets/js/lightbox.js') ?>" defer></script>

<?php include __DIR__ . '/../page-footer.php'; ?>
