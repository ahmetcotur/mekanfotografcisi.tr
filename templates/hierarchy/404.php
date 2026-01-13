<?php include __DIR__ . '/../page-header.php'; ?>
<main class="min-h-[70vh] flex items-center justify-center relative overflow-hidden bg-slate-900">
    <!-- Background Decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4 text-center">
        <!-- Error Code -->
        <h1
            class="text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-blue-400 opacity-20 select-none">
            404
        </h1>

        <!-- Content -->
        <div class="-mt-12 space-y-6">
            <h2 class="text-3xl md:text-4xl font-bold text-white tracking-tight">
                Aradığınız Sayfa Bulunamadı
            </h2>
            <p class="text-lg text-slate-400 max-w-lg mx-auto leading-relaxed">
                Üzgünüz, gitmeye çalıştığınız sayfa silinmiş, taşınmış veya hiç var olmamış olabilir.
            </p>

            <div class="flex items-center justify-center gap-4 pt-4">
                <a href="/"
                    class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white px-8 py-3.5 rounded-xl font-medium transition-all hover:scale-105 shadow-lg shadow-brand-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    Ana Sayfaya Dön
                </a>
                <a href="javascript:history.back()"
                    class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-300 px-8 py-3.5 rounded-xl font-medium transition-all hover:text-white border border-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Geri Gel
                </a>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../page-footer.php'; ?>