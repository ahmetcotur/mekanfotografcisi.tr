-- Migration: M20260114_001_Localize_URLs_and_New_Hero
-- Description: Updates URL slugs and homepage hero content to the new premium design.

-- 1. URL and Slug Updates
UPDATE posts SET slug = 'hizmet-bolgeleri' WHERE slug = 'locations';
-- STATEMENT
UPDATE posts SET slug = 'hizmetlerimiz' WHERE slug = 'services';
-- STATEMENT
UPDATE posts SET slug = REPLACE(slug, 'services/', 'hizmetlerimiz/') WHERE slug LIKE 'services/%';
-- STATEMENT

-- 2. New Premium Hero Design and Gooey Animation
UPDATE posts SET content = '<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-slate-900" id="hero-slider">
    <div class="absolute inset-0 z-0">
        <div id="hero-slides" class="w-full h-full"></div>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px]"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/60 via-transparent to-slate-900"></div>
        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:40px_40px]"></div>
    </div>
    <div class="relative z-10 container mx-auto px-4 text-center">
        <div class="inline-flex items-center gap-2 py-1 px-4 rounded-full bg-white/10 border border-white/20 text-brand-300 text-xs font-semibold tracking-widest uppercase mb-10 backdrop-blur-xl shadow-2xl">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
            </span>
            Mimari & Mekan Fotoğrafçılığı
        </div>
        <h1 class="font-heading font-extrabold text-5xl md:text-8xl lg:text-9xl text-white mb-12 leading-[1.05] tracking-tight">
            <div class="flex flex-col items-center justify-center space-y-2 md:space-y-4">
                <div id="prefix-morph" class="h-16 md:h-28 lg:h-32 w-full max-w-4xl"></div>
                <div id="suffix-morph" class="h-16 md:h-28 lg:h-32 w-full max-w-3xl"></div>
                <div class="text-white drop-shadow-[0_10px_10px_rgba(0,0,0,0.5)]">Dönüştürüyoruz</div>
            </div>
        </h1>
        <p class="text-base md:text-xl lg:text-2xl text-slate-200/90 max-w-3xl mx-auto mb-14 font-light">
            Estetik ve tekniği birleştirerek mekanlarınızın ruhunu karelere hapsediyoruz. 
            <span class="block mt-2 font-medium text-brand-300">İşletmenize değer katan profesyonel çözümler.</span>
        </p>
        <div class="flex flex-col sm:flex-row gap-5 justify-center items-center">
            <button onclick="openQuoteWizard()" class="px-10 py-5 bg-brand-600 text-white rounded-2xl font-bold text-lg shadow-xl shadow-brand-500/20 transition-all hover:scale-105">Hemen Fiyat Hesapla</button>
            <a href="/portfolio" class="px-10 py-5 bg-white/5 text-white border border-white/20 rounded-2xl font-bold text-lg backdrop-blur-xl transition-all hover:scale-105">Portfolyoyu İncele</a>
        </div>
    </div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
    if (window.initGooeyText) {
        window.initGooeyText("#prefix-morph", ["Mekanınızı", "Otelinizi", "Restoranınızı", "Villanızı", "Ofisinizi"], { morphTime: 1.8, cooldownTime: 1.2, textClassName: "text-white" });
        window.initGooeyText("#suffix-morph", ["Sanata", "Markaya", "Satışa", "Hikayeye", "Prestije"], { morphTime: 1.8, cooldownTime: 1.2, textClassName: "text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-cyan-300 font-black italic" });
    }
});
</script>' || split_part(content, '<!-- Stats Section -->', 2) 
WHERE slug = 'homepage';
-- STATEMENT
UPDATE post_meta SET meta_value = REPLACE(meta_value::text, '/services/', '/hizmetlerimiz/')::jsonb;
-- STATEMENT
UPDATE posts SET content = REPLACE(content, '/services/', '/hizmetlerimiz/');
-- STATEMENT
UPDATE posts SET content = REPLACE(content, '/services"', '/hizmetlerimiz"');
-- STATEMENT
UPDATE posts SET content = REPLACE(content, '/locations', '/hizmet-bolgeleri');
