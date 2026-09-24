<?php
/**
 * Partial: right-hand panel on login/registration pages.
 * Expects $asideTitle and $asidePoints (list of strings).
 */
?>
<aside class="relative hidden overflow-hidden bg-ink p-12 text-white lg:sticky lg:top-[var(--header-h)] lg:flex lg:h-[calc(100dvh-var(--header-h))] lg:flex-col lg:justify-end">
    <img src="/assets/images/hero-bg.jpg" alt="" class="absolute inset-0 h-full w-full object-cover opacity-35">
    <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/70 to-transparent"></div>
    <div class="relative max-w-md">
        <h2 class="font-display text-3xl font-semibold leading-tight"><?= e($asideTitle) ?></h2>
        <ul class="mt-6 space-y-3 text-stone-200">
            <?php foreach ($asidePoints as $point): ?>
                <li class="flex gap-3"><?= icon('check', 'mt-0.5 h-5 w-5 shrink-0 text-brand-300') ?> <?= e($point) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</aside>
<?php unset($asideTitle, $asidePoints); ?>
