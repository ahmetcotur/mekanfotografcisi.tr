<?php
/**
 * Partial: sticky "get a quote" card for detail pages.
 * Optional: $sidebarTitle, $sidebarService, $sidebarLocation.
 */
$sidebarTitle = $sidebarTitle ?? 'Ücretsiz teklif al';
$sidebarService = $sidebarService ?? null;
$sidebarLocation = $sidebarLocation ?? null;
?>
<div class="card p-6 lg:sticky lg:top-[calc(var(--header-h)+1.5rem)]">
    <h2 class="h-card"><?= e($sidebarTitle) ?></h2>
    <p class="mt-2 text-sm text-ink-soft">Talebini ilet, uygun fotoğrafçılar teklifleriyle sana dönsün.</p>
    <button type="button" onclick='openQuoteWizard(<?= json_encode($sidebarService) ?>, <?= json_encode($sidebarLocation) ?>)' class="btn btn-primary mt-5 w-full py-3">
        Teklif al <?= icon('arrow-right', 'h-4 w-4') ?>
    </button>
    <ul class="mt-6 space-y-3 border-t border-line pt-5 text-sm text-ink-soft">
        <li class="flex gap-3"><?= icon('check', 'mt-0.5 h-4 w-4 shrink-0 text-brand-600') ?> Ücretsiz, bağlayıcı değil</li>
        <li class="flex gap-3"><?= icon('check', 'mt-0.5 h-4 w-4 shrink-0 text-brand-600') ?> Başvurusu incelenmiş fotoğrafçılar</li>
        <li class="flex gap-3"><?= icon('check', 'mt-0.5 h-4 w-4 shrink-0 text-brand-600') ?> Portfolyo ve yorumlarla karşılaştır</li>
    </ul>
    <?php if (whatsapp_url() || phone_href()): ?>
        <div class="mt-5 flex gap-2 border-t border-line pt-5">
            <?php if (whatsapp_url()): ?>
                <a href="<?= e(whatsapp_url('Merhaba, mekan çekimi için bilgi almak istiyorum.')) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm flex-1"><?= icon('whatsapp', 'h-4 w-4') ?> WhatsApp</a>
            <?php endif; ?>
            <?php if (phone_href()): ?>
                <a href="<?= e(phone_href()) ?>" class="btn btn-outline btn-sm flex-1"><?= icon('phone', 'h-4 w-4') ?> Ara</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php unset($sidebarTitle, $sidebarService, $sidebarLocation); ?>
