<?php
/**
 * Service-specific template
 * Supports both legacy text content and new Tailwind-rich content
 */
include __DIR__ . '/../page-header.php';
global $db;
?>

<?php
// Generic fallback for non-updated services
// Get hero image or fallback
$heroImageMeta = $post->getMeta('hero_image');
if ($heroImageMeta) {
    $heroImage = $heroImageMeta;
} else {
    $randomPhoto = get_random_pexels_photo();
    $heroImage = $randomPhoto ? $randomPhoto['src'] : 'https://images.pexels.com/photos/2079246/pexels-photo-2079246.jpeg';
}
$serviceName = $post->title;
?>

<!-- Service Hero -->
<section class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-950">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="<?= htmlspecialchars($serviceName) ?>"
            class="w-full h-full object-cover opacity-30 animate-pulse-subtle">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
        <div
            class="absolute inset-0 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:40px_40px] opacity-10">
        </div>
    </div>

    <div class="relative z-10 container mx-auto px-4 text-center py-24 animate-slide-up">
        <span
            class="inline-block px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-[10px] font-black tracking-[0.2em] uppercase mb-8 backdrop-blur-xl">
            Profesyonel Hizmetlerimiz
        </span>
        <h1 class="font-heading font-black text-5xl md:text-8xl text-white mb-8 tracking-tighter drop-shadow-2xl">
            <?= htmlspecialchars($post->title) ?>
        </h1>
        <p class="text-xl md:text-2xl text-slate-400 max-w-4xl mx-auto font-light leading-relaxed mb-16">
            <?= htmlspecialchars($post->excerpt ?: 'İşletmeniz için yüksek kaliteli görsel içerikler üretiyoruz.') ?>
        </p>

        <div class="flex flex-col sm:flex-row gap-8 justify-center items-center">
            <button onclick="openQuoteWizard('mimari')"
                class="group relative px-12 py-6 bg-brand-600 hover:bg-brand-500 text-white rounded-3xl font-black text-xl shadow-[0_20px_50px_rgba(14,165,233,0.3)] transition-all hover:scale-110 active:scale-95 overflow-hidden">
                <span class="relative z-10">Hemen Fiyat Teklifi Al</span>
                <div
                    class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
                </div>
            </button>
            <a href="#hizmet-detay"
                class="px-12 py-6 bg-white/5 hover:bg-white/10 text-white border border-white/20 rounded-3xl font-black text-xl backdrop-blur-xl transition-all hover:scale-110 active:scale-95">
                Detayları İncele
            </a>
        </div>
    </div>
</section>

