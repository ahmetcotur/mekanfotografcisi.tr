<?php
/**
 * SEO/Location-specific template
 * Refactored for Tailwind CSS
 */
include __DIR__ . '/../page-header.php';
global $db;

// Get hero image or fallback
$heroImageMeta = $post->getMeta('hero_image');
if ($heroImageMeta) {
    $heroImage = $heroImageMeta;
} else {
    $randomPhoto = get_random_pexels_photo();
    $heroImage = $randomPhoto ? $randomPhoto['src'] : 'https://images.pexels.com/photos/2079246/pexels-photo-2079246.jpeg';
}
$locationName = $post->getMeta('location_name') ?: $post->title;
?>

<!-- Location Hero -->
<section
    class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-900 border-b-4 border-brand-500">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="<?= htmlspecialchars($locationName) ?>"
            class="w-full h-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
    </div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <span
            class="inline-block py-1 px-3 rounded-full bg-brand-500/20 border border-brand-500/30 text-brand-300 text-sm font-semibold mb-4 backdrop-blur-md">
            <?= htmlspecialchars($locationName) ?>
        </span>
        <h1 class="font-heading font-extrabold text-5xl md:text-7xl text-white mb-6 tracking-tight drop-shadow-2xl">
            <?= htmlspecialchars($post->title) ?>
        </h1>
        <p class="text-xl md:text-2xl text-slate-200 max-w-3xl mx-auto font-light leading-relaxed">
            <?= htmlspecialchars($post->excerpt ?: 'Profesyonel mimari ve iç mekan fotoğrafçılığı çözüm ortağınız.') ?>
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mt-10">
            <button onclick="openQuoteWizard('mimari')"
                class="inline-flex items-center justify-center px-8 py-3 bg-brand-600 text-white rounded-xl font-bold shadow-lg shadow-brand-500/25 hover:bg-brand-500 transition-all hover:scale-105">
                Hemen Fiyat Al
            </button>
            <a href="#hizmetler"
                class="inline-flex items-center justify-center px-8 py-3 bg-white/10 text-white backdrop-blur-sm border border-white/20 rounded-xl font-bold hover:bg-white/20 transition-all">
                Hizmetlerimizi İncele
            </a>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="py-24 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-3 gap-12">

            <!-- Content -->
            <div class="lg:col-span-2 space-y-16">
                <!-- Intro -->
                <div class="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-slate-100">
                    <h2 class="font-heading font-bold text-3xl text-slate-900 mb-6 relative inline-block">
                        Projenizi Birlikte Hayata Geçirelim
                        <span class="absolute -bottom-2 left-0 w-1/3 h-1 bg-brand-500 rounded-full"></span>
                    </h2>
                    <div class="prose prose-lg prose-slate max-w-none text-slate-600 leading-relaxed">
                        <?= do_shortcode($post->content) ?>
                    </div>
                </div>

                <!-- Process Workflow Section -->
                <div class="space-y-8">
                    <div class="text-center mb-8">
                        <span class="text-brand-600 font-bold tracking-wider uppercase text-sm">Nasıl
                            Çalışıyoruz?</span>
                        <h3 class="text-3xl font-heading font-bold text-slate-900">Çekim Süreci</h3>
                    </div>

                    <div class="grid md:grid-cols-3 gap-6">
                        <!-- Step 1 -->
                        <div
                            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all relative group overflow-hidden">
                            <div
                                class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                            </div>
                            <div class="relative z-10">
                                <div
                                    class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4 font-bold shadow-sm">
                                    1</div>
                                <h4 class="font-bold text-xl text-slate-800 mb-2">Planlama</h4>
                                <p class="text-slate-500 text-sm leading-relaxed">
                                    Mekanınızı inceliyor, ışık ve açı planlaması yaparak en doğru zamanı belirliyoruz.
                                    İsteklerinize özel shot-list oluşturuyoruz.
                                </p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div
                            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all relative group overflow-hidden">
                            <div
                                class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                            </div>
                            <div class="relative z-10">
                                <div
                                    class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl mb-4 font-bold shadow-sm">
                                    2</div>
                                <h4 class="font-bold text-xl text-slate-800 mb-2">Çekim ve Prodüksiyon</h4>
                                <p class="text-slate-500 text-sm leading-relaxed">
                                    Belirlenen gün ve saatte, profesyonel ekipmanlarımızla (Drone, 360, Gimbal) mekanı
                                    en estetik haliyle kayıt altına alıyoruz.
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div
                            class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg transition-all relative group overflow-hidden">
                            <div
                                class="absolute top-0 right-0 w-24 h-24 bg-green-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                            </div>
                            <div class="relative z-10">
                                <div
                                    class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-2xl mb-4 font-bold shadow-sm">
                                    3</div>
                                <h4 class="font-bold text-xl text-slate-800 mb-2">Teslimat</h4>
                                <p class="text-slate-500 text-sm leading-relaxed">
                                    Çekilen görselleri retouch işleminden geçiriyor, videolara kurgu ve renk düzenlemesi
                                    yaparak dijital ortamda teslim ediyoruz.
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
                        class="bg-indigo-900 text-white p-8 rounded-3xl shadow-xl relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300">
                        <!-- Decorative bg -->
                        <div
                            class="absolute -top-10 -right-10 w-40 h-40 bg-brand-500 rounded-full blur-3xl opacity-20 group-hover:opacity-30 transition-opacity">
                        </div>

                        <div class="relative z-10 text-center">
                            <h3 class="font-bold text-2xl mb-3"><?= htmlspecialchars($locationName) ?> Çekim Fırsatı
                            </h3>
                            <p class="text-indigo-200 text-sm mb-8 leading-relaxed">
                                Profesyonel ekibimizle projeniz için en uygun çözümü üretelim. Hemen fiyat teklifi alın.
                            </p>

                            <button onclick="openQuoteWizard('mimari')"
                                class="w-full py-4 bg-brand-500 hover:bg-brand-400 text-white font-bold rounded-xl shadow-lg shadow-brand-900/50 transition-all flex items-center justify-center gap-2">
                                <span>Hemen Fiyat Hesapla</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <p class="mt-4 text-xs text-indigo-400 flex items-center justify-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Ücretsiz Danışmanlık</span>
                            </p>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-4">
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                            <div
                                class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                                📷</div>
                            <span class="font-semibold text-slate-700">10+ Yıl Deneyim</span>
                        </div>
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                            <div
                                class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl">
                                🚀</div>
                            <span class="font-semibold text-slate-700">Hızlı Teslimat</span>
                        </div>
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50">
                            <div
                                class="w-12 h-12 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center text-xl">
                                ⭐</div>
                            <span class="font-semibold text-slate-700">%100 Memnuniyet</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php
// Auto-render Gallery if folder is set (placed at the bottom for all styles)
if (!empty($post->gallery_folder_id)) {
    echo render_media_gallery($post->gallery_folder_id, 'Portfolyo');
}

include __DIR__ . '/../partials/example-works.php';

// Example Works & Other Services
include __DIR__ . '/../partials/services-grid.php';

include __DIR__ . '/../page-footer.php'; ?>