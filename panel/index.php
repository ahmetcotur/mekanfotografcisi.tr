<?php
/**
 * Freelancer Dashboard (/panel)
 * Single page with client-side tabs (Açık İşler / İşlerim / Profilim), all backed
 * by the api/freelancer/* endpoints. Auth is enforced client-side via the JWT
 * stored in localStorage (mf_token) - same pattern admin-spa uses server-side
 * via bearer token, just without a build step.
 */
$pageTitle = 'Fotoğrafçı Paneli';
$pageDescription = 'Açık çekim taleplerini görüntüleyin, işlerinizi yönetin ve profilinizi güncelleyin.';
$pageRobots = 'noindex, follow';
include __DIR__ . '/../templates/page-header.php';
?>

<main id="main" class="flex-1 bg-stone-100/60">
    <div id="panel-guard-message" class="container-page max-w-md py-24 text-center" hidden>
        <h1 class="h-section">Giriş yapman gerekiyor</h1>
        <p class="mt-3 text-ink-muted">Fotoğrafçı panelini görmek için hesabınla giriş yap.</p>
        <div class="mt-8 flex justify-center gap-3">
            <a href="/giris" class="btn btn-primary">Giriş yap</a>
            <a href="/kayit/fotografci" class="btn btn-outline">Kayıt ol</a>
        </div>
    </div>

    <div id="panel-content" class="container-page max-w-5xl py-10 md:py-12" hidden>
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="eyebrow">Fotoğrafçı paneli</p>
                <h1 class="h-section mt-2" id="panel-greeting">Merhaba</h1>
                <p id="panel-status-note" class="mt-2 text-sm"></p>
            </div>
            <div class="flex gap-2">
                <a id="panel-public-link" href="/fotografcilar" class="btn btn-outline btn-sm" hidden>Profilimi gör</a>
                <button type="button" onclick="mfLogout()" class="btn btn-ghost btn-sm"><?= icon('log-out', 'h-4 w-4') ?> Çıkış</button>
            </div>
        </div>

        <div class="mt-8 inline-flex rounded-full border border-line bg-white p-1" role="tablist" aria-label="Panel bölümleri">
            <button type="button" role="tab" data-tab="open" class="panel-tab rounded-full px-4 py-2 text-sm font-semibold">Açık işler</button>
            <button type="button" role="tab" data-tab="mine" class="panel-tab rounded-full px-4 py-2 text-sm font-semibold">İşlerim</button>
            <button type="button" role="tab" data-tab="profile" class="panel-tab rounded-full px-4 py-2 text-sm font-semibold">Profilim</button>
        </div>

        <div class="mt-6">
            <div id="tab-open" class="panel-tab-content" role="tabpanel"></div>
            <div id="tab-mine" class="panel-tab-content" role="tabpanel" hidden></div>
            <div id="tab-profile" class="panel-tab-content" role="tabpanel" hidden></div>
        </div>
    </div>
</main>

<div id="toast" class="fixed bottom-6 left-1/2 z-[300] -translate-x-1/2 rounded-full bg-ink px-5 py-3 text-sm font-medium text-white shadow-lift" role="status" hidden></div>

