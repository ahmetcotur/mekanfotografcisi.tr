<!-- Footer -->
<!-- Quote Wizard Modal -->
<?php include __DIR__ . '/partials/quote-wizard.php'; ?>

<footer class="site-footer bg-slate-900 border-t border-slate-800 text-slate-300 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8 mb-16">

            <!-- Brand Section -->
            <div class="lg:col-span-4 space-y-6">
                <a href="/" class="flex items-center gap-2 group">
                    <?php
                    $logoUrl = get_setting('logo_url');
                    $siteName = get_setting('site_title', 'Mekan Fotoğrafçısı');
                    ?>
                    <?php if ($logoUrl): ?>
                        <img src="<?= e($logoUrl) ?>" alt="<?= e($siteName) ?>"
                            class="h-12 w-auto object-contain transition-transform group-hover:scale-105">
                    <?php else: ?>
                        <div
                            class="w-10 h-10 bg-brand-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-brand-500/20 transition-transform group-hover:scale-105 group-hover:rotate-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                                <circle cx="12" cy="13" r="3" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-heading font-bold text-xl tracking-tight text-white leading-none">Mekan</span>
                            <span class="font-heading font-medium text-sm tracking-wide text-slate-400">Fotoğrafçısı</span>
                        </div>
                    <?php endif; ?>
                </a>

                <p class="text-slate-400 leading-relaxed max-w-sm">
                    Antalya ve Muğla bölgesinde mimari, iç mekan ve otel fotoğrafçılığında uzmanlaşmış ekibimizle
                    mekanlarınızın hikayesini en iyi şekilde anlatıyoruz.
                </p>

                <!-- Social Links -->
                <div class="flex items-center gap-4">
                    <a href="<?= e(get_setting('social_instagram', '#')) ?>"
                        class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-brand-600 hover:text-white transition-all hover:-translate-y-1"
                        aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                        </svg>
                    </a>
                    <a href="<?= e(get_setting('social_facebook', '#')) ?>"
                        class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-brand-600 hover:text-white transition-all hover:-translate-y-1"
                        aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                        </svg>
                    </a>
                    <a href="mailto:<?= e(get_setting('email', 'info@mekanfotografcisi.tr')) ?>"
                        class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-brand-600 hover:text-white transition-all hover:-translate-y-1"
                        aria-label="Email">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="16" x="2" y="4" rx="2" />
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        </svg>
                    </a>
                </div>

                <div class="flex flex-wrap gap-4 pt-4">
                    <button onclick="openQuoteWizard()"
                        class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-xl font-bold text-sm transition-all shadow-lg shadow-brand-600/20">
                        Hemen Teklif Al
                    </button>
                    <button onclick="openInquiryModal()"
                        class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-6 py-3 rounded-xl font-bold text-sm transition-all border border-slate-700">
                        Teklif Sorgula
                    </button>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="lg:col-span-2 space-y-6">
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider">Hizmetler</h4>
                <ul class="space-y-3">
                    <li><a href="/hizmetlerimiz/mimari-fotografcilik"
                            class="text-sm hover:text-brand-400 transition-colors">Mimari Çekimler</a></li>
                    <li><a href="/hizmetlerimiz/ic-mekan-fotografciligi"
                            class="text-sm hover:text-brand-400 transition-colors">İç Mekan</a></li>
                    <li><a href="/hizmetlerimiz/otel-fotografciligi"
                            class="text-sm hover:text-brand-400 transition-colors">Otel & Tatil Köyü</a></li>
                    <li><a href="/hizmetlerimiz/emlak-fotografciligi"
                            class="text-sm hover:text-brand-400 transition-colors">Emlak & Villa</a></li>
                    <li><a href="/hizmetlerimiz"
                            class="text-sm hover:text-brand-400 transition-colors flex items-center gap-1 text-brand-400">Tüm
                            Hizmetler <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6" />
                            </svg></a></li>
                </ul>
            </div>

            <!-- Corporate -->
            <div class="lg:col-span-2 space-y-6">
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider">Kurumsal</h4>
                <ul class="space-y-3">
                    <li><a href="/portfolio" class="text-sm hover:text-brand-400 transition-colors">Portfolyo</a></li>
                    <li><a href="/hizmet-bolgeleri" class="text-sm hover:text-brand-400 transition-colors">Hizmet
                            Bölgeleri</a>
                    </li>
                    <li><a href="/#hakkimizda" class="text-sm hover:text-brand-400 transition-colors">Hakkımızda</a>
                    </li>
                    <li><a href="/#iletisim" class="text-sm hover:text-brand-400 transition-colors">İletişim</a></li>
                </ul>
            </div>

            <!-- Pexels Integration -->
            <?php $pexelsPhoto = get_random_pexels_photo(); ?>
            <?php if ($pexelsPhoto): ?>
                <div class="lg:col-span-4 space-y-6">
                    <h4 class="text-white font-semibold text-sm uppercase tracking-wider">Günün Karesi</h4>
                    <a href="<?= e($pexelsPhoto['url']) ?>" target="_blank" rel="noopener"
                        class="block group relative aspect-video rounded-xl overflow-hidden border border-slate-700">
                        <img src="<?= e($pexelsPhoto['src']['large'] ?? $pexelsPhoto['thumbnail'] ?? $pexelsPhoto['src']) ?>"
                            alt="<?= e($pexelsPhoto['alt']) ?>"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            loading="lazy">
                        <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/80 to-transparent">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-white/90">Fotoğraf:
                                    <?= e($pexelsPhoto['photographer']) ?></span>
                                <span
                                    class="text-[10px] px-1.5 py-0.5 rounded bg-white/20 text-white backdrop-blur-sm">Pexels</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm text-slate-500">&copy; <?= date('Y') ?> Mekan Fotoğrafçısı. Tüm hakları saklıdır.</p>
            <div class="flex gap-6 text-sm text-slate-500">
                <a href="#" class="hover:text-white transition-colors">Gizlilik Politikası</a>
                <a href="#" class="hover:text-white transition-colors">Kullanım Şartları</a>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="/assets/js/main.js?v=<?= time() ?>"></script>

