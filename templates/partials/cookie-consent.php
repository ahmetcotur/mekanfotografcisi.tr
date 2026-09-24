<?php
/**
 * Cookie Consent Banner Partial
 * Small card in the corner; remembers the choice in localStorage.
 */
?>
<div id="cookie-consent-banner" role="region" aria-label="Çerez bildirimi"
    class="fixed bottom-24 left-4 right-4 z-[95] flex max-w-sm items-center gap-4 rounded-2xl border border-line bg-white p-4 shadow-lift md:block md:p-5 md:bottom-6 md:left-6 md:right-auto"
    hidden>
    <p class="text-xs leading-relaxed text-ink-soft md:text-sm">
        Deneyiminizi iyileştirmek için çerez kullanıyoruz. Ayrıntılar için
        <a href="/cerez-politikasi" class="font-medium text-ink underline underline-offset-2">çerez politikamıza</a> bakabilirsiniz.
    </p>
    <button type="button" onclick="acceptCookies()" class="btn btn-dark btn-sm shrink-0 md:mt-4">Anladım</button>
</div>

<script>
    (function () {
        var accepted = false;
        try { accepted = !!localStorage.getItem('cookie_consent'); } catch (e) { }
        if (!accepted) {
            setTimeout(function () { document.getElementById('cookie-consent-banner').hidden = false; }, 800);
        }
    })();

    function acceptCookies() {
        try { localStorage.setItem('cookie_consent', 'true'); } catch (e) { }
        document.getElementById('cookie-consent-banner').hidden = true;
    }
</script>
