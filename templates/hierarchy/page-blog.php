<?php
/**
 * Blog Archive Page - Premium UI
 */
include __DIR__ . '/../page-header.php';
global $db;

// Fetch all published blog posts
$blogPosts = $db->select('posts', [
    'post_type' => 'blog',
    'post_status' => 'publish',
    'limit' => 50,
    'order' => 'created_at DESC'
]);

$randomPhoto = get_random_pexels_photo();
$heroImage = $randomPhoto ? $randomPhoto['src'] : 'https://images.pexels.com/photos/262508/pexels-photo-262508.jpeg';
?>

<!-- Hero Section -->
<section class="relative h-[60vh] min-h-[500px] flex items-center justify-center overflow-hidden bg-slate-950">
    <div class="absolute inset-0 z-0">
        <img src="<?= htmlspecialchars($heroImage) ?>" alt="Blog"
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
                Sektörel Yazılar & Haberler
            </span>
            <h1 class="font-heading font-black text-5xl md:text-8xl text-white mb-8 tracking-tighter drop-shadow-2xl">
                Blog
            </h1>
            <p class="text-xl md:text-2xl text-slate-400 max-w-2xl mx-auto font-light leading-relaxed">
                Mekan fotoğrafçılığı dünyasından <span class="text-white font-medium">güncel bilgiler, ipuçları ve
                    trendler.</span>
            </p>
        </div>
    </div>
</section>

<!-- Blog Grid -->
<main class="py-32 bg-slate-50">
    <div class="container mx-auto px-4">

        <?php if (empty($blogPosts)): ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-lg">Henüz yayınlanmış bir yazı bulunmuyor.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php foreach ($blogPosts as $post):
                    $postTitle = htmlspecialchars($post['title']);
                    $postSlug = htmlspecialchars($post['slug']);
                    $postExcerpt = htmlspecialchars($post['excerpt'] ?: 'Mekan fotoğrafçılığı üzerine bilgilendirici bir yazı.');
                    $postDate = date('d.m.Y', strtotime($post['created_at']));

                    // Try to find an image in meta or use random
                    $postImage = 'https://images.pexels.com/photos/7045926/pexels-photo-7045926.jpeg'; // Default
                    ?>
                    <article
                        class="group relative bg-white rounded-5xl overflow-hidden shadow-[0_32px_64px_-20px_rgba(0,0,0,0.06)] border border-slate-100 flex flex-col hover-lift">
                        <div class="aspect-[16/10] overflow-hidden relative">
                            <img src="<?= $postImage ?>" alt="<?= $postTitle ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000 grayscale-[0.5] group-hover:grayscale-0">
                            <div class="absolute top-6 left-6">
                                <span
                                    class="px-4 py-1 bg-white/90 backdrop-blur-md rounded-full text-[10px] font-black text-slate-900 uppercase tracking-widest leading-none">
                                    <?= $postDate ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-10 flex flex-col flex-1">
                            <h3
                                class="text-2xl font-black text-slate-900 mb-4 tracking-tight leading-tight group-hover:text-brand-600 transition-colors">
                                <a href="/blog/<?= $postSlug ?>">
                                    <?= $postTitle ?>
                                </a>
                            </h3>
                            <p class="text-slate-500 text-sm leading-relaxed font-medium mb-8 line-clamp-3">
                                <?= $postExcerpt ?>
                            </p>
                            <div class="mt-auto">
                                <a href="/blog/<?= $postSlug ?>"
                                    class="inline-flex items-center gap-2 text-slate-900 font-bold text-xs uppercase tracking-widest group/btn">
                                    Devamını Oku
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                        stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform">
                                        <path d="M5 12h14" />
                                        <path d="m12 5 7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>