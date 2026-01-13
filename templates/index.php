<?php
/**
 * Main Index / Blog Template
 * Refactored for Tailwind CSS
 */
include __DIR__ . '/page-header.php';

// Get hero image
$randomPhoto = get_random_pexels_photo();
$heroImage = $randomPhoto ? $randomPhoto['src'] : 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg';
?>

<section class="relative py-24 bg-slate-900 flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Blog Hero" class="w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
    </div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <h1 class="font-heading font-bold text-5xl md:text-6xl text-white mb-6 tracking-tight">
            Blog & Haberler
        </h1>
        <p class="text-xl text-slate-200 max-w-2xl mx-auto font-light">
            Mekan fotoğrafçılığı hakkında ipuçları ve güncel haberler.
        </p>
    </div>
</section>

<main class="py-24 bg-slate-50">
    <div class="container mx-auto px-4">
        <!-- Since this is a static site structure mostly, just show standard content or placeholder -->
        <?php if (!empty($posts)): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($posts as $p): ?>
                    <div
                        class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 group">
                        <div class="p-8">
                            <h2 class="font-bold text-2xl text-slate-900 mb-4 group-hover:text-brand-600 transition-colors">
                                <a href="/<?= $p['slug'] ?>">
                                    <?= htmlspecialchars($p['title']) ?>
                                </a>
                            </h2>
                            <p class="text-slate-600 mb-6 line-clamp-3 leading-relaxed">
                                <?= htmlspecialchars($p['excerpt']) ?>
                            </p>
                            <a href="/<?= $p['slug'] ?>"
                                class="inline-flex items-center text-brand-600 font-semibold group-hover:gap-2 transition-all">
                                Devamını Oku ->
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-slate-100">
                <p class="text-slate-500 text-lg">Şu an için içerik bulunamadı.</p>
                <a href="/" class="inline-block mt-6 text-brand-600 font-semibold hover:underline">Ana Sayfaya Dön</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/page-footer.php'; ?>