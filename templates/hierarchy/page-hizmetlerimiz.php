<?php
/**
 * Services archive (/hizmetlerimiz)
 */
$pageTitle = 'Hizmetler';
$pageDescription = 'Otel, villa, restoran, ofis ve daha fazlası: mekan fotoğrafçılığı kategorileri ve her biri için uygun fotoğrafçıdan teklif alma.';
include __DIR__ . '/../page-header.php';
global $db;

$activeServices = published_services($db);

$servicePhotos = array_map('photo_src', get_random_pexels_photos(count($activeServices) ?: 1));
$defaultImage = '/assets/images/hero-bg.jpg';

$heroEyebrow = 'Hizmetler';
$heroTitle = 'Her mekan için doğru uzman';
$heroLead = 'Çektirmek istediğin mekanın türünü seç; o alanda deneyimli fotoğrafçılardan ücretsiz teklif al.';
$heroCrumbs = [['href' => '/hizmetlerimiz', 'label' => 'Hizmetler']];
?>

<main id="main">
    <?php include __DIR__ . '/../partials/page-hero.php'; ?>

    <section class="section pt-10 md:pt-14">
        <div class="container-page">
            <?php if (empty($activeServices)): ?>
                <div class="card p-10 text-center text-ink-muted">Henüz yayınlanmış hizmet bulunmuyor.</div>
            <?php else: ?>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($activeServices as $i => $service):
                        $href = '/hizmetlerimiz/' . preg_replace('#^hizmetlerimiz/#', '', $service['slug']);
                        $intro = $service['excerpt'] ?? '';
                        $image = $servicePhotos[$i] ?? '' ?: $defaultImage;
                        ?>
                        <article class="card-link group flex flex-col overflow-hidden">
                            <a href="<?= e($href) ?>" class="photo-placeholder block aspect-[3/2] overflow-hidden" tabindex="-1" aria-hidden="true">
                                <img src="<?= e($image) ?>" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            </a>
                            <div class="flex flex-1 flex-col p-5">
                                <h2 class="h-card"><a href="<?= e($href) ?>" class="hover:text-brand-700"><?= e($service['title']) ?></a></h2>
                                <?php if ($intro): ?>
                                    <p class="mt-2 line-clamp-2 text-sm text-ink-soft"><?= e($intro) ?></p>
                                <?php endif; ?>
                                <div class="mt-auto flex items-center justify-between gap-3 pt-5">
                                    <a href="<?= e($href) ?>" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700">Detaylar <?= icon('arrow-right', 'h-4 w-4') ?></a>
                                    <button type="button" onclick='openQuoteWizard(<?= json_encode($service['slug']) ?>)' class="btn btn-outline btn-sm">Teklif al</button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php
    $ctaTitle = 'Aradığın kategori listede yok mu?';
    $ctaLead = 'Drone, etkinlik ya da özel bir proje — talebini anlat, uygun fotoğrafçıyı biz bulalım.';
    include __DIR__ . '/../partials/cta-band.php';
    ?>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
