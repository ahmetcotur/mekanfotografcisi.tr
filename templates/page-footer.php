<?php
$siteName = get_setting('site_title', 'Mekan Fotoğrafçısı');
$appPage = $appPage ?? false;
$footerEmail = get_setting('email_primary') ?: get_setting('email');
$footerPhone = get_setting('phone');
$socials = array_filter([
    'instagram' => get_setting('social_instagram'),
    'facebook' => get_setting('social_facebook'),
    'twitter' => get_setting('social_twitter'),
], function ($url) {
    return $url && $url !== '#';
});
?>

<?php include __DIR__ . '/partials/quote-wizard.php'; ?>

<footer class="mt-auto border-t border-line bg-white">
    <?php if (!$appPage): ?>
        <div class="container-page grid grid-cols-2 gap-x-6 gap-y-10 py-14 md:grid-cols-4 lg:grid-cols-12">
            <div class="col-span-2 md:col-span-4 lg:col-span-3">
                <a href="/" class="font-display text-xl font-semibold"><?= e($siteName) ?></a>
                <p class="mt-3 max-w-sm text-sm leading-relaxed text-ink-muted">
                    Mekanını çektirmek isteyenleri, bölgesindeki doğru fotoğrafçıyla buluşturan kolektif.
                    Otel, villa, restoran, ofis ve daha fazlası için.
                </p>
                <?php if ($socials): ?>
                    <div class="mt-5 flex gap-2">
                        <?php foreach ($socials as $network => $url): ?>
                            <a href="<?= e($url) ?>" target="_blank" rel="noopener"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-line text-ink-soft transition hover:border-stone-300 hover:text-ink"
                                aria-label="<?= e(ucfirst($network)) ?>">
                                <?= icon($network, 'h-4 w-4') ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-2">
                <h2 class="text-sm font-semibold">Mekan sahipleri</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-ink-muted">
                    <li><button type="button" onclick="openQuoteWizard()" class="hover:text-ink">Teklif al</button></li>
                    <li><a href="/fotografcilar" class="hover:text-ink">Fotoğrafçı bul</a></li>
                    <li><a href="/hizmetlerimiz" class="hover:text-ink">Hizmetler</a></li>
                    <li><a href="/kayit/musteri" class="hover:text-ink">Müşteri hesabı</a></li>
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h2 class="text-sm font-semibold">Fotoğrafçılar</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-ink-muted">
                    <li><a href="/kayit/fotografci" class="hover:text-ink">Kolektife katıl</a></li>
                    <li><a href="/nasil-calisir#fotografcilar" class="hover:text-ink">Nasıl çalışır?</a></li>
                    <li><a href="/giris" class="hover:text-ink">Giriş yap</a></li>
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h2 class="text-sm font-semibold">Keşfet</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-ink-muted">
                    <li><a href="/hizmet-bolgeleri" class="hover:text-ink">Bölgeler</a></li>
                    <li><a href="/portfolio" class="hover:text-ink">Portfolyo</a></li>
                    <li><a href="/blog" class="hover:text-ink">Blog</a></li>
                    <li><button type="button" onclick="openInquiryModal()" class="hover:text-ink">Talep sorgula</button></li>
                </ul>
            </div>

            <div class="col-span-2 md:col-span-1 lg:col-span-3">
                <h2 class="text-sm font-semibold">İletişim</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-ink-muted">
                    <?php if ($footerPhone && phone_href()): ?>
                        <li><a href="<?= e(phone_href()) ?>" class="hover:text-ink"><?= e($footerPhone) ?></a></li>
                    <?php endif; ?>
                    <?php if (whatsapp_url()): ?>
                        <li><a href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener" class="hover:text-ink">WhatsApp</a></li>
                    <?php endif; ?>
                    <?php if ($footerEmail): ?>
                        <li><a href="mailto:<?= e($footerEmail) ?>" class="hover:text-ink [overflow-wrap:anywhere]"><?= e($footerEmail) ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="border-t border-line">
        <div class="container-page flex flex-col gap-3 py-6 text-xs text-ink-muted md:flex-row md:items-center md:justify-between">
            <p>&copy; <?= date('Y') ?> <?= e($siteName) ?></p>
            <nav class="flex flex-wrap gap-x-5 gap-y-2" aria-label="Yasal">
                <a href="/gizlilik-politikasi" class="hover:text-ink">Gizlilik</a>
                <a href="/kullanim-sartlari" class="hover:text-ink">Kullanım şartları</a>
                <a href="/cerez-politikasi" class="hover:text-ink">Çerez politikası</a>
            </nav>
        </div>
    </div>
</footer>

