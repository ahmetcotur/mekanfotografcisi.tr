<?php
/**
 * Cookie Consent Banner Partial
 * 
 * Displays a fixed bottom banner for cookie consent compliance.
 * Uses localStorage to remember user choice.
 */
?>
<div id="cookie-consent-banner"
    class="fixed bottom-0 inset-x-0 z-[9999] hidden translate-y-full transition-transform duration-500 ease-out">
    <div
        class="bg-slate-900/90 backdrop-blur-md border-t border-white/10 p-4 md:p-6 shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">

            <div class="flex items-start gap-4 max-w-3xl">
                <div class="hidden md:flex w-12 h-12 rounded-xl bg-brand-500/10 items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-brand-500">
                        <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5" />
                        <path d="M8.5 8.5v.01" />
                        <path d="M16 15.5v.01" />
                        <path d="M12 12v.01" />
                        <path d="M11 17v.01" />
                        <path d="M7 14v.01" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-bold text-sm mb-1">Çerez Tercihleriniz</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Sizlere daha iyi bir deneyim sunmak için çerezleri kullanıyoruz. Sitemizi kullanarak çerez
                        politikamızı kabul etmiş olursunuz.
                        Detaylı bilgi için <a href="/cerez-politikasi"
                            class="text-brand-400 hover:text-brand-300 underline underline-offset-2 transition-colors">Çerez
                            Politikamızı</a> inceleyebilirsiniz.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <button onclick="acceptCookies()"
                    class="flex-1 md:flex-none px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-lg shadow-brand-600/20 active:scale-95">
                    Kabul Et
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const banner = document.getElementById('cookie-consent-banner');

        // Check if user has already accepted cookies
        if (!localStorage.getItem('cookie_consent')) {
            // Show banner with a slight delay for smooth entrance
            setTimeout(() => {
                banner.classList.remove('hidden');
                // Trigger reflow
                void banner.offsetWidth;
                banner.classList.remove('translate-y-full');
            }, 1000);
        }
    });

    function acceptCookies() {
        const banner = document.getElementById('cookie-consent-banner');

        // Set local storage
        localStorage.setItem('cookie_consent', 'true');

        // Animate out
        banner.classList.add('translate-y-full');

        // Hide after anmation
        setTimeout(() => {
            banner.classList.add('hidden');
        }, 500);
    }
</script>