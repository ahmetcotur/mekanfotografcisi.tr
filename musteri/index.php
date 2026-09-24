<?php
/**
 * Client Dashboard (/musteri)
 * Lists the logged-in client's own quote requests and who's handling them.
 * Same client-side JWT-guard pattern as /panel.
 */
require_once __DIR__ . '/../includes/database.php';

$pageTitle = 'Taleplerim';
$pageDescription = 'Çekim taleplerinizi ve durumlarını görüntüleyin.';
$pageRobots = 'noindex, follow';
include __DIR__ . '/../templates/page-header.php';
?>

<main id="main" class="flex-1 bg-stone-100/60">
    <div id="client-guard-message" class="container-page max-w-md py-24 text-center" hidden>
        <h1 class="h-section">Giriş yapman gerekiyor</h1>
        <p class="mt-3 text-ink-muted">Taleplerini görmek için müşteri hesabınla giriş yap.</p>
        <div class="mt-8 flex justify-center gap-3">
            <a href="/giris" class="btn btn-primary">Giriş yap</a>
            <a href="/kayit/musteri" class="btn btn-outline">Hesap oluştur</a>
        </div>
    </div>

    <div id="client-content" class="container-page max-w-4xl py-10 md:py-12" hidden>
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="eyebrow">Müşteri paneli</p>
                <h1 class="h-section mt-2">Taleplerim</h1>
                <p class="mt-2 text-sm text-ink-muted">Gönderdiğin çekim taleplerini ve seninle eşleşen fotoğrafçıları buradan takip et.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="openQuoteWizard()" class="btn btn-primary">Yeni talep</button>
                <button type="button" onclick="mfLogout()" class="btn btn-ghost"><?= icon('log-out', 'h-4 w-4') ?> Çıkış</button>
            </div>
        </div>

        <div id="quotes-list" class="mt-8"></div>
    </div>
</main>

<script>
    const token = localStorage.getItem('mf_token');
    const role = localStorage.getItem('mf_role');

    const STATUS_LABELS = { pending: 'Fotoğrafçı onayı bekleniyor', accepted: 'Fotoğrafçı kabul etti', completed: 'Tamamlandı', rejected: 'Fotoğrafçı reddetti' };
    const SERVICE_LABELS = { mimari: 'Mimari & iç mekan', otel: 'Otel & turizm', yemek: 'Yemek & restoran', diger: 'Diğer' };

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function mfLogout() {
        localStorage.removeItem('mf_token');
        localStorage.removeItem('mf_role');
        window.location.href = '/giris';
    }

    function statusBadge(status) {
        const colors = { pending: 'bg-amber-50 text-amber-700', accepted: 'bg-sky-50 text-sky-700', completed: 'bg-emerald-50 text-emerald-700', rejected: 'bg-red-50 text-red-700' };
        return `<span class="status ${colors[status] || 'bg-stone-100 text-ink-soft'}">${escapeHtml(STATUS_LABELS[status] || status)}</span>`;
    }

    if (!token || role !== 'client') {
        document.getElementById('client-guard-message').hidden = false;
    } else {
        document.getElementById('client-content').hidden = false;
        loadQuotes();
    }

    function loadQuotes() {
        const el = document.getElementById('quotes-list');
        el.innerHTML = '<div class="card p-10 text-center text-sm text-ink-muted">Yükleniyor…</div>';

        fetch('/api/client/my-quotes.php', { headers: { 'Authorization': 'Bearer ' + token } })
            .then(async r => {
                const data = await r.json();
                if (r.status === 401) { mfLogout(); return; }
                if (!data.success) { el.innerHTML = `<div class="notice notice-error">${escapeHtml(data.error || 'Hata')}</div>`; return; }
                if (!data.quotes.length) {
                    el.innerHTML = `
                        <div class="card p-10 text-center">
                            <p class="font-semibold">Henüz bir talebin yok</p>
                            <p class="mx-auto mt-1 max-w-md text-sm text-ink-muted">Mekanını anlat, bölgendeki uygun fotoğrafçılar teklifleriyle sana dönsün.</p>
                            <button type="button" onclick="openQuoteWizard()" class="btn btn-primary mt-6">İlk talebini oluştur</button>
                        </div>`;
                    return;
                }

                el.innerHTML = '<ul class="space-y-3">' + data.quotes.map(q => {
                    const assignment = (q.assignments || [])[0];
                    const badge = assignment ? statusBadge(assignment.status) : '<span class="status bg-stone-100 text-ink-soft">Fotoğrafçı aranıyor</span>';
                    const date = q.created_at ? new Date(q.created_at.replace(' ', 'T')).toLocaleDateString('tr-TR') : '';
                    return `
                    <li class="card p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">${escapeHtml(SERVICE_LABELS[q.service] || q.service || 'Genel çekim')}${q.location ? ' · ' + escapeHtml(q.location) : ''}</p>
                                ${date ? `<p class="mt-0.5 text-xs text-ink-muted">${escapeHtml(date)}</p>` : ''}
                            </div>
                            ${badge}
                        </div>
                        <p class="mt-3 line-clamp-3 whitespace-pre-line text-sm text-ink-soft">${escapeHtml(q.message || '')}</p>
                        ${assignment && assignment.freelancer ? `
                            <div class="mt-4 flex items-center justify-between gap-3 border-t border-line pt-4 text-sm">
                                <span class="text-ink-muted">Fotoğrafçın</span>
                                <a href="/fotografcilar/${encodeURIComponent(assignment.freelancer.slug || '')}" class="font-semibold text-brand-700 hover:underline">${escapeHtml(assignment.freelancer.name)}</a>
                            </div>` : ''}
                    </li>`;
                }).join('') + '</ul>';
            })
            .catch(() => { el.innerHTML = '<div class="notice notice-error">Talepler yüklenemedi.</div>'; });
    }
</script>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
