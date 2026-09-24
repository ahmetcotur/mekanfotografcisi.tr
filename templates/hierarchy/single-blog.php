<?php
/**
 * Single blog post (/blog/{slug})
 */
include __DIR__ . '/../page-header.php';
global $db;

$heroImage = !empty($post->featured_image) ? $post->featured_image : '';
$postDate = strtotime($post->created_at);

$relatedPosts = array_values(array_filter($db->select('posts', [
    'post_type' => 'blog',
    'post_status' => 'publish',
    'limit' => 4,
    'order' => 'RANDOM()'
]), function ($p) use ($post) {
    return $p['id'] !== $post->id;
}));
?>

<main id="main">
    <article>
        <header class="container-page max-w-3xl pb-8 pt-10 md:pt-14">
            <nav aria-label="Konum" class="mb-6 text-sm text-ink-muted">
                <a href="/blog" class="inline-flex items-center gap-1 hover:text-ink"><?= icon('arrow-left', 'h-4 w-4') ?> Blog</a>
            </nav>
            <time class="text-sm text-ink-muted" datetime="<?= e(date('Y-m-d', $postDate)) ?>"><?= e(date('d.m.Y', $postDate)) ?></time>
            <h1 class="h-display mt-3 text-4xl lg:text-5xl"><?= e($post->title) ?></h1>
            <?php if ($post->excerpt): ?>
                <p class="lead mt-5"><?= e($post->excerpt) ?></p>
            <?php endif; ?>
        </header>

        <?php if ($heroImage): ?>
            <div class="container-page max-w-5xl">
                <div class="photo-placeholder aspect-[16/9] overflow-hidden rounded-3xl">
                    <img src="<?= e($heroImage) ?>" alt="" class="h-full w-full object-cover" fetchpriority="high">
                </div>
            </div>
        <?php endif; ?>

        <div class="container-page max-w-3xl py-12">
            <div class="prose prose-lg max-w-none">
                <?= prepare_page_content(do_shortcode($post->content), $post->title) ?>
            </div>
        </div>
    </article>

    <?php if (!empty($relatedPosts)): ?>
        <section class="section border-t border-line bg-white">
            <div class="container-page">
                <h2 class="h-section">Diğer yazılar</h2>
                <div class="mt-8 grid gap-8 md:grid-cols-3">
                    <?php foreach (array_slice($relatedPosts, 0, 3) as $rpost): ?>
                        <a href="/blog/<?= e(preg_replace('#^blog/#', '', $rpost['slug'])) ?>" class="group">
                            <h3 class="h-card group-hover:text-brand-700"><?= e($rpost['title']) ?></h3>
                            <?php if (!empty($rpost['excerpt'])): ?>
                                <p class="mt-2 line-clamp-2 text-sm text-ink-soft"><?= e($rpost['excerpt']) ?></p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php include __DIR__ . '/../partials/cta-band.php'; ?>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
