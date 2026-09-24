<?php
/**
 * Partial: compact page header used by archive/detail pages.
 *
 * Expects (all optional except $heroTitle):
 *   $heroEyebrow, $heroTitle, $heroLead, $heroImage,
 *   $heroCrumbs  = [['href' => '/x', 'label' => 'X'], ...]  (last item = current page)
 *   $heroActions = raw HTML for the button row
 */
$heroCrumbs = $heroCrumbs ?? [];
?>
<section class="border-b border-line">
    <div class="container-page grid items-center gap-10 py-10 md:py-14 <?= !empty($heroImage) ? 'lg:grid-cols-12' : '' ?>">
        <div class="<?= !empty($heroImage) ? 'lg:col-span-7' : 'max-w-3xl' ?>">
            <?php if ($heroCrumbs): ?>
                <nav aria-label="Konum" class="mb-5 text-sm text-ink-muted">
                    <ol class="flex flex-wrap items-center gap-1.5">
                        <li><a href="/" class="hover:text-ink">Ana sayfa</a></li>
                        <?php foreach ($heroCrumbs as $i => $crumb): ?>
                            <li aria-hidden="true">/</li>
                            <?php if ($i === count($heroCrumbs) - 1): ?>
                                <li aria-current="page" class="text-ink"><?= e($crumb['label']) ?></li>
                            <?php else: ?>
                                <li><a href="<?= e($crumb['href']) ?>" class="hover:text-ink"><?= e($crumb['label']) ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>
            <?php if (!empty($heroEyebrow)): ?>
                <p class="eyebrow mb-3"><?= e($heroEyebrow) ?></p>
            <?php endif; ?>
            <h1 class="h-display"><?= e($heroTitle) ?></h1>
            <?php if (!empty($heroLead)): ?>
                <p class="lead mt-5 max-w-2xl"><?= e($heroLead) ?></p>
            <?php endif; ?>
            <?php if (!empty($heroActions)): ?>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row"><?= $heroActions ?></div>
            <?php endif; ?>
        </div>
        <?php if (!empty($heroImage)): ?>
            <div class="lg:col-span-5">
                <div class="photo-placeholder aspect-[4/3] overflow-hidden rounded-3xl">
                    <img src="<?= e($heroImage) ?>" alt="" class="h-full w-full object-cover" fetchpriority="high">
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php unset($heroEyebrow, $heroTitle, $heroLead, $heroImage, $heroCrumbs, $heroActions); ?>