<script>
    const token = localStorage.getItem('mf_token');
    const role = localStorage.getItem('mf_role');

    const STATUS_LABELS = { pending: 'Onay bekliyor', accepted: 'Kabul edildi', completed: 'Tamamlandı', rejected: 'Reddedildi' };
    const SERVICE_LABELS = { mimari: 'Mimari & iç mekan', otel: 'Otel & turizm', yemek: 'Yemek & restoran', diger: 'Diğer' };

    function mfLogout() {
        localStorage.removeItem('mf_token');
        localStorage.removeItem('mf_role');
        window.location.href = '/giris';
    }

    function authHeaders() {
        return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token };
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function toast(message) {
        const el = document.getElementById('toast');
        el.textContent = message;
        el.hidden = false;
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => { el.hidden = true; }, 3500);
    }

    function emptyState(title, text) {
        return `<div class="card p-10 text-center"><p class="font-semibold">${title}</p><p class="mx-auto mt-1 max-w-md text-sm text-ink-muted">${text}</p></div>`;
    }

    function loading() {
        return '<div class="card p-10 text-center text-sm text-ink-muted">Yükleniyor…</div>';
    }

    function errorState(message) {
        return `<div class="notice notice-error">${escapeHtml(message || 'Bir hata oluştu.')}</div>`;
    }

    function statusBadge(status) {
        const colors = { pending: 'bg-amber-50 text-amber-700', accepted: 'bg-sky-50 text-sky-700', completed: 'bg-emerald-50 text-emerald-700', rejected: 'bg-red-50 text-red-700' };
        return `<span class="status ${colors[status] || 'bg-stone-100 text-ink-soft'}">${escapeHtml(STATUS_LABELS[status] || status)}</span>`;
    }

    function quoteTitle(quote) {
        if (!quote) return 'Çekim talebi';
        return escapeHtml(SERVICE_LABELS[quote.service] || quote.service || 'Genel çekim');
    }

    if (!token || role !== 'freelancer') {
        document.getElementById('panel-guard-message').hidden = false;
    } else {
        document.getElementById('panel-content').hidden = false;
        initPanel();
    }

    function initPanel() {
        document.querySelectorAll('.panel-tab').forEach(btn => {
            btn.addEventListener('click', () => switchTab(btn.dataset.tab));
        });
        const initial = ['open', 'mine', 'profile'].includes(location.hash.slice(1)) ? location.hash.slice(1) : 'open';
        switchTab(initial);
        loadOpenQuotes();
        loadMyAssignments();
        loadProfile();
    }

    function switchTab(tab) {
        document.querySelectorAll('.panel-tab').forEach(btn => {
            const active = btn.dataset.tab === tab;
            btn.setAttribute('aria-selected', String(active));
            btn.classList.toggle('bg-ink', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('text-ink-soft', !active);
        });
        document.querySelectorAll('.panel-tab-content').forEach(el => { el.hidden = el.id !== 'tab-' + tab; });
        history.replaceState(null, '', '#' + tab);
    }

    function apiFetch(url, options = {}) {
        return fetch(url, Object.assign({ headers: authHeaders() }, options))
            .then(async r => {
                const data = await r.json();
                if (r.status === 401) { mfLogout(); throw new Error('unauthorized'); }
                return data;
            });
    }

    function loadOpenQuotes() {
        const el = document.getElementById('tab-open');
        el.innerHTML = loading();
        apiFetch('/api/freelancer/open-quotes.php').then(data => {
            if (!data.success) { el.innerHTML = errorState(data.error); return; }
            if (data.note) { el.innerHTML = emptyState('Açık işler henüz kapalı', escapeHtml(data.note)); return; }
            if (!data.matches.length) { el.innerHTML = emptyState('Şu an sana uygun açık talep yok', 'Bölgene ve uzmanlığına uyan yeni talepler geldiğinde burada görünecek.'); return; }
            el.innerHTML = '<ul class="space-y-3">' + data.matches.map(m => `
                <li class="card flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-semibold">${quoteTitle(m.quote)}${m.quote.location ? ' · ' + escapeHtml(m.quote.location) : ''}</p>
                        <p class="mt-1 line-clamp-2 whitespace-pre-line text-sm text-ink-soft">${escapeHtml(m.quote.message || '')}</p>
                        <p class="mt-2 text-xs text-ink-muted">Uyum puanı: ${escapeHtml(m.score)}</p>
                    </div>
                    <button type="button" onclick="claimQuote(${Number(m.quote.id)}, this)" class="btn btn-primary shrink-0">Üstlen</button>
                </li>
            `).join('') + '</ul>';
        }).catch(() => { el.innerHTML = errorState('Talepler yüklenemedi.'); });
    }

    function claimQuote(quoteId, btn) {
        btn.disabled = true;
        apiFetch('/api/freelancer/claim-quote.php', { method: 'POST', body: JSON.stringify({ quote_id: quoteId }) })
            .then(data => {
                toast(data.success ? 'Talep üstlenildi. İşlerim sekmesinden takip edebilirsin.' : (data.error || 'Talep üstlenilemedi.'));
                if (data.success) { loadOpenQuotes(); loadMyAssignments(); } else { btn.disabled = false; }
            })
            .catch(() => { btn.disabled = false; toast('Bağlantı hatası.'); });
    }

    function loadMyAssignments() {
        const el = document.getElementById('tab-mine');
        el.innerHTML = loading();
        apiFetch('/api/freelancer/my-assignments.php').then(data => {
            if (!data.success) { el.innerHTML = errorState(data.error); return; }
            if (!data.assignments.length) { el.innerHTML = emptyState('Henüz bir işin yok', 'Açık işler sekmesinden sana uygun talepleri üstlenebilirsin.'); return; }
            el.innerHTML = '<ul class="space-y-3">' + data.assignments.map(a => `
                <li class="card p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <p class="font-semibold">${quoteTitle(a.quote)}${a.quote && a.quote.location ? ' · ' + escapeHtml(a.quote.location) : ''}</p>
                        ${statusBadge(a.status)}
                    </div>
                    <p class="mt-2 whitespace-pre-line text-sm text-ink-soft">${escapeHtml(a.quote ? a.quote.message || '' : '')}</p>
                    ${a.status === 'pending' ? `
                        <div class="mt-4 flex gap-2">
                            <button type="button" onclick="updateAssignment(${Number(a.id)}, 'accepted')" class="btn btn-primary btn-sm">Kabul et</button>
                            <button type="button" onclick="updateAssignment(${Number(a.id)}, 'rejected')" class="btn btn-outline btn-sm">Reddet</button>
                        </div>` : ''}
                    ${a.status === 'accepted' ? `
                        <button type="button" onclick="updateAssignment(${Number(a.id)}, 'completed')" class="btn btn-dark btn-sm mt-4">Tamamlandı olarak işaretle</button>` : ''}
                </li>
            `).join('') + '</ul>';
        }).catch(() => { el.innerHTML = errorState('İşler yüklenemedi.'); });
    }

    function updateAssignment(id, status) {
        apiFetch('/api/freelancer/assignment-status.php', { method: 'PUT', body: JSON.stringify({ id, status }) })
            .then(data => {
                if (!data.success) { toast(data.error || 'İşlem başarısız.'); return; }
                toast('İş durumu güncellendi: ' + (STATUS_LABELS[status] || status));
                loadMyAssignments();
            })
            .catch(() => toast('Bağlantı hatası.'));
    }

    function loadProfile() {
        const el = document.getElementById('tab-profile');
        el.innerHTML = loading();
        apiFetch('/api/freelancer/profile.php').then(data => {
            if (!data.success) { el.innerHTML = errorState(data.error); return; }
            const p = data.profile;
            const approved = p.status === 'approved';

            if (p.name) document.getElementById('panel-greeting').textContent = 'Merhaba, ' + p.name.split(' ')[0];
            const note = document.getElementById('panel-status-note');
            note.innerHTML = approved
                ? '<span class="status bg-emerald-50 text-emerald-700">Profilin onaylı</span>'
                : '<span class="status bg-amber-50 text-amber-700">Başvurun inceleniyor</span> <span class="text-ink-muted">Onaylandığında açık işleri görmeye başlayacaksın.</span>';
            if (approved && p.is_public && p.slug) {
                const link = document.getElementById('panel-public-link');
                link.href = '/fotografcilar/' + encodeURIComponent(p.slug);
                link.hidden = false;
            }

            el.innerHTML = `
                <div class="grid gap-4 lg:grid-cols-5">
                    <form id="profile-form" class="card space-y-4 p-6 lg:col-span-3" novalidate>
                        <h2 class="font-semibold">Profil bilgileri</h2>
                        <div>
                            <label for="pf-bio" class="label">Biyografi</label>
                            <textarea id="pf-bio" name="bio" rows="5" class="input" placeholder="Hangi mekanları çekiyorsun, tarzın nasıl?">${escapeHtml(p.bio || '')}</textarea>
                            <p class="hint">Müşteriler profilinde bunu görür.</p>
                        </div>
                        <div>
                            <label for="pf-city" class="label">Şehir</label>
                            <input id="pf-city" name="city" value="${escapeHtml(p.city || '')}" class="input">
                        </div>
                        <label class="flex items-start gap-3 rounded-xl border border-line p-4 ${approved ? 'cursor-pointer' : 'opacity-60'}">
                            <input type="checkbox" name="is_public" class="mt-0.5 h-4 w-4 accent-brand-600" ${p.is_public ? 'checked' : ''} ${approved ? '' : 'disabled'}>
                            <span class="text-sm">
                                <span class="block font-medium">Profilim herkese açık dizinde görünsün</span>
                                <span class="text-ink-muted">${approved ? 'Kapalıyken müşteriler seni dizinde bulamaz.' : 'Başvurun onaylandığında açabilirsin.'}</span>
                            </span>
                        </label>
                        <p id="profile-form-message" class="notice" hidden></p>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </form>

                    <div class="card p-6 lg:col-span-2">
                        <h2 class="font-semibold">Portfolyo fotoğrafları</h2>
                        <p class="mt-1 text-sm text-ink-muted">En iyi mekan çekimlerini yükle; profilinde galeri olarak görünür.</p>
                        <label for="portfolio-input" class="mt-4 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-stone-300 p-6 text-center text-sm text-ink-muted hover:border-brand-400 hover:bg-brand-50/40">
                            <?= icon('upload', 'h-6 w-6') ?>
                            <span id="portfolio-input-label">Fotoğraf seç (birden fazla seçebilirsin)</span>
                            <input type="file" id="portfolio-input" multiple accept="image/*" class="sr-only">
                        </label>
                        <button type="button" onclick="uploadPortfolio(this)" class="btn btn-dark mt-4 w-full">Yükle</button>
                        <p id="portfolio-upload-message" class="notice mt-3" hidden></p>
                    </div>
                </div>
            `;

            document.getElementById('portfolio-input').addEventListener('change', function () {
                document.getElementById('portfolio-input-label').textContent = this.files.length ? `${this.files.length} fotoğraf seçildi` : 'Fotoğraf seç (birden fazla seçebilirsin)';
            });

            document.getElementById('profile-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const fd = new FormData(e.target);
                const body = { bio: fd.get('bio'), city: fd.get('city'), is_public: fd.get('is_public') === 'on' };
                apiFetch('/api/freelancer/profile.php', { method: 'PUT', body: JSON.stringify(body) })
                    .then(data => {
                        const msg = document.getElementById('profile-form-message');
                        msg.textContent = data.success ? 'Kaydedildi.' : (data.error || 'Hata oluştu');
                        msg.className = 'notice ' + (data.success ? 'notice-success' : 'notice-error');
                        msg.hidden = false;
                    });
            });
        }).catch(() => { el.innerHTML = errorState('Profil yüklenemedi.'); });
    }

    function uploadPortfolio(btn) {
        const input = document.getElementById('portfolio-input');
        const msg = document.getElementById('portfolio-upload-message');
        if (!input.files.length) {
            msg.textContent = 'Önce fotoğraf seç.';
            msg.className = 'notice notice-error mt-3';
            msg.hidden = false;
            return;
        }
        const fd = new FormData();
        for (const f of input.files) fd.append('files[]', f);

        btn.disabled = true;
        btn.textContent = 'Yükleniyor…';
        fetch('/api/freelancer/portfolio-upload.php', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            body: fd
        }).then(r => r.json()).then(data => {
            msg.textContent = data.success ? `${data.uploaded} fotoğraf yüklendi.` : (data.error || 'Hata oluştu');
            msg.className = 'notice mt-3 ' + (data.success ? 'notice-success' : 'notice-error');
            msg.hidden = false;
        }).catch(() => {
            msg.textContent = 'Bağlantı hatası.';
            msg.className = 'notice notice-error mt-3';
            msg.hidden = false;
        }).finally(() => {
            btn.disabled = false;
            btn.textContent = 'Yükle';
        });
    }
</script>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
