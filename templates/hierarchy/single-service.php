<?php
/**
 * Service-specific template
 * Supports both legacy text content and new Tailwind-rich content
 */
include __DIR__ . '/../page-header.php';
global $db;

// Check if content is "new style" (starts with <article or <section)
$isRichContent = (strpos(trim($post->content), '<article') === 0 || strpos(trim($post->content), '<section') === 0);

if ($isRichContent) {
    // Render rich content directly
    echo do_shortcode($post->content);

} else {
    // Legacy fallback for non-updated services
    // Get hero image
    $heroImageMeta = $post->getMeta('hero_image');
    if ($heroImageMeta) {
        $heroImage = $heroImageMeta;
    } else {
        $randomPhoto = get_random_pexels_photo();
        $heroImage = $randomPhoto ? $randomPhoto['src'] : '/assets/images/hero-bg.jpg';
    }
    ?>
    <section class="relative py-32 bg-slate-900 flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="<?= htmlspecialchars($heroImage) ?>" class="w-full h-full object-cover opacity-40">
        </div>
        <div class="relative z-10 container mx-auto px-4 text-center">
            <span
                class="inline-block py-1 px-3 rounded-full bg-blue-500/20 text-blue-100 text-sm font-semibold mb-4 backdrop-blur-sm border border-blue-500/30">
                <?= htmlspecialchars($post->title) ?>
            </span>
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 tracking-tight">
                <?= htmlspecialchars($post->title) ?> Hizmeti
            </h1>
        </div>
    </section>

    <div class="container mx-auto px-4 py-16">
        <div class="prose prose-lg prose-slate max-w-none mb-16">
            <?= do_shortcode($post->content) ?>
        </div>

    </div>

    <?php
    // Include Other Services logic
    include __DIR__ . '/../partials/services-grid.php';
}

// Auto-render Gallery if folder is set (placed at the bottom for all styles)
if (!empty($post->gallery_folder_id)) {
    echo render_media_gallery($post->gallery_folder_id, 'Portfolyo');
}

include __DIR__ . '/../partials/example-works.php';
include __DIR__ . '/../page-footer.php';
?>