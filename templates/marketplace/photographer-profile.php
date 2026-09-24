<?php
/**
 * Individual Photographer Profile (/fotografcilar/{slug})
 * $freelancerSlug is set by router.php before including this file.
 */
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

$db = new DatabaseClient();
try {
    $rows = $db->select('freelancer_applications', ['slug' => $freelancerSlug, 'status' => 'approved', 'is_public' => true]);
} catch (Exception $e) {
    error_log('Photographer profile fetch failed (migrations pending?): ' . $e->getMessage());
    $rows = [];
}

if (empty($rows)) {
    http_response_code(404);
    $pageTitle = 'Fotoğrafçı bulunamadı';
    $pageRobots = 'noindex, follow';
    $appPage = false;
    include __DIR__ . '/../page-header.php';
    ?>
    <main id="main" class="flex flex-1 items-center">
        <div class="container-page max-w-xl py-20 text-center">
            <h1 class="h-section">Fotoğrafçı bulunamadı</h1>
            <p class="lead mt-4">Bu profil kaldırılmış ya da henüz yayında olmayabilir.</p>
            <a href="/fotografcilar" class="btn btn-dark btn-lg mt-8">Tüm fotoğrafçılar</a>
        </div>
    </main>
    <?php
    include __DIR__ . '/../page-footer.php';
    exit;
}

$profile = $rows[0];
$specs = json_decode($profile['specialization'] ?? '[]', true) ?: [];
$regions = array_filter(array_map(function ($r) {
    return trim(($r['district_name'] ?? '') . ($r['district_name'] ?? '' ? ', ' : '') . ($r['province_name'] ?? ''));
}, json_decode($profile['working_regions'] ?? '[]', true) ?: []));

$mediaUrl = function ($mediaId) use ($db) {
    if (!$mediaId) {
        return null;
    }
    $media = $db->select('media', ['id' => $mediaId]);
    return $media[0]['public_url'] ?? null;
};
$avatarUrl = $mediaUrl($profile['avatar_media_id'] ?? null);

$portfolio = [];
foreach (json_decode($profile['portfolio_media_ids'] ?? '[]', true) ?: [] as $mediaId) {
    $media = $db->select('media', ['id' => $mediaId]);
    if (!empty($media)) {
        $portfolio[] = $media[0];
    }
}

$reviews = [];
try {
    $reviews = $db->query('SELECT rating, comment, created_at FROM reviews WHERE freelancer_id = ? AND is_published = true ORDER BY created_at DESC LIMIT 10', [$profile['id']]);
} catch (Exception $e) {
    error_log('Profile reviews fetch failed: ' . $e->getMessage());
}

$experienceLabel = $profile['experience'] ? $profile['experience'] . ' yıl deneyim' : '';
$ratingCount = (int) ($profile['rating_count'] ?? 0);

$pageTitle = $profile['name'] . ' — ' . $profile['city'] . ' Mekan Fotoğrafçısı';
$pageDescription = $profile['bio'] ?: ($profile['city'] . ' bölgesinde bağımsız mekan fotoğrafçısı.');
include __DIR__ . '/../page-header.php';
?>

