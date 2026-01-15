<?php include __DIR__ . '/../page-header.php'; ?>
<main class="min-h-screen flex items-center justify-center relative overflow-hidden bg-slate-950">
    <!-- Background Decoration -->
    <div class="absolute inset-0 z-0">
        <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg" alt="Background"
            class="w-full h-full object-cover opacity-20 animate-pulse-subtle">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
        <div
            class="absolute inset-0 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:40px_40px] opacity-10">
        </div>
        <!-- Light blobs -->
        <div
            class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-brand-500/10 rounded-full blur-[120px] animate-pulse-subtle">
        </div>
        <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-blue-500/10 rounded-full blur-[120px] animate-pulse-subtle"
            style="animation-delay: 1s;"></div>
    </div>

    <div class="relative z-10 container mx-auto px-4 text-center py-20">
        <!-- Error Code -->
        <h1
            class="text-[15rem] md:text-[20rem] font-black text-white/5 leading-none select-none tracking-tighter animate-slide-up">
            404
        </h1>

        <!-- Content -->
        <div class="-mt-20 md:-mt-32 space-y-10 animate-slide-up" style="animation-delay: 0.2s;">
            <div>
                <h2 class="text-4xl md:text-6xl font-black text-white tracking-tighter mb-4">
                    Kayıp mı <span class="text-gradient">Oldunuz?</span>
                </h2>
                <p class="text-xl md:text-2xl text-slate-400 max-w-lg mx-auto leading-relaxed font-light">
                    Aradığınız kareyi burada bulamadık ama size en yakışan hizmeti ana sayfamızda bulabilirsiniz.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-6 pt-8">
                <a href="/"
                    class="group relative px-10 py-5 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl font-black text-lg transition-all hover:scale-105 active:scale-95 shadow-2xl flex items-center gap-3 overflow-hidden">
                    <span class="relative z-10 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                        Ana Sayfaya Dön
                    </span>
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000">
                    </div>
                </a>
                <a href="javascript:history.back()"
                    class="px-10 py-5 bg-white/5 hover:bg-white/10 text-white border border-white/20 rounded-2xl font-black text-lg backdrop-blur-md transition-all hover:scale-105 active:scale-95 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Geri Dön
                </a>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../page-footer.php'; ?>