<?php
/**
 * Generic page template (legal pages, custom pages, ...)
 */
include __DIR__ . '/../page-header.php';
$pageContent = prepare_page_content(do_shortcode($post->content), $post->title);
$isRichContent = strpos($pageContent, 'class=') !== false && strpos($pageContent, 'class="prose') === false;
?>

<main id="main">
    <header class="border-b border-line">
        <div class="container-page max-w-3xl py-10 md:py-14">
            <h1 class="h-display text-4xl lg:text-5xl"><?= e($post->title) ?></h1>
            <?php if ($post->excerpt): ?>
                <p class="lead mt-5"><?= e($post->excerpt) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <div class="container-page <?= $isRichContent ? '' : 'max-w-3xl' ?> py-12">
        <div class="<?= $isRichContent ? '' : 'prose prose-lg max-w-none' ?>">
            <?= $pageContent ?>
        </div>
    </div>

    <?php
    if (!empty($post->gallery_folder_id)) {
        echo render_media_gallery($post->gallery_folder_id, 'Portfolyo');
    }
    ?>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