<?php if (!$appPage): ?>
    <!-- Mobile quote bar (space on the right is left for the chat launcher) -->
    <div class="fixed inset-x-0 bottom-0 z-[90] border-t border-line bg-paper/95 py-3 pl-4 pr-24 backdrop-blur md:hidden"
        style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
        <button type="button" onclick="openQuoteWizard()" class="btn btn-primary w-full py-3">
            Ücretsiz teklif al <?= icon('arrow-right', 'h-4 w-4') ?>
        </button>
    </div>
<?php endif; ?>

<!-- Quote status lookup -->
<div id="inquiry-modal" class="fixed inset-0 z-[200] flex items-end justify-center p-0 sm:items-center sm:p-4" role="dialog"
    aria-modal="true" aria-labelledby="inquiry-title" hidden>
    <div class="absolute inset-0 bg-ink/50" onclick="closeInquiryModal()"></div>
    <div class="relative w-full max-w-md rounded-t-3xl bg-white p-6 shadow-lift sm:rounded-3xl sm:p-8">
        <button type="button" onclick="closeInquiryModal()" class="absolute right-4 top-4 rounded-full p-2 text-ink-muted hover:bg-stone-100" aria-label="Kapat">
            <?= icon('x') ?>
        </button>
        <h2 id="inquiry-title" class="h-card">Talep sorgula</h2>
        <p class="mt-1 text-sm text-ink-muted">Size iletilen MF-XXXXX formatındaki talep numarasını girin.</p>
        <form class="mt-6 space-y-3" onsubmit="event.preventDefault(); submitInquiry();">
            <label for="inquiry-number" class="sr-only">Talep numarası</label>
            <input type="text" id="inquiry-number" placeholder="Örn: MF-00123" class="input text-center text-lg font-semibold tracking-wide">
            <p id="inquiry-error" class="notice notice-error" hidden></p>
            <button type="submit" id="inquiry-btn" class="btn btn-primary w-full py-3">Sorgula</button>
        </form>
        <div id="inquiry-result" class="mt-6 border-t border-line pt-6" hidden></div>
    </div>
</div>

<?php include __DIR__ . '/partials/cookie-consent.php'; ?>

<script>
    window.LEADS_SITE_KEY = <?= json_encode(getenv('LEADS_SITE_KEY') ?: 'site_mekan_8342') ?>;
    window.LEADS_API_URL = <?= json_encode(getenv('LEADS_API_URL') ?: 'https://lead.ahmetcotur.com/api/leads/form') ?>;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function openInquiryModal() {
        document.getElementById('inquiry-modal').hidden = false;
        document.getElementById('inquiry-result').hidden = true;
        document.getElementById('inquiry-error').hidden = true;
        const input = document.getElementById('inquiry-number');
        input.value = '';
        input.focus();
    }

    function closeInquiryModal() {
        document.getElementById('inquiry-modal').hidden = true;
    }

    function submitInquiry() {
        const number = document.getElementById('inquiry-number').value.trim();
        const errorEl = document.getElementById('inquiry-error');
        const resultEl = document.getElementById('inquiry-result');
        const btn = document.getElementById('inquiry-btn');
        errorEl.hidden = true;
        if (!number) {
            errorEl.textContent = 'Lütfen talep numaranızı girin.';
            errorEl.hidden = false;
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Sorgulanıyor…';

        fetch('/api/quote-status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ quote_number: number })
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    errorEl.textContent = res.message || 'Talep bulunamadı.';
                    errorEl.hidden = false;
                    return;
                }
                resultEl.innerHTML = `
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-ink-muted">Ad</dt><dd class="font-medium">${escapeHtml(res.data.name)}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-ink-muted">Durum</dt><dd><span class="chip-brand">${escapeHtml(res.data.status)}</span></dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-ink-muted">Talep tarihi</dt><dd>${escapeHtml(res.data.date)}</dd></div>
                    </dl>
                    ${res.data.note ? `<p class="mt-4 rounded-xl bg-stone-50 p-4 text-sm text-ink-soft">${escapeHtml(res.data.note)}</p>` : ''}`;
                resultEl.hidden = false;
            })
            .catch(() => {
                errorEl.textContent = 'Bağlantı hatası, lütfen tekrar deneyin.';
                errorEl.hidden = false;
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Sorgula';
            });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeInquiryModal();
    });

    // A photo that fails to load leaves its tinted placeholder visible
    // instead of a broken-image icon.
    document.addEventListener('error', function (e) {
        if (e.target.tagName === 'IMG') e.target.style.visibility = 'hidden';
    }, true);
</script>
<script src="<?= asset_url('assets/js/quote-wizard-v2.js') ?>"></script>

<?php if (!$appPage): ?>
    <!-- Voyn Widget -->
    <script src="/widget/widget.js?v=14" data-website-uuid="<?= e(getenv('LEADS_WEBSITE_UUID') ?: '1be2f821-28cd-4c86-aeb0-dabe0c05aa0a') ?>"></script>
<?php endif; ?>
</body>

</html>