<main id="main">
    <section class="border-b border-line">
        <div class="container-page py-10 md:py-14">
            <nav aria-label="Konum" class="mb-6 text-sm text-ink-muted">
                <a href="/fotografcilar" class="inline-flex items-center gap-1 hover:text-ink"><?= icon('arrow-left', 'h-4 w-4') ?> Fotoğrafçılar</a>
            </nav>
            <div class="flex flex-col gap-6 md:flex-row md:items-center">
                <?php if ($avatarUrl): ?>
                    <img src="<?= e($avatarUrl) ?>" alt="" class="h-24 w-24 shrink-0 rounded-full object-cover md:h-28 md:w-28">
                <?php else: ?>
                    <span class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-brand-100 font-display text-4xl font-semibold text-brand-800 md:h-28 md:w-28">
                        <?= e(mb_strtoupper(mb_substr($profile['name'], 0, 1))) ?>
                    </span>
                <?php endif; ?>
                <div class="min-w-0 flex-1">
                    <h1 class="h-display text-4xl lg:text-5xl"><?= e($profile['name']) ?></h1>
                    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-ink-soft">
                        <span class="flex items-center gap-1.5"><?= icon('map-pin', 'h-4 w-4') ?> <?= e($profile['city']) ?></span>
                        <?php if ($ratingCount > 0): ?>
                            <span class="flex items-center gap-1.5"><?= icon('star', 'h-4 w-4 fill-current text-brand-500') ?> <strong class="text-ink"><?= number_format((float) $profile['rating_avg'], 1) ?></strong> (<?= $ratingCount ?> değerlendirme)</span>
                        <?php endif; ?>
                        <?php if ($experienceLabel): ?>
                            <span class="flex items-center gap-1.5"><?= icon('clock', 'h-4 w-4') ?> <?= e($experienceLabel) ?></span>
                        <?php endif; ?>
                        <span class="flex items-center gap-1.5 text-emerald-700"><?= icon('shield', 'h-4 w-4') ?> Onaylı üye</span>
                    </div>
                    <?php if ($specs): ?>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <?php foreach ($specs as $spec): ?>
                                <a href="/fotografcilar?uzmanlik=<?= e($spec) ?>" class="chip-brand"><?= e(specialty_label($spec)) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="#iletisim" class="btn btn-primary btn-lg md:self-center">İletişime geç</a>
            </div>
        </div>
    </section>

    <section class="section pt-10 md:pt-12">
        <div class="container-page grid gap-10 lg:grid-cols-12">
            <div class="min-w-0 space-y-12 lg:col-span-8">
                <?php if (!empty($profile['bio'])): ?>
                    <div>
                        <h2 class="h-card text-2xl">Hakkında</h2>
                        <p class="mt-4 leading-relaxed text-ink-soft"><?= nl2br(e($profile['bio'])) ?></p>
                    </div>
                <?php endif; ?>

                <div>
                    <h2 class="h-card text-2xl">Portfolyo</h2>
                    <?php if ($portfolio): ?>
                        <div class="mt-5 columns-2 gap-3 md:columns-3 [&>*]:mb-3">
                            <?php foreach ($portfolio as $item): ?>
                                <a href="<?= e($item['public_url']) ?>" data-lightbox="profile" data-title="<?= e($item['alt'] ?? '') ?>" class="photo-placeholder group block break-inside-avoid overflow-hidden rounded-xl">
                                    <img src="<?= e($item['public_url']) ?>" alt="<?= e($item['alt'] ?? ($profile['name'] . ' portfolyo')) ?>" loading="lazy" class="h-auto w-full transition duration-500 group-hover:scale-[1.03]">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="mt-4 text-sm text-ink-muted">Bu fotoğrafçı henüz portfolyo yüklemedi.</p>
                    <?php endif; ?>
                </div>

                <?php if ($regions): ?>
                    <div>
                        <h2 class="h-card text-2xl">Çalıştığı bölgeler</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <?php foreach ($regions as $region): ?>
                                <span class="chip"><?= e($region) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <h2 class="h-card text-2xl">Değerlendirmeler</h2>
                    <?php if ($reviews): ?>
                        <ul class="mt-5 space-y-4">
                            <?php foreach ($reviews as $review): ?>
                                <li class="card p-5">
                                    <div class="flex items-center justify-between gap-4">
                                        <span class="flex text-brand-500" aria-label="<?= (int) $review['rating'] ?> / 5">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?= icon('star', 'h-4 w-4 ' . ($i <= (int) $review['rating'] ? 'fill-current' : 'text-stone-300')) ?>
                                            <?php endfor; ?>
                                        </span>
                                        <time class="text-xs text-ink-muted"><?= e(date('d.m.Y', strtotime($review['created_at']))) ?></time>
                                    </div>
                                    <?php if (!empty($review['comment'])): ?>
                                        <p class="mt-3 text-sm leading-relaxed text-ink-soft"><?= nl2br(e($review['comment'])) ?></p>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="mt-4 text-sm text-ink-muted">Henüz değerlendirme yok. Değerlendirmeler, platform üzerinden tamamlanan çekimlerden sonra müşteriler tarafından yazılır.</p>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="lg:col-span-4" id="iletisim">
                <div class="card p-6 lg:sticky lg:top-[calc(var(--header-h)+1.5rem)]">
                    <h2 class="h-card"><?= e(explode(' ', $profile['name'])[0]) ?> ile iletişime geç</h2>
                    <p class="mt-1 text-sm text-ink-muted">Talebin doğrudan fotoğrafçıya iletilir.</p>
                    <form id="contact-form" class="mt-5 space-y-3" novalidate>
                        <input type="hidden" name="freelancer_id" value="<?= (int) $profile['id'] ?>">
                        <div>
                            <label for="cf-name" class="label">Ad soyad</label>
                            <input id="cf-name" name="name" required class="input" autocomplete="name">
                        </div>
                        <div>
                            <label for="cf-email" class="label">E-posta</label>
                            <input id="cf-email" name="email" type="email" required class="input" autocomplete="email">
                        </div>
                        <div>
                            <label for="cf-phone" class="label">Telefon</label>
                            <input id="cf-phone" name="phone" type="tel" required class="input" autocomplete="tel">
                        </div>
                        <div>
                            <label for="cf-service" class="label">Çekim türü <span class="font-normal text-ink-muted">(opsiyonel)</span></label>
                            <input id="cf-service" name="service" class="input" placeholder="Örn: villa, otel, restoran">
                        </div>
                        <div>
                            <label for="cf-message" class="label">Mesajın</label>
                            <textarea id="cf-message" name="message" required rows="4" class="input" placeholder="Mekanın, konumu ve tercih ettiğin tarih…"></textarea>
                        </div>
                        <p id="contact-form-message" class="notice" role="status" hidden></p>
                        <button type="submit" class="btn btn-primary w-full py-3">Talep gönder</button>
                    </form>
                </div>
            </aside>
        </div>
    </section>
