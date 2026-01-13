<?php
/**
 * Generic singular template
 * Refactored for Tailwind CSS
 */
include __DIR__ . '/../page-header.php';

// Get hero image
$heroImageMeta = $post->getMeta('hero_image');
if ($heroImageMeta) {
    $heroImage = $heroImageMeta;
} else {
    $randomPhoto = get_random_pexels_photo();
    $heroImage = $randomPhoto ? $randomPhoto['src'] : 'https://images.pexels.com/photos/2079246/pexels-photo-2079246.jpeg'; // Fallback
}
?>

<!-- Simple Hero -->
<section class="relative py-24 bg-slate-900 flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Hero" class="w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-slate-900/40"></div>
    </div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <h1 class="font-heading font-bold text-4xl md:text-5xl text-white mb-6 tracking-tight drop-shadow-lg">
            <?= htmlspecialchars($post->title) ?>
        </h1>
        <?php if ($post->excerpt): ?>
            <p class="text-xl text-slate-200 max-w-2xl mx-auto font-light">
                <?= htmlspecialchars($post->excerpt) ?>
            </p>
        <?php endif; ?>
    </div>
</section>

<!-- Content -->
<main class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="prose prose-lg prose-slate max-w-3xl mx-auto mb-16">
            <?= do_shortcode($post->content) ?>
        </div>

        <?php
        // Auto-render Gallery if folder is set
        if (!empty($post->gallery_folder_id)) {
            echo render_media_gallery($post->gallery_folder_id, 'Portfolyo');
        }
        ?>

        <!-- CTA -->
        <div class="mt-20 pt-10 border-t border-slate-100 text-center">
            <h3 class="text-2xl font-bold text-slate-800 mb-6">Projeniz İçin Teklif Alın</h3>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/#iletisim"
                    class="inline-flex items-center justify-center px-8 py-3 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-500 transition-colors shadow-lg shadow-brand-500/20">
                    Hemen Teklif Al
                </a>
                <a href="tel:+905074677502"
                    class="inline-flex items-center justify-center px-8 py-3 bg-white border border-slate-200 text-slate-700 rounded-xl font-semibold hover:bg-slate-50 transition-colors">
                    Hemen Ara
                </a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../partials/example-works.php'; ?>
<?php include __DIR__ . '/../page-footer.php'; ?>