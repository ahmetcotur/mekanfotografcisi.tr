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
include __DIR__ . '/../templates/page-header.php';
?>

<main class="pt-40 pb-24 min-h-screen bg-slate-50">
    <div id="panel-guard-message" class="max-w-xl mx-auto px-4 text-center py-24 hidden">
        <h1 class="text-2xl font-heading font-black text-slate-900 mb-4">Bu sayfayı görüntülemek için giriş yapmalısınız</h1>
        <a href="/giris" class="inline-block px-8 py-4 bg-brand-600 text-white rounded-2xl font-bold">Giriş Yap</a>
    </div>

    <div id="panel-content" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-10">
            <div>
                <h1 class="text-3xl font-heading font-black text-slate-900">Fotoğrafçı Paneli</h1>
                <p id="panel-status-note" class="text-slate-400 text-sm mt-1"></p>
            </div>
            <button onclick="mfLogout()" class="text-sm font-bold text-slate-400 hover:text-red-500">Çıkış Yap</button>
        </div>

        <div class="flex gap-2 mb-8 border-b border-slate-200">
            <button data-tab="open" class="panel-tab px-5 py-3 text-sm font-bold border-b-2 border-brand-600 text-brand-600">Açık İşler</button>
            <button data-tab="mine" class="panel-tab px-5 py-3 text-sm font-bold border-b-2 border-transparent text-slate-400">İşlerim</button>
            <button data-tab="profile" class="panel-tab px-5 py-3 text-sm font-bold border-b-2 border-transparent text-slate-400">Profilim</button>
        </div>

        <div id="tab-open" class="panel-tab-content"></div>
        <div id="tab-mine" class="panel-tab-content hidden"></div>
        <div id="tab-profile" class="panel-tab-content hidden"></div>
    </div>
</main>

