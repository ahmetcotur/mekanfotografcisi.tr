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
    <section class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-950">
        <div class="absolute inset-0 z-0">
            <img src="<?= htmlspecialchars($heroImage) ?>"
                class="w-full h-full object-cover opacity-30 animate-pulse-subtle">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
            <div
                class="absolute inset-0 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:40px_40px] opacity-10">
            </div>
        </div>
        <div class="relative z-10 container mx-auto px-4 text-center">
            <div class="animate-slide-up">
                <span
                    class="inline-block px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-[10px] font-black tracking-[0.2em] uppercase mb-8 backdrop-blur-xl">
                    <?= htmlspecialchars($post->title) ?>
                </span>
                <h1 class="font-heading font-black text-5xl md:text-8xl text-white mb-8 tracking-tighter drop-shadow-2xl">
                    <?= htmlspecialchars($post->title) ?> <span class="text-gradient">Hizmeti</span>
                </h1>
            </div>
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