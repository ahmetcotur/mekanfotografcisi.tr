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
<section class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-950">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Hero"
            class="w-full h-full object-cover opacity-30 animate-pulse-subtle">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
        <div
            class="absolute inset-0 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:40px_40px] opacity-10">
        </div>
        <!-- Light sweep effect -->
        <div class="absolute inset-0 bg-gradient-to-tr from-brand-500/10 via-transparent to-blue-500/10 opacity-50">
        </div>
    </div>

    <div class="relative z-10 container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center animate-slide-up">
            <h1
                class="font-heading font-black text-5xl md:text-8xl text-white mb-8 tracking-tighter drop-shadow-2xl leading-[0.9]">
                <span class="text-gradient"><?= htmlspecialchars($post->title) ?></span>
            </h1>
            <?php if ($post->excerpt): ?>
                <p class="text-xl md:text-2xl text-slate-400 max-w-2xl mx-auto font-light leading-relaxed">
                    <?= htmlspecialchars($post->excerpt) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Content -->
<main class="relative py-24 bg-slate-50 overflow-hidden">
    <!-- Decorative background -->
    <div
        class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-slate-950 to-transparent pointer-events-none opacity-5">
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto">
            <div class="glass-panel p-8 md:p-16 mb-20 animate-slide-up">
                <article class="prose prose-lg prose-slate max-w-none 
                    prose-headings:font-heading prose-headings:font-black prose-headings:tracking-tighter
                    prose-h2:text-4xl prose-h2:mb-8
                    prose-p:text-slate-600 prose-p:leading-relaxed
                    prose-a:text-brand-600 prose-a:no-underline hover:prose-a:text-brand-700
                    prose-strong:text-slate-900">
                    <?= do_shortcode($post->content) ?>
                </article>
            </div>

            <?php
            // Auto-render Gallery if folder is set
            if (!empty($post->gallery_folder_id)) {
                echo '<div class="mb-24 animate-slide-up" style="animation-delay: 0.1s;">';
                echo render_media_gallery($post->gallery_folder_id, 'Portfolyo');
                echo '</div>';
            }
            ?>

            <!-- CTA -->
            <div class="bg-slate-900 rounded-5xl p-12 md:p-20 text-center text-white relative overflow-hidden shadow-2xl group animate-slide-up"
                style="animation-delay: 0.2s;">
                <div class="absolute inset-0 z-0 opacity-40">
                    <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg" alt="CTA BG"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-[2s]">
                    <div class="absolute inset-0 bg-gradient-to-br from-brand-900/80 to-slate-900/90 backdrop-blur-sm">
                    </div>
                </div>
                <div class="relative z-10">
                    <h2 class="font-heading font-black text-4xl md:text-6xl mb-8 tracking-tight">Projeniz İçin Teklif
                        Alın
                    </h2>
                    <p class="text-brand-100 mb-12 text-xl md:text-2xl font-light max-w-3xl mx-auto leading-relaxed">
                        Mekanınızın vizyonunu dijital dünyaya en profesyonel şekilde yansıtmak için <span
                            class="text-white font-bold">doğru adrestesiniz.</span>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <button onclick="openQuoteWizard()"
                            class="px-12 py-6 bg-white text-slate-900 rounded-3xl font-black text-xl hover:bg-brand-50 transition-all hover:scale-105 active:scale-95 shadow-2xl">
                            Hemen Teklif Al
                        </button>
                        <a href="tel:<?= get_setting('phone_url') ?>"
                            class="px-12 py-6 bg-brand-600/20 backdrop-blur-md border border-brand-500/30 text-white rounded-3xl font-black text-xl hover:bg-brand-600/40 transition-all hover:scale-105 active:scale-95 flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            Bizi Arayın
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../partials/example-works.php'; ?>
<?php include __DIR__ . '/../page-footer.php'; ?>