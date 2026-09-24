<?php include __DIR__ . '/../page-header.php'; ?>
<main id="main" class="container-page max-w-3xl py-12 md:py-16">
    <h1 class="h-display text-4xl lg:text-5xl"><?= e($post->title) ?></h1>
    <div class="prose prose-lg mt-8 max-w-none">
        <?= prepare_page_content($post->content, $post->title) ?>
    </div>
</main>
<?php include __DIR__ . '/../page-footer.php'; ?>
