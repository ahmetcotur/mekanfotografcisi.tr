<?php
/**
 * 404 Error Page
 * Refactored for Tailwind CSS
 */
http_response_code(404);
include __DIR__ . '/../page-header.php';
?>

<section class="min-h-[70vh] flex items-center justify-center bg-slate-50 relative overflow-hidden">
    <div class="absolute inset-0 z-0 opacity-10">
        <div class="absolute top-10 left-10 w-64 h-64 bg-brand-500 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-cyan-500 rounded-full blur-3xl animate-pulse"
            style="animation-delay: 1s;"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4 text-center">
        <h1 class="font-heading font-extrabold text-9xl text-slate-900 mb-4 tracking-tighter drop-shadow-sm">
            404
        </h1>
        <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-6">
            Sayfa Bulunamadı
        </h2>
        <p class="text-xl text-slate-600 max-w-lg mx-auto mb-10 leading-relaxed">
            Aradığınız sayfa taşınmış, silinmiş veya hiç var olmamış olabilir.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/"
                class="inline-flex items-center justify-center px-8 py-4 bg-brand-600 text-white rounded-xl font-bold shadow-lg shadow-brand-500/25 hover:bg-brand-500 transition-all hover:scale-105">
                Ana Sayfaya Dön
            </a>
            <a href="/services"
                class="inline-flex items-center justify-center px-8 py-4 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold hover:bg-slate-50 hover:border-slate-300 transition-all">
                Hizmetleri İncele
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../page-footer.php'; ?>