<!-- SweetAlert2 for Frontend Modals -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Inquiry Modal -->
<div id="inquiry-modal" class="fixed inset-0 z-[10000] hidden flex items-center justify-center p-4">
    <div onclick="closeInquiryModal()" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"></div>
    <div
        class="relative bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in duration-300">
        <div class="p-8">
            <h3 class="text-2xl font-heading font-black text-slate-900 mb-2">Teklif Takibi</h3>
            <p class="text-slate-500 text-sm mb-6">Lütfen size verilen MF-XXXXX formatındaki teklif numarasını giriniz.
            </p>

            <div class="space-y-4">
                <input type="text" id="inquiry-number" placeholder="Örn: MF-00123"
                    class="w-full bg-slate-50 border-slate-200 rounded-2xl p-4 text-center text-xl font-black tracking-tight focus:ring-brand-500 focus:border-brand-500">

                <button onclick="submitInquiry()" id="inquiry-btn"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-brand-600/30 transition-all">
                    Sorgula
                </button>
            </div>

            <div id="inquiry-result"
                class="mt-8 hidden border-t border-slate-100 pt-8 animate-in slide-in-from-bottom-4 duration-500">
                <!-- Result injected here -->
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/quote-wizard-v2.js?v=<?= time() ?>"></script>

<script>
    function openInquiryModal() {
        document.getElementById('inquiry-modal').classList.remove('hidden');
        document.getElementById('inquiry-result').classList.add('hidden');
        document.getElementById('inquiry-number').value = '';
    }

    function closeInquiryModal() {
        document.getElementById('inquiry-modal').classList.add('hidden');
    }

    function submitInquiry() {
        const number = document.getElementById('inquiry-number').value.trim();
        if (!number) return Swal.fire('Uyarı', 'Lütfen teklif numarası giriniz.', 'warning');

        const btn = document.getElementById('inquiry-btn');
        const resultDiv = document.getElementById('inquiry-result');

        btn.disabled = true;
        btn.innerText = 'Sorgulanıyor...';

        fetch('/api/quote-status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ quote_number: number })
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    resultDiv.innerHTML = `
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Sayın</span>
                            <span class="font-bold text-slate-900">${res.data.name}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-400">Durum</span>
                            <span class="bg-brand-50 text-brand-600 px-3 py-1 rounded-full text-xs font-bold border border-brand-100 line-clamp-1">${res.data.status}</span>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 text-left">
                            <span class="text-[10px] text-slate-400 uppercase font-bold block mb-1">Mekan Fotoğrafçısı Notu</span>
                            <p class="text-xs text-slate-700 italic leading-relaxed">${res.data.note}</p>
                        </div>
                        <div class="text-[10px] text-center text-slate-300 pt-2 border-t border-slate-100">Talep Tarihi: ${res.data.date}</div>
                    </div>
                `;
                    resultDiv.classList.remove('hidden');
                } else {
                    Swal.fire({
                        title: 'Hata',
                        text: res.message,
                        icon: 'error',
                        confirmButtonColor: 'var(--brand-600)'
                    });
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = 'Sorgula';
            });
    }
</script>

<!-- GLightbox for Service Galleries -->
<?php if (isset($serviceData['gallery_images']) && !empty($serviceData['gallery_images'])): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof GLightbox !== 'undefined') {
                const lightbox = GLightbox({
                    selector: '.glightbox',
                    touchNavigation: true,
                    loop: true,
                    autoplayVideos: false
                });
            }
        });
    </script>
<?php endif; ?>
</body>

</html>