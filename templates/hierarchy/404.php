<?php
http_response_code(404);
$pageTitle = 'Sayfa bulunamadı';
$pageRobots = 'noindex, follow';
$appPage = false;
include __DIR__ . '/../page-header.php';
?>

<main id="main" class="flex flex-1 items-center">
    <div class="container-page max-w-2xl py-20 text-center">
        <p class="font-display text-7xl font-semibold text-brand-600">404</p>
        <h1 class="h-section mt-4">Aradığın kareyi bulamadık</h1>
        <p class="lead mt-4">Sayfa taşınmış ya da hiç var olmamış olabilir. Şunlardan biri işine yarayabilir:</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="/" class="btn btn-dark btn-lg">Ana sayfa</a>
            <a href="/fotografcilar" class="btn btn-outline btn-lg">Fotoğrafçılar</a>
            <button type="button" onclick="openQuoteWizard()" class="btn btn-outline btn-lg">Teklif al</button>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
