<?php include __DIR__ . '/../page-header.php'; ?>
<main class="page-content">
    <div class="container">
        <h1>
            <?= e($post->title) ?>
        </h1>
        <div class="content">
            <?= $post->content ?>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../page-footer.php'; ?>