<script>
    const token = localStorage.getItem('mf_token');
    const role = localStorage.getItem('mf_role');

    function mfLogout() {
        localStorage.removeItem('mf_token');
        localStorage.removeItem('mf_role');
        window.location.href = '/giris';
    }

    function authHeaders() {
        return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token };
    }

    if (!token || role !== 'freelancer') {
        document.getElementById('panel-guard-message').classList.remove('hidden');
    } else {
        document.getElementById('panel-content').classList.remove('hidden');
        initPanel();
    }

    function initPanel() {
        document.querySelectorAll('.panel-tab').forEach(btn => {
            btn.addEventListener('click', () => switchTab(btn.dataset.tab));
        });
        loadOpenQuotes();
        loadMyAssignments();
        loadProfile();
    }

    function switchTab(tab) {
        document.querySelectorAll('.panel-tab').forEach(btn => {
            const active = btn.dataset.tab === tab;
            btn.classList.toggle('border-brand-600', active);
            btn.classList.toggle('text-brand-600', active);
            btn.classList.toggle('border-transparent', !active);
            btn.classList.toggle('text-slate-400', !active);
        });
        document.querySelectorAll('.panel-tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('tab-' + tab).classList.remove('hidden');
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
        el.innerHTML = '<p class="text-slate-400 py-8">Yükleniyor...</p>';
        apiFetch('/api/freelancer/open-quotes.php').then(data => {
            if (!data.success) { el.innerHTML = `<p class="text-red-500">${data.error || 'Hata'}</p>`; return; }
            if (data.note) { el.innerHTML = `<p class="text-slate-400 py-8">${data.note}</p>`; return; }
            if (!data.matches.length) { el.innerHTML = '<p class="text-slate-400 py-8">Şu anda size uygun açık talep yok.</p>'; return; }
            el.innerHTML = data.matches.map(m => `
                <div class="bg-white rounded-2xl border border-slate-100 shadow p-6 mb-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-slate-900">${escapeHtml(m.quote.service || 'Genel çekim')} &middot; ${escapeHtml(m.quote.location || '')}</p>
                        <p class="text-sm text-slate-400 mt-1">${escapeHtml(m.quote.message || '')}</p>
                        <p class="text-xs text-slate-300 mt-1">Skor: ${m.score}</p>
                    </div>
                    <button onclick="claimQuote(${m.quote.id})" class="shrink-0 px-5 py-2.5 bg-brand-600 text-white rounded-xl text-sm font-bold hover:bg-brand-700">Üstlen</button>
                </div>
            `).join('');
        });
    }

    function claimQuote(quoteId) {
        apiFetch('/api/freelancer/claim-quote.php', { method: 'POST', body: JSON.stringify({ quote_id: quoteId }) })
            .then(data => {
                alert(data.success ? 'Talep üstlenildi.' : (data.error || 'Hata oluştu'));
                if (data.success) { loadOpenQuotes(); loadMyAssignments(); }
            });
    }

    function loadMyAssignments() {
        const el = document.getElementById('tab-mine');
        el.innerHTML = '<p class="text-slate-400 py-8">Yükleniyor...</p>';
        apiFetch('/api/freelancer/my-assignments.php').then(data => {
            if (!data.success) { el.innerHTML = `<p class="text-red-500">${data.error || 'Hata'}</p>`; return; }
            if (!data.assignments.length) { el.innerHTML = '<p class="text-slate-400 py-8">Henüz bir işiniz yok.</p>'; return; }
            el.innerHTML = data.assignments.map(a => `
                <div class="bg-white rounded-2xl border border-slate-100 shadow p-6 mb-4">
                    <div class="flex items-center justify-between gap-4 mb-3">
                        <p class="font-bold text-slate-900">${escapeHtml(a.quote ? (a.quote.service || 'Genel çekim') : '')} &middot; ${escapeHtml(a.quote ? a.quote.location || '' : '')}</p>
                        <span class="px-3 py-1 rounded-full text-xs font-bold ${statusColor(a.status)}">${escapeHtml(a.status)}</span>
                    </div>
                    <p class="text-sm text-slate-400 mb-4">${escapeHtml(a.quote ? a.quote.message || '' : '')}</p>
                    ${a.status === 'pending' ? `
                        <div class="flex gap-2">
                            <button onclick="updateAssignment(${a.id}, 'accepted')" class="px-4 py-2 bg-green-600 text-white rounded-xl text-xs font-bold">Kabul Et</button>
                            <button onclick="updateAssignment(${a.id}, 'rejected')" class="px-4 py-2 bg-slate-200 text-slate-600 rounded-xl text-xs font-bold">Reddet</button>
                        </div>` : ''}
                    ${a.status === 'accepted' ? `
                        <button onclick="updateAssignment(${a.id}, 'completed')" class="px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold">Tamamlandı Olarak İşaretle</button>` : ''}
                </div>
            `).join('');
        });
    }

    function statusColor(status) {
        return { pending: 'bg-amber-50 text-amber-600', accepted: 'bg-blue-50 text-blue-600', completed: 'bg-green-50 text-green-600', rejected: 'bg-red-50 text-red-600' }[status] || 'bg-slate-100 text-slate-500';
    }

    function updateAssignment(id, status) {
        apiFetch('/api/freelancer/assignment-status.php', { method: 'PUT', body: JSON.stringify({ id, status }) })
            .then(data => {
                if (!data.success) { alert(data.error || 'Hata oluştu'); return; }
                loadMyAssignments();
            });
    }

    function loadProfile() {
        const el = document.getElementById('tab-profile');
        el.innerHTML = '<p class="text-slate-400 py-8">Yükleniyor...</p>';
        apiFetch('/api/freelancer/profile.php').then(data => {
            if (!data.success) { el.innerHTML = `<p class="text-red-500">${data.error || 'Hata'}</p>`; return; }
            const p = data.profile;
            document.getElementById('panel-status-note').textContent =
                p.status === 'approved' ? 'Profiliniz onaylı' : 'Başvurunuz inceleniyor';

            el.innerHTML = `
                <form id="profile-form" class="bg-white rounded-2xl border border-slate-100 shadow p-8 max-w-2xl space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-600 mb-1">Biyografi</label>
                        <textarea name="bio" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200">${escapeHtml(p.bio || '')}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-600 mb-1">Şehir</label>
                        <input name="city" value="${escapeHtml(p.city || '')}" class="w-full px-4 py-3 rounded-xl border border-slate-200">
                    </div>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_public" ${p.is_public ? 'checked' : ''} ${p.status !== 'approved' ? 'disabled' : ''}>
                        <span class="text-sm font-medium text-slate-600">Profilim herkese açık dizinde görünsün ${p.status !== 'approved' ? '(admin onayı bekleniyor)' : ''}</span>
                    </label>
                    <button type="submit" class="px-6 py-3 bg-brand-600 text-white rounded-xl font-bold">Kaydet</button>
                    <p id="profile-form-message" class="text-sm font-medium"></p>
                </form>

                <div class="bg-white rounded-2xl border border-slate-100 shadow p-8 max-w-2xl mt-6">
                    <label class="block text-sm font-bold text-slate-600 mb-3">Portfolyo Fotoğrafları</label>
                    <input type="file" id="portfolio-input" multiple accept="image/*" class="mb-3">
                    <button onclick="uploadPortfolio()" class="px-6 py-3 bg-slate-900 text-white rounded-xl font-bold text-sm">Yükle</button>
                    <p id="portfolio-upload-message" class="text-sm font-medium mt-3"></p>
                </div>
            `;

            document.getElementById('profile-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const fd = new FormData(e.target);
                const body = { bio: fd.get('bio'), city: fd.get('city'), is_public: fd.get('is_public') === 'on' };
                apiFetch('/api/freelancer/profile.php', { method: 'PUT', body: JSON.stringify(body) })
                    .then(data => {
                        const msg = document.getElementById('profile-form-message');
                        msg.textContent = data.success ? 'Kaydedildi.' : (data.error || 'Hata oluştu');
                        msg.className = 'text-sm font-medium ' + (data.success ? 'text-green-600' : 'text-red-600');
                    });
            });
        });
    }

    function uploadPortfolio() {
        const input = document.getElementById('portfolio-input');
        if (!input.files.length) return;
        const fd = new FormData();
        for (const f of input.files) fd.append('files[]', f);

        fetch('/api/freelancer/portfolio-upload.php', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            body: fd
        }).then(r => r.json()).then(data => {
            const msg = document.getElementById('portfolio-upload-message');
            msg.textContent = data.success ? `${data.uploaded} fotoğraf yüklendi.` : (data.error || 'Hata oluştu');
            msg.className = 'text-sm font-medium mt-3 ' + (data.success ? 'text-green-600' : 'text-red-600');
        });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : str;
        return div.innerHTML;
    }
</script>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
