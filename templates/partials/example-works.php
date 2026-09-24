<?php
/**
 * Partial: strip of example photos (curated Pexels set from the admin).
 */
$examplePhotos = array_values(array_filter(array_map('photo_src', get_random_pexels_photos(4))));
if (!empty($examplePhotos)): ?>
    <section class="section border-t border-line">
        <div class="container-page">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <h2 class="h-section">Örnek çalışmalar</h2>
                <a href="/portfolio" class="btn btn-outline self-start md:self-auto">Portfolyoyu gör <?= icon('arrow-right', 'h-4 w-4') ?></a>
            </div>
            <div class="mt-8 grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
                <?php foreach ($examplePhotos as $src): ?>
                    <div class="photo-placeholder aspect-square overflow-hidden rounded-2xl">
                        <img src="<?= e($src) ?>" alt="Örnek mekan fotoğrafı" loading="lazy" class="h-full w-full object-cover">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
