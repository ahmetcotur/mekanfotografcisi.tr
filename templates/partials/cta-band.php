<?php
/**
 * Partial: closing call-to-action with both marketplace paths.
 * Optional: $ctaTitle, $ctaLead, $ctaService (pre-selects the quote wizard).
 */
$ctaTitle = $ctaTitle ?? 'Mekanını çektirmeye hazır mısın?';
$ctaLead = $ctaLead ?? 'Talebini birkaç dakikada ilet; bölgendeki uygun fotoğrafçılar teklifleriyle sana dönsün. Ücretsiz ve bağlayıcı değil.';
$ctaService = $ctaService ?? null;
?>
<section class="section">
    <div class="container-page">
        <div class="grid overflow-hidden rounded-3xl bg-ink text-white lg:grid-cols-5">
            <div class="p-8 md:p-12 lg:col-span-3">
                <h2 class="h-section text-white"><?= e($ctaTitle) ?></h2>
                <p class="mt-4 max-w-xl text-stone-300"><?= e($ctaLead) ?></p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <button type="button" onclick='openQuoteWizard(<?= json_encode($ctaService) ?>)' class="btn btn-primary btn-lg">
                        Ücretsiz teklif al <?= icon('arrow-right', 'h-4 w-4') ?>
                    </button>
                    <a href="/fotografcilar" class="btn btn-outline-light btn-lg">Fotoğrafçıları incele</a>
                </div>
            </div>
            <div class="border-t border-white/10 bg-white/5 p-8 md:p-12 lg:col-span-2 lg:border-l lg:border-t-0">
                <p class="eyebrow-light">Fotoğrafçı mısın?</p>
                <p class="mt-3 text-stone-300">Kolektife ücretsiz katıl, bölgendeki açık çekim taleplerini gör ve dilediğini üstlen.</p>
                <a href="/kayit/fotografci" class="mt-6 inline-flex items-center gap-2 font-semibold text-white underline-offset-4 hover:underline">
                    Fotoğrafçı olarak katıl <?= icon('arrow-right', 'h-4 w-4') ?>
                </a>
            </div>
        </div>
    </div>
</section>
<?php unset($ctaTitle, $ctaLead, $ctaService); ?>
