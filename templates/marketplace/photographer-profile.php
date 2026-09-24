<?php
/**
 * Individual Photographer Profile (/fotografcilar/{slug})
 * $freelancerSlug is set by router.php before including this file.
 */
require_once __DIR__ . '/../../includes/database.php';

$db = new DatabaseClient();
$rows = $db->select('freelancer_applications', ['slug' => $freelancerSlug, 'status' => 'approved', 'is_public' => true]);

if (empty($rows)) {
    http_response_code(404);
    $pageTitle = 'Fotoğrafçı Bulunamadı';
    include __DIR__ . '/../page-header.php';
    echo '<main class="pt-40 pb-24 text-center"><h1 class="text-3xl font-heading font-black">Fotoğrafçı bulunamadı</h1><p class="mt-4"><a href="/fotografcilar" class="text-brand-600 font-bold">Tüm fotoğrafçılara dön</a></p></main>';
    include __DIR__ . '/../page-footer.php';
    exit;
}

$profile = $rows[0];
$specs = json_decode($profile['specialization'] ?? '[]', true) ?: [];
$portfolioIds = json_decode($profile['portfolio_media_ids'] ?? '[]', true) ?: [];
$portfolio = [];
foreach ($portfolioIds as $mediaId) {
    $media = $db->select('media', ['id' => $mediaId]);
    if (!empty($media)) {
        $portfolio[] = $media[0];
    }
}

$pageTitle = $profile['name'];
$pageDescription = $profile['bio'] ?: ($profile['city'] . ' bölgesinde bağımsız fotoğrafçı');
include __DIR__ . '/../page-header.php';
?>

<main class="pt-40 pb-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row gap-10 items-start mb-16">
            <div class="w-32 h-32 rounded-3xl bg-slate-100 flex items-center justify-center text-slate-300 font-heading font-black text-5xl shrink-0">
                <?= e(mb_substr($profile['name'], 0, 1)) ?>
            </div>
            <div class="flex-1">
                <h1 class="text-3xl md:text-5xl font-heading font-black text-slate-900 mb-2"><?= e($profile['name']) ?></h1>
                <p class="text-slate-400 font-medium mb-4"><?= e($profile['city']) ?>
                    <?php if ($profile['rating_count'] > 0): ?>
                        &middot; ★ <?= number_format((float) $profile['rating_avg'], 1) ?> (<?= (int) $profile['rating_count'] ?> değerlendirme)
                    <?php endif; ?>
                </p>
                <div class="flex flex-wrap gap-2 mb-6">
                    <?php foreach ($specs as $spec): ?>
                        <span class="px-3 py-1 bg-brand-50 text-brand-600 rounded-full text-xs font-bold"><?= e($spec) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($profile['bio'])): ?>
                    <p class="text-slate-600 leading-relaxed"><?= nl2br(e($profile['bio'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($portfolio)): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-16">
                <?php foreach ($portfolio as $item): ?>
                    <div class="aspect-square rounded-2xl overflow-hidden bg-slate-100">
                        <img src="<?= e($item['public_url']) ?>" alt="<?= e($item['alt'] ?? '') ?>" class="w-full h-full object-cover">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="bg-slate-50 rounded-3xl p-8 md:p-12">
            <h2 class="text-2xl font-heading font-black text-slate-900 mb-6">Bu fotoğrafçıyla iletişime geç</h2>
            <form id="contact-form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="freelancer_id" value="<?= (int) $profile['id'] ?>">
                <input name="name" required placeholder="Ad Soyad" class="px-5 py-3 rounded-2xl border border-slate-200">
                <input name="email" type="email" required placeholder="E-posta" class="px-5 py-3 rounded-2xl border border-slate-200">
                <input name="phone" required placeholder="Telefon" class="px-5 py-3 rounded-2xl border border-slate-200">
                <input name="service" placeholder="Çekim türü (ör. mekan, düğün)" class="px-5 py-3 rounded-2xl border border-slate-200">
                <textarea name="message" required placeholder="Talebinizi kısaca anlatın" rows="4" class="md:col-span-2 px-5 py-3 rounded-2xl border border-slate-200"></textarea>
                <button type="submit" class="md:col-span-2 py-4 rounded-2xl bg-brand-600 text-white font-black uppercase tracking-widest hover:bg-brand-700 transition-all">
                    Talep Gönder
                </button>
                <p id="contact-form-message" class="md:col-span-2 text-sm font-medium"></p>
            </form>
        </div>
    </div>
</main>

<script>
    document.getElementById('contact-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = e.target;
        const msgEl = document.getElementById('contact-form-message');
        const data = Object.fromEntries(new FormData(form).entries());
        const token = localStorage.getItem('mf_token');

        const headers = { 'Content-Type': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;

        fetch('/api/client/contact-freelancer.php', {
            method: 'POST',
            headers,
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    msgEl.textContent = 'Talebiniz iletildi, fotoğrafçı en kısa sürede sizinle iletişime geçecek.';
                    msgEl.className = 'md:col-span-2 text-sm font-medium text-green-600';
                    form.reset();
                } else {
                    msgEl.textContent = res.error || 'Bir hata oluştu, lütfen tekrar deneyin.';
                    msgEl.className = 'md:col-span-2 text-sm font-medium text-red-600';
                }
            })
            .catch(() => {
                msgEl.textContent = 'Bir hata oluştu, lütfen tekrar deneyin.';
                msgEl.className = 'md:col-span-2 text-sm font-medium text-red-600';
            });
    });
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
