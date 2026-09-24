<?php
/**
 * Public Photographer Directory (/fotografcilar)
 * Server-rendered shell + client-side fetch against api/directory/freelancers.php,
 * matching the rest of the public site's vanilla PHP + fetch() convention.
 */
$pageTitle = 'Fotoğrafçılarımız';
$pageDescription = 'Kolektifimizdeki bağımsız mekan fotoğrafçılarını keşfedin; bölge ve uzmanlığa göre filtreleyip doğrudan iletişime geçin.';
include __DIR__ . '/../page-header.php';
?>

<main class="pt-40 pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center gap-4 mb-8">
                <span class="h-px w-12 bg-brand-500/30"></span>
                <span class="text-brand-600 font-bold tracking-[0.3em] text-[11px] uppercase italic">Kolektif</span>
                <span class="h-px w-12 bg-brand-500/30"></span>
            </div>
            <h1 class="text-4xl md:text-6xl font-heading font-black text-slate-900 tracking-tight leading-tight mb-6">
                Fotoğrafçılarımızı Keşfedin
            </h1>
            <p class="text-lg text-slate-500">
                Kolektifimizdeki onaylı, bağımsız fotoğrafçılar arasından bölgenize ve ihtiyacınıza en uygun olanı
                seçin.
            </p>
        </div>

        <div class="flex flex-wrap gap-3 justify-center mb-12">
            <input id="filter-province" type="text" placeholder="Şehir / Bölge"
                class="px-5 py-3 rounded-2xl border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-brand-500/30">
            <select id="filter-specialization"
                class="px-5 py-3 rounded-2xl border border-slate-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                <option value="">Tüm Uzmanlıklar</option>
                <option value="mekan">Mekan</option>
                <option value="dugun">Düğün</option>
                <option value="mimari">Mimari</option>
                <option value="otel">Otel</option>
                <option value="emlak">Emlak</option>
                <option value="yemek">Yemek</option>
                <option value="drone">Drone</option>
            </select>
            <button onclick="loadDirectory()"
                class="px-6 py-3 rounded-2xl bg-brand-600 text-white text-sm font-bold hover:bg-brand-700 transition-all">
                Filtrele
            </button>
        </div>

        <div id="directory-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <p class="col-span-full text-center text-slate-400 py-16">Fotoğrafçılar yükleniyor...</p>
        </div>
    </div>
</main>

<script>
    function loadDirectory() {
        const grid = document.getElementById('directory-grid');
        grid.innerHTML = '<p class="col-span-full text-center text-slate-400 py-16">Yükleniyor...</p>';

        const params = new URLSearchParams();
        const province = document.getElementById('filter-province').value.trim();
        const specialization = document.getElementById('filter-specialization').value;
        if (province) params.set('province', province);
        if (specialization) params.set('specialization', specialization);

        fetch('/api/directory/freelancers.php?' + params.toString())
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.freelancers.length) {
                    grid.innerHTML = '<p class="col-span-full text-center text-slate-400 py-16">Kriterlere uygun fotoğrafçı bulunamadı.</p>';
                    return;
                }
                grid.innerHTML = data.freelancers.map(renderCard).join('');
            })
            .catch(() => {
                grid.innerHTML = '<p class="col-span-full text-center text-red-400 py-16">Fotoğrafçılar yüklenirken bir hata oluştu.</p>';
            });
    }

    function renderCard(f) {
        let specs = [];
        try { specs = JSON.parse(f.specialization || '[]'); } catch (e) { }
        const specBadges = specs.map(s => `<span class="px-3 py-1 bg-brand-50 text-brand-600 rounded-full text-xs font-bold">${escapeHtml(s)}</span>`).join(' ');
        const rating = f.rating_count > 0 ? `★ ${Number(f.rating_avg).toFixed(1)} (${f.rating_count})` : 'Henüz değerlendirme yok';

        return `
        <a href="/fotografcilar/${encodeURIComponent(f.slug)}" class="block bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-900/5 overflow-hidden hover-lift group">
            <div class="h-48 bg-slate-100 flex items-center justify-center text-slate-300 font-heading font-black text-4xl">
                ${escapeHtml((f.name || '?').charAt(0))}
            </div>
            <div class="p-6">
                <h3 class="font-heading font-bold text-xl text-slate-900 group-hover:text-brand-600 transition-colors">${escapeHtml(f.name || '')}</h3>
                <p class="text-sm text-slate-400 mb-3">${escapeHtml(f.city || '')} &middot; ${escapeHtml(rating)}</p>
                <div class="flex flex-wrap gap-2">${specBadges}</div>
            </div>
        </a>`;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    loadDirectory();
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
