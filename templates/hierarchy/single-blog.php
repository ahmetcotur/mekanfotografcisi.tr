<?php
/**
 * Single Blog Post Template
 */
include __DIR__ . '/../page-header.php';
global $db;

$randomPhoto = get_random_pexels_photo();
$heroImage = $randomPhoto ? $randomPhoto['src'] : 'https://images.pexels.com/photos/2079246/pexels-photo-2079246.jpeg';
$postTitle = $post->title;
$postDate = date('d.m.Y', strtotime($post->created_at));
?>

<!-- Blog Hero -->
<section class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-950">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="<?= htmlspecialchars($postTitle) ?>"
            class="w-full h-full object-cover opacity-30 animate-pulse-subtle">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
        <div
            class="absolute inset-0 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:40px_40px] opacity-10">
        </div>
    </div>

    <div class="relative z-10 container mx-auto px-4 text-center py-24 animate-slide-up">
        <span
            class="inline-block px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-[10px] font-black tracking-[0.2em] uppercase mb-8 backdrop-blur-xl">
            Blog //
            <?= $postDate ?>
        </span>
        <h1 class="font-heading font-black text-5xl md:text-7xl text-white mb-8 tracking-tighter drop-shadow-2xl">
            <?= htmlspecialchars($postTitle) ?>
        </h1>
        <?php if ($post->excerpt): ?>
            <p class="text-xl md:text-2xl text-slate-400 max-w-4xl mx-auto font-light leading-relaxed mb-8">
                <?= htmlspecialchars($post->excerpt) ?>
            </p>
        <?php endif; ?>
    </div>
</section>

<!-- Main Content -->
<main class="py-24 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <div
                class="bg-white rounded-5xl p-10 md:p-16 shadow-[0_32px_64px_-20px_rgba(0,0,0,0.06)] border border-slate-100 relative overflow-hidden group">
                <div class="relative z-10">
                    <div class="prose prose-xl prose-slate max-w-none text-slate-600 leading-relaxed font-medium">
                        <?= do_shortcode($post->content) ?>
                    </div>
                </div>
            </div>

            <!-- CTA Section Inside Blog -->
            <div class="mt-16 bg-slate-900 text-white p-12 rounded-5xl shadow-2xl relative overflow-hidden group">
                <div class="absolute inset-0 z-0 opacity-20">
                    <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg" alt="CTA BG"
                        class="w-full h-full object-cover">
                </div>
                <div class="relative z-10 text-center">
                    <h3 class="font-black text-3xl mb-6 tracking-tight">Profesyonel Çekimlere mi İhtiyacınız Var?</h3>
                    <p class="text-brand-100 mb-8 text-lg font-light max-w-2xl mx-auto">
                        Mekanınızın hikayesini en etkileyici şekilde anlatalım. Hemen ücretsiz teklif alın.
                    </p>
                    <button onclick="openQuoteWizard()"
                        class="px-10 py-5 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl font-black text-lg transition-all hover:scale-105 active:scale-95 shadow-xl">
                        Hemen Fiyat Al
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Include other blog posts for reading
$relatedPosts = $db->select('posts', [
    'post_type' => 'blog',
    'post_status' => 'publish',
    'limit' => 3,
    'order' => 'RANDOM()'
]);

if (!empty($relatedPosts)): ?>
    <section class="py-24 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-heading font-black text-slate-900 mb-12 tracking-tight">Diğer Yazılarımız</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <?php foreach ($relatedPosts as $rpost):
                    if ($rpost['id'] === $post->id)
                        continue;
                    ?>
                    <div class="group">
                        <h3
                            class="text-xl font-black text-slate-900 mb-4 tracking-tight group-hover:text-brand-600 transition-colors">
                            <a href="/blog/<?= $rpost['slug'] ?>">
                                <?= htmlspecialchars($rpost['title']) ?>
                            </a>
                        </h3>
                        <p class="text-slate-500 text-sm line-clamp-2">
                            <?= htmlspecialchars($rpost['excerpt']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/../page-footer.php'; ?>