</main>

<script src="<?= asset_url('assets/js/lightbox.js') ?>" defer></script>
<script>
    document.getElementById('contact-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = e.target;
        const msgEl = document.getElementById('contact-form-message');
        const btn = form.querySelector('button[type="submit"]');
        const show = (text, ok) => {
            msgEl.textContent = text;
            msgEl.className = 'notice ' + (ok ? 'notice-success' : 'notice-error');
            msgEl.hidden = false;
        };

        const firstInvalid = Array.from(form.querySelectorAll('[required]')).find(f => !f.value.trim() || !f.checkValidity());
        if (firstInvalid) {
            show('Lütfen tüm zorunlu alanları doldurun.', false);
            firstInvalid.focus();
            return;
        }

        const data = Object.fromEntries(new FormData(form).entries());
        let token = null;
        try { token = localStorage.getItem('mf_token'); } catch (err) { }
        const headers = { 'Content-Type': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;

        btn.disabled = true;
        btn.textContent = 'Gönderiliyor…';
        fetch('/api/client/contact-freelancer.php', { method: 'POST', headers, body: JSON.stringify(data) })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    show('Talebin iletildi. Fotoğrafçı en kısa sürede seninle iletişime geçecek.', true);
                    form.reset();
                } else {
                    show(res.error || 'Bir hata oluştu, lütfen tekrar dene.', false);
                }
            })
            .catch(() => show('Bağlantı hatası, lütfen tekrar dene.', false))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Talep gönder';
            });
    });
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
