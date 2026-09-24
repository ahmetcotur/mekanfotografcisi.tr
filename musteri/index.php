<?php
/**
 * Client Dashboard (/musteri)
 * Lists the logged-in client's own quote requests and who's handling them.
 * Same client-side JWT-guard pattern as /panel.
 */
$pageTitle = 'Taleplerim';
$pageDescription = 'Çekim taleplerinizi ve durumlarını görüntüleyin.';
$pageRobots = 'noindex, follow';
include __DIR__ . '/../templates/page-header.php';
?>

<main class="pt-40 pb-24 min-h-screen bg-slate-50">
    <div id="client-guard-message" class="max-w-xl mx-auto px-4 text-center py-24 hidden">
        <h1 class="text-2xl font-heading font-black text-slate-900 mb-4">Bu sayfayı görüntülemek için giriş yapmalısınız</h1>
        <a href="/giris" class="inline-block px-8 py-4 bg-brand-600 text-white rounded-2xl font-bold">Giriş Yap</a>
    </div>

    <div id="client-content" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-10">
            <div>
                <h1 class="text-3xl font-heading font-black text-slate-900">Taleplerim</h1>
                <p class="text-slate-400 text-sm mt-1">Gönderdiğiniz çekim taleplerini ve durumlarını buradan takip edin.</p>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="openQuoteWizard()" class="px-5 py-3 bg-brand-600 text-white rounded-xl text-sm font-bold hover:bg-brand-700">Yeni Talep Oluştur</button>
                <button onclick="mfLogout()" class="text-sm font-bold text-slate-400 hover:text-red-500">Çıkış Yap</button>
            </div>
        </div>

        <div id="quotes-list"></div>
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

    if (!token || role !== 'client') {
        document.getElementById('client-guard-message').classList.remove('hidden');
    } else {
        document.getElementById('client-content').classList.remove('hidden');
        loadQuotes();
    }

    function loadQuotes() {
        const el = document.getElementById('quotes-list');
        el.innerHTML = '<p class="text-slate-400 py-8">Yükleniyor...</p>';

        fetch('/api/client/my-quotes.php', { headers: { 'Authorization': 'Bearer ' + token } })
            .then(async r => {
                const data = await r.json();
                if (r.status === 401) { mfLogout(); return; }
                if (!data.success) { el.innerHTML = `<p class="text-red-500">${data.error || 'Hata'}</p>`; return; }
                if (!data.quotes.length) { el.innerHTML = '<p class="text-slate-400 py-8">Henüz bir talebiniz yok.</p>'; return; }

                el.innerHTML = data.quotes.map(q => {
                    const assignment = (q.assignments || [])[0];
                    const statusBadge = assignment
                        ? `<span class="px-3 py-1 rounded-full text-xs font-bold ${statusColor(assignment.status)}">${escapeHtml(assignment.status)}</span>`
                        : `<span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">Eşleştirme bekleniyor</span>`;

                    return `
                    <div class="bg-white rounded-2xl border border-slate-100 shadow p-6 mb-4">
                        <div class="flex items-center justify-between gap-4 mb-3">
                            <p class="font-bold text-slate-900">${escapeHtml(q.service || 'Genel çekim')} &middot; ${escapeHtml(q.location || '')}</p>
                            ${statusBadge}
                        </div>
                        <p class="text-sm text-slate-400 mb-2">${escapeHtml(q.message || '')}</p>
                        ${assignment && assignment.freelancer ? `<p class="text-xs text-slate-400">Fotoğrafçı: <a href="/fotografcilar/${encodeURIComponent(assignment.freelancer.slug)}" class="text-brand-600 font-bold">${escapeHtml(assignment.freelancer.name)}</a></p>` : ''}
                    </div>`;
                }).join('');
            });
    }

    function statusColor(status) {
        return { pending: 'bg-amber-50 text-amber-600', accepted: 'bg-blue-50 text-blue-600', completed: 'bg-green-50 text-green-600', rejected: 'bg-red-50 text-red-600' }[status] || 'bg-slate-100 text-slate-500';
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : str;
        return div.innerHTML;
    }
</script>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