<!-- Main Content -->
<main id="hizmet-detay" class="py-24 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-3 gap-12">

            <!-- Content -->
            <div class="lg:col-span-2 space-y-16">
                <!-- Intro -->
                <div
                    class="bg-white rounded-5xl p-10 md:p-16 shadow-[0_32px_64px_-20px_rgba(0,0,0,0.06)] border border-slate-100 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full blur-3xl -mr-32 -mt-32 opacity-50 group-hover:scale-110 transition-transform duration-1000">
                    </div>
                    <div class="relative z-10">
                        <h2 class="font-heading font-black text-4xl text-slate-900 mb-10 tracking-tight">
                            <span class="text-gradient"><?= htmlspecialchars($post->title) ?></span> Hakkında Merak
                            Edilenler
                        </h2>
                        <div class="prose prose-lg prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                            <?= do_shortcode($post->content) ?>
                        </div>
                    </div>
                </div>

                <!-- Process Workflow Section -->
                <div class="space-y-12">
                    <div class="text-center mb-12">
                        <span class="text-brand-500 text-[10px] font-black tracking-[0.2em] uppercase mb-4 block">Nasıl
                            Çalışıyoruz?</span>
                        <h3 class="text-4xl font-heading font-black text-slate-900 tracking-tight">Çekim Süreci</h3>
                    </div>

                    <div class="grid md:grid-cols-3 gap-8">
                        <!-- Step 1 -->
                        <div
                            class="bg-white p-10 rounded-4xl shadow-sm border border-slate-100 hover:shadow-2xl hover:border-brand-200 transition-all group relative overflow-hidden">
                            <div class="relative z-10">
                                <div
                                    class="w-16 h-16 bg-slate-50 text-slate-900 rounded-2xl flex items-center justify-center text-2xl mb-8 font-black shadow-inner group-hover:bg-brand-600 group-hover:text-white transition-all duration-500">
                                    1</div>
                                <h4 class="font-black text-2xl text-slate-900 mb-4 tracking-tight">Planlama</h4>
                                <p class="text-slate-500 text-sm leading-relaxed font-medium">
                                    Mekanınızı inceliyor, ışık ve açı planlaması yaparak en doğru zamanı belirliyoruz.
                                    İsteklerinize özel çekim planı oluşturuyoruz.
                                </p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div
                            class="bg-white p-10 rounded-4xl shadow-sm border border-slate-100 hover:shadow-2xl hover:border-brand-200 transition-all group relative overflow-hidden">
                            <div class="relative z-10">
                                <div
                                    class="w-16 h-16 bg-slate-50 text-slate-900 rounded-2xl flex items-center justify-center text-2xl mb-8 font-black shadow-inner group-hover:bg-brand-600 group-hover:text-white transition-all duration-500">
                                    2</div>
                                <h4 class="font-black text-2xl text-slate-900 mb-4 tracking-tight">Prodüksiyon</h4>
                                <p class="text-slate-500 text-sm leading-relaxed font-medium">
                                    Belirlenen vakitte, en ileri teknoloji ekipmanlarımızla mekanınızın ruhunu ve
                                    detaylarını en estetik haliyle kayıt altına alıyoruz.
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div
                            class="bg-white p-10 rounded-4xl shadow-sm border border-slate-100 hover:shadow-2xl hover:border-brand-200 transition-all group relative overflow-hidden">
                            <div class="relative z-10">
                                <div
                                    class="w-16 h-16 bg-slate-50 text-slate-900 rounded-2xl flex items-center justify-center text-2xl mb-8 font-black shadow-inner group-hover:bg-brand-600 group-hover:text-white transition-all duration-500">
                                    3</div>
                                <h4 class="font-black text-2xl text-slate-900 mb-4 tracking-tight">Teslimat</h4>
                                <p class="text-slate-500 text-sm leading-relaxed font-medium">
                                    Görselleri retouch ve renk düzenleme işlemlerinden geçirerek, markanızın prestijini
                                    artıracak şekilde dijital olarak teslim ediyoruz.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Sidebar -->
            <aside class="lg:col-span-1">
                <div class="sticky top-28 space-y-8">
                    <!-- Wizard CTA Card -->
                    <div
                        class="bg-slate-900 text-white p-10 rounded-5xl shadow-2xl relative overflow-hidden group hover:-translate-y-2 transition-all duration-500">
                        <div class="absolute inset-0 z-0 opacity-40">
                            <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg"
                                alt="Sidebar CTA" class="w-full h-full object-cover">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/60 to-transparent">
                            </div>
                        </div>

                        <div class="relative z-10">
                            <h3 class="font-black text-3xl mb-6 tracking-tight leading-tight">
                                <?= htmlspecialchars($serviceName) ?> İçin <span
                                    class="text-brand-400 italic">Profesyonel Dokunuş</span>
                            </h3>
                            <p class="text-slate-300 text-sm mb-10 leading-relaxed font-medium">
                                Mekanınızın en iyi halini dünyaya gösterelim. Hemen fiyat teklifi alın.
                            </p>

                            <button onclick="openQuoteWizard('mimari')"
                                class="w-full py-5 bg-white text-slate-900 font-black rounded-2xl shadow-xl hover:bg-brand-50 transition-all flex items-center justify-center gap-3 active:scale-95">
                                <span>Hemen Fiyat Al</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M5 12h14" />
                                    <path d="m12 5 7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div
                        class="bg-white p-10 rounded-5xl border border-slate-100 shadow-[0_32px_64px_-20px_rgba(0,0,0,0.06)] space-y-6">
                        <div
                            class="flex items-center gap-6 p-6 rounded-3xl bg-slate-50 group hover:bg-brand-50 transition-all duration-300">
                            <div
                                class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                📷</div>
                            <div>
                                <h4
                                    class="font-black text-slate-900 text-sm uppercase tracking-widest leading-none mb-1">
                                    Tecrübe</h4>
                                <span class="font-bold text-slate-500 text-xs">Derin Sektör Bilgisi</span>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-6 p-6 rounded-3xl bg-slate-50 group hover:bg-brand-50 transition-all duration-300">
                            <div
                                class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                🚀</div>
                            <div>
                                <h4
                                    class="font-black text-slate-900 text-sm uppercase tracking-widest leading-none mb-1">
                                    Hız</h4>
                                <span class="font-bold text-slate-500 text-xs">Yüksek Kalite & Hızlı Teslim</span>
                            </div>
                        </div>
                    </div>

                    <!-- Service Areas Link -->
                    <div
                        class="bg-white p-10 rounded-5xl border border-slate-100 shadow-[0_32px_64px_-20px_rgba(0,0,0,0.06)] relative overflow-hidden group">
                        <div class="relative z-10">
                            <h4 class="font-black text-slate-900 text-xl tracking-tight mb-4">Hizmet Bölgelerimiz</h4>
                            <p class="text-slate-500 text-sm mb-6 leading-relaxed">Antalya ve çevresinde profesyonel
                                mekan çekimleri yapıyoruz.</p>
                            <a href="/hizmet-bolgeleri"
                                class="flex items-center justify-between font-black text-brand-600 text-sm uppercase tracking-wider hover:text-brand-700 transition-colors">
                                <span>Tüm Bölgeleri Gör</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M5 12h14" />
                                    <path d="m12 5 7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php
// Include Other Services logic
include __DIR__ . '/../partials/services-grid.php';

// Auto-render Gallery if folder is set (placed at the bottom for all styles)
if (!empty($post->gallery_folder_id)) {
    echo render_media_gallery($post->gallery_folder_id, 'Portfolyo');
}

include __DIR__ . '/../partials/example-works.php';
include __DIR__ . '/../page-footer.php';
?>