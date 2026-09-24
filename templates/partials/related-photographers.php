<?php
/**
 * Partial: photographers relevant to the current page.
 * Optional: $relatedSpecialty, $relatedCity.
 */
global $db;
$relatedMatch = null;
$relatedPhotographers = find_photographers($db, $relatedSpecialty ?? null, $relatedCity ?? null, 3, $relatedMatch);
if ($relatedPhotographers):
    $relatedHeading = in_array($relatedMatch, ['both', 'city'], true) && !empty($relatedCity)
        ? $relatedCity . ' bölgesindeki fotoğrafçılar'
        : ($relatedMatch === 'specialty' ? 'Bu alanda çalışan fotoğrafçılar' : 'Kolektiften fotoğrafçılar');
    ?>
    <section class="section border-t border-line">
        <div class="container-page">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <h2 class="h-section"><?= e($relatedHeading) ?></h2>
                <a href="/fotografcilar" class="btn btn-outline self-start md:self-auto">Tüm fotoğrafçılar <?= icon('arrow-right', 'h-4 w-4') ?></a>
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($relatedPhotographers as $photographer): ?>
                    <?= photographer_card($photographer) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php unset($relatedSpecialty, $relatedCity, $relatedMatch, $relatedPhotographers, $relatedHeading); ?>
