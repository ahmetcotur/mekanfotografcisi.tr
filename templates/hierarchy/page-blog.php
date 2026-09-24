<?php
/**
 * Blog archive (/blog)
 */
$pageTitle = 'Blog';
$pageDescription = 'Mekan fotoğrafçılığı üzerine ipuçları, rehberler ve sektörden notlar.';
include __DIR__ . '/../page-header.php';
global $db;

$blogPosts = $db->select('posts', [
    'post_type' => 'blog',
    'post_status' => 'publish',
    'limit' => 50,
    'order' => 'created_at DESC'
]);

$heroEyebrow = 'Blog';
$heroTitle = 'Mekan fotoğrafçılığı üzerine';
$heroLead = 'Mekan sahipleri ve fotoğrafçılar için ipuçları, rehberler ve sektörden notlar.';
$heroCrumbs = [['href' => '/blog', 'label' => 'Blog']];
?>

<main id="main">
    <?php include __DIR__ . '/../partials/page-hero.php'; ?>

    <section class="section pt-10 md:pt-14">
        <div class="container-page">
            <?php if (empty($blogPosts)): ?>
                <div class="card p-10 text-center">
                    <p class="text-ink-muted">Henüz yayınlanmış bir yazı yok. Çok yakında burada olacağız.</p>
                    <a href="/hizmetlerimiz" class="btn btn-outline mt-6">Hizmetlere göz at</a>
                </div>
            <?php else: ?>
                <div class="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($blogPosts as $blogPost):
                        $href = '/blog/' . preg_replace('#^blog/#', '', $blogPost['slug']);
                        $image = $blogPost['featured_image'] ?? '';
                        $excerpt = $blogPost['excerpt'] ?: content_excerpt($blogPost['content'] ?? '');
                        ?>
                        <article class="group">
                            <a href="<?= e($href) ?>" class="photo-placeholder block aspect-[3/2] overflow-hidden rounded-2xl" tabindex="-1" aria-hidden="true">
                                <?php if ($image): ?>
                                    <img src="<?= e($image) ?>" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                <?php endif; ?>
                            </a>
                            <time class="mt-4 block text-sm text-ink-muted" datetime="<?= e(date('Y-m-d', strtotime($blogPost['created_at']))) ?>"><?= e(date('d.m.Y', strtotime($blogPost['created_at']))) ?></time>
                            <h2 class="h-card mt-1"><a href="<?= e($href) ?>" class="hover:text-brand-700"><?= e($blogPost['title']) ?></a></h2>
                            <?php if ($excerpt): ?>
                                <p class="mt-2 line-clamp-3 text-sm text-ink-soft"><?= e($excerpt) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
