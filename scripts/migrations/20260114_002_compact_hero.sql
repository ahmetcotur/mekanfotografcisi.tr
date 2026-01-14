-- Migration: M20260114_002_Compact_Hero
-- Description: Makes the hero section title more compact by reducing it to 2 lines and optimizing font sizes.

UPDATE posts SET content = '<!-- Hero Section -->
<section class="relative min-h-[85vh] flex items-center justify-center overflow-hidden bg-slate-900" id="hero-slider">
    <div class="absolute inset-0 z-0">
        <div id="hero-slides" class="w-full h-full"></div>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px]"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/60 via-transparent to-slate-900"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 container mx-auto px-4">
        <div class="max-w-5xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 py-1 px-4 rounded-full bg-white/10 border border-white/20 text-brand-300 text-xs font-semibold tracking-widest uppercase mb-8 backdrop-blur-xl">
                Mimari & Mekan Fotoğrafçılığı
            </div>
            
            <h1 class="font-heading font-black text-4xl md:text-6xl lg:text-7xl text-white mb-10 leading-tight tracking-tight drop-shadow-2xl">
                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 mb-2">
                    <div id="prefix-morph" class="h-12 md:h-16 lg:h-20 min-w-[200px] md:min-w-[350px]"></div>
                    <div id="suffix-morph" class="h-12 md:h-16 lg:h-20 min-w-[150px] md:min-w-[250px] text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-cyan-300"></div>
                </div>
                <div class="text-white">Dönüştürüyoruz</div>
            </h1>

            <p class="text-base md:text-lg lg:text-xl text-slate-200/90 max-w-2xl mx-auto mb-12 leading-relaxed font-light">
                Estetik ve tekniği birleştirerek mekanlarınızın ruhunu karelere hapsediyoruz. 
                <span class="block mt-1 font-medium text-brand-300 text-sm md:text-base">İşletmenize değer katan profesyonel çekimler.</span>
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <button onclick="openQuoteWizard()" class="w-full sm:w-auto px-8 py-4 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-bold transition-all hover:scale-105">
                    Fiyat Hesapla
                </button>
                <a href="/portfolio" class="w-full sm:w-auto px-8 py-4 bg-white/5 hover:bg-white/10 text-white border border-white/20 rounded-xl font-bold backdrop-blur-xl transition-all hover:scale-105">
                    Portfolyoyu İncele
                </a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    if (window.initGooeyText) {
        window.initGooeyText("#prefix-morph", ["Mekanınızı", "Otelinizi", "Restoranınızı", "Villanızı"], { morphTime: 1.5, cooldownTime: 1, textClassName: "text-white" });
        window.initGooeyText("#suffix-morph", ["Sanata", "Markaya", "Satışa", "Hikayeye"], { morphTime: 1.5, cooldownTime: 1, textClassName: "text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-cyan-300 font-black italic" });
    }
});
</script>' || split_part(content, '<!-- Stats Section -->', 2) 
WHERE slug = 'homepage';
