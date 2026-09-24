<?php
/**
 * Partial: links to the other services (skips the current post).
 */
global $db;

$otherServices = array_filter(published_services($db), function ($svc) use ($post) {
    return !isset($post->title) || mb_strtolower(trim($svc['title'])) !== mb_strtolower(trim($post->title));
});

if (!empty($otherServices)): ?>
    <section class="section border-t border-line bg-white">
        <div class="container-page">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <h2 class="h-section">Diğer hizmetler</h2>
                <a href="/hizmetlerimiz" class="btn btn-outline self-start md:self-auto">Tüm hizmetler <?= icon('arrow-right', 'h-4 w-4') ?></a>
            </div>
            <ul class="mt-8 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                <?php foreach ($otherServices as $svc): ?>
                    <li>
                        <a href="/hizmetlerimiz/<?= e(preg_replace('#^hizmetlerimiz/#', '', $svc['slug'])) ?>"
                            class="flex items-center justify-between gap-3 rounded-xl border border-line px-4 py-3 text-sm font-medium transition hover:border-stone-300 hover:bg-stone-50">
                            <?= e($svc['title']) ?> <?= icon('chevron-right', 'h-4 w-4 shrink-0 text-ink-muted') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>
