-- Migration: M20260114_004_Full_Homepage_Restore
-- Description: Restores missing sections and fixes Hero cropping with larger containers and overflow settings.
-- STATEMENT
UPDATE posts SET content = '<!-- Hero Section -->
<section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden bg-slate-900" id="hero-slider">
    <div class="absolute inset-0 z-0">
        <div id="hero-slides" class="w-full h-full"></div>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px]"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/60 via-transparent to-slate-900"></div>
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:30px_30px]"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 container mx-auto px-4">
        <div class="max-w-6xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 py-1 px-4 rounded-full bg-white/10 border border-white/20 text-brand-300 text-xs font-semibold tracking-widest uppercase mb-10 backdrop-blur-xl animate-fade-in shadow-2xl">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                </span>
                Profesyonel Mimari & Mekan Fotoğrafçılığı
            </div>
            
            <h1 class="font-heading font-black text-5xl md:text-7xl lg:text-8xl text-white mb-12 leading-[1.1] tracking-tight drop-shadow-2xl">
                <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-4 mb-4 overflow-visible">
                    <div id="prefix-morph" class="h-20 md:h-24 lg:h-32 min-w-[300px] md:min-w-[450px] lg:min-w-[550px] overflow-visible"></div>
                    <div id="suffix-morph" class="h-20 md:h-24 lg:h-32 min-w-[200px] md:min-w-[350px] lg:min-w-[400px] text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-cyan-300 font-black italic overflow-visible"></div>
                </div>
                <div class="text-white mt-4">Dönüştürüyoruz</div>
            </h1>

            <p class="text-lg md:text-xl lg:text-2xl text-slate-200/90 max-w-3xl mx-auto mb-14 leading-relaxed font-light drop-shadow-md">
                Mekanlarınızın ruhunu ve estetiğini profesyonel karelerle ölümsüzleştiriyoruz.
                <span class="block mt-2 font-medium text-brand-300">Hizmet verdiğimiz bölgelerde fark yaratan çözümler.</span>
            </p>

            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <button onclick="openQuoteWizard()" class="group relative w-full sm:w-auto px-10 py-5 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl font-bold text-lg shadow-[0_20px_50px_rgba(14,165,233,0.3)] transition-all hover:scale-105 active:scale-95 overflow-hidden">
                    <span class="relative z-10">Hemen Fiyat Hesapla</span>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                </button>
                <a href="/portfolio" class="w-full sm:w-auto px-10 py-5 bg-white/5 hover:bg-white/10 text-white border border-white/20 rounded-2xl font-bold text-lg backdrop-blur-xl transition-all hover:scale-105 active:scale-95 flex items-center justify-center gap-3">
                    Portfolyoyu İncele
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-20 animate-bounce hidden md:block">
        <div class="w-6 h-10 rounded-full border-2 border-white/30 flex justify-center p-1">
            <div class="w-1 h-2 bg-white/60 rounded-full"></div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    if (window.initGooeyText) {
        window.initGooeyText("#prefix-morph", ["Mekanınızı", "Otelinizi", "Restoranınızı", "Villanızı", "Ofisinizi"], { 
            morphTime: 1.8, 
            cooldownTime: 1.2, 
            textClassName: "text-white" 
        });
        window.initGooeyText("#suffix-morph", ["Sanata", "Markaya", "Satışa", "Hikayeye", "Prestije"], { 
            morphTime: 1.8, 
            cooldownTime: 1.2, 
            textClassName: "text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-cyan-300 font-black italic" 
        });
    }
});
</script>

<!-- Stats Section -->
<section class="py-20 bg-white border-b border-slate-100">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="text-center group">
                <div class="text-4xl md:text-5xl font-bold text-brand-600 mb-2 group-hover:scale-110 transition-transform">500+</div>
                <div class="text-slate-600 font-medium text-sm md:text-base">Mutlu Müşteri</div>
            </div>
            <div class="text-center group">
                <div class="text-4xl md:text-5xl font-bold text-brand-600 mb-2 group-hover:scale-110 transition-transform">1000+</div>
                <div class="text-slate-600 font-medium text-sm md:text-base">Proje Teslimi</div>
            </div>
            <div class="text-center group">
                <div class="text-4xl md:text-5xl font-bold text-brand-600 mb-2 group-hover:scale-110 transition-transform">8+</div>
                <div class="text-slate-600 font-medium text-sm md:text-base">Yıllık Deneyim</div>
            </div>
             <div class="text-center group">
                <div class="text-4xl md:text-5xl font-bold text-brand-600 mb-2 group-hover:scale-110 transition-transform">81</div>
                <div class="text-slate-600 font-medium text-sm md:text-base">Şehirde Hizmet</div>
            </div>
        </div>
    </div>
</section>

<!-- Working Steps (Process) Section -->
<section class="py-24 bg-white overflow-hidden" id="nasil-calisiriz">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <span class="text-brand-600 font-bold tracking-widest uppercase text-sm mb-4 block">Süreç</span>
            <h2 class="font-heading font-bold text-3xl md:text-5xl text-slate-900 mb-6">Nasıl Çalışıyoruz?</h2>
            <p class="text-slate-600 text-lg lg:text-xl font-light">Mükemmel sonucu elde etmek için her aşamada titizlikle çalışıyoruz.</p>
        </div>

        <div class="grid md:grid-cols-4 gap-12 relative">
            <!-- Connector Line (Desktop) -->
            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-100 -translate-y-1/2 z-0 hidden md:block"></div>
            
            <!-- Step 1 -->
            <div class="relative z-10 text-center group">
                <div class="w-20 h-20 bg-brand-50 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:bg-brand-500 group-hover:rotate-12 transition-all duration-500 shadow-xl shadow-brand-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-600 group-hover:text-white"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-4">Keşif & Planlama</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Mekanınızın ihtiyaçlarını analiz ediyor, çekim konseptini birlikte planlıyoruz.</p>
            </div>

            <!-- Step 2 -->
            <div class="relative z-10 text-center group">
                <div class="w-20 h-20 bg-brand-50 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:bg-brand-500 group-hover:-rotate-12 transition-all duration-500 shadow-xl shadow-brand-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-600 group-hover:text-white"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-4">Profesyonel Çekim</h3>
                <p class="text-slate-500 text-sm leading-relaxed">En son teknoloji ekipmanlar ve sanatsal bakış açımızla çekimi gerçekleştiriyoruz.</p>
            </div>

            <!-- Step 3 -->
            <div class="relative z-10 text-center group">
                <div class="w-20 h-20 bg-brand-50 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:bg-brand-500 group-hover:rotate-12 transition-all duration-500 shadow-xl shadow-brand-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-600 group-hover:text-white"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-4">Post-Prodüksiyon</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Gerekli renk düzenlemeleri ve rötüş işlemleriyle fotoğrafları kusursuzlaştırıyoruz.</p>
            </div>

            <!-- Step 4 -->
            <div class="relative z-10 text-center group">
                <div class="w-20 h-20 bg-brand-50 rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:bg-brand-500 group-hover:-rotate-12 transition-all duration-500 shadow-xl shadow-brand-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-600 group-hover:text-white"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-4">Hızlı Teslimat</h3>
                <p class="text-slate-500 text-sm leading-relaxed">İşlenen tüm görselleri dijital platformlar üzerinden hızlı ve güvenli şekilde iletiyoruz.</p>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section class="py-24 bg-slate-50" id="hizmetler">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <h2 class="font-heading font-bold text-3xl md:text-5xl text-slate-900 mb-6">Neler Yapıyoruz?</h2>
            <p class="text-slate-600 text-lg lg:text-xl font-light">Her mekanın kendine has bir dili vardır. Biz o dili görselleştiriyoruz.</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-10">
            <!-- Service 1 -->
            <div class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-slate-100">
                <div class="relative h-72 overflow-hidden">
                    <img src="https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg" alt="Mimari" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent"></div>
                    <div class="absolute bottom-6 left-6">
                         <h3 class="text-2xl font-bold text-white mb-2">Mimari Fotoğrafçılık</h3>
                    </div>
                </div>
                <div class="p-8">
                    <p class="text-slate-600 mb-8 font-light">Oteller, villalar ve ticari yapılar için profesyonel, geniş açılı ve etkileyici mimari çekimler.</p>
                    <a href="/hizmetlerimiz/mimari-fotografcilik" class="inline-flex items-center gap-2 text-brand-600 font-bold hover:gap-4 transition-all uppercase text-xs tracking-widest">Detaylı İncele <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
                </div>
            </div>
             <!-- Service 2 -->
            <div class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-slate-100">
                <div class="relative h-72 overflow-hidden">
                    <img src="https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg" alt="İç Mekan" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent"></div>
                    <div class="absolute bottom-6 left-6">
                         <h3 class="text-2xl font-bold text-white mb-2">İç Mekan & Dekorasyon</h3>
                    </div>
                </div>
                <div class="p-8">
                    <p class="text-slate-600 mb-8 font-light">Mekanınızın detaylarını, dokusunu ve atmosferini yansıtan, dekorasyon odaklı estetik kareler.</p>
                    <a href="/hizmetlerimiz/ic-mekan-fotografciligi" class="inline-flex items-center gap-2 text-brand-600 font-bold hover:gap-4 transition-all uppercase text-xs tracking-widest">Detaylı İncele <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
                </div>
            </div>
             <!-- Service 3 -->
             <div class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 border border-slate-100">
                <div class="relative h-72 overflow-hidden">
                    <img src="https://images.pexels.com/photos/7045926/pexels-photo-7045926.jpeg" alt="Havadan" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent"></div>
                    <div class="absolute bottom-6 left-6">
                         <h3 class="text-2xl font-bold text-white mb-2">Havadan & Drone</h3>
                    </div>
                </div>
                <div class="p-8">
                    <p class="text-slate-600 mb-8 font-light">Mekanınızın konumunu, çevresini ve görkemini vurgulayan profesyonel drone çekimleri.</p>
                    <a href="/hizmetlerimiz" class="inline-flex items-center gap-2 text-brand-600 font-bold hover:gap-4 transition-all uppercase text-xs tracking-widest">Tüm Hizmetler <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-24 relative bg-slate-900" id="iletisim">
    <div class="absolute inset-0 z-0 opacity-20">
         <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg" alt="Contact BG" class="w-full h-full object-cover">
         <div class="absolute inset-0 bg-slate-900/80"></div>
    </div>
    <div class="container mx-auto px-4 relative z-10 text-center">
        <h2 class="font-heading font-bold text-4xl md:text-5xl text-white mb-8">Hayalinizdeki Kareler İçin</h2>
        <p class="text-xl text-brand-100 mb-12 font-light max-w-2xl mx-auto">Profesyonel çekimler ve teklif almak için bizimle hemen iletişime geçebilirsiniz.</p>
        <div class="flex flex-col sm:flex-row gap-6 justify-center">
             <a href="mailto:info@mekanfotografcisi.tr" class="inline-flex items-center justify-center gap-3 px-10 py-5 bg-white text-brand-900 rounded-2xl font-bold text-lg hover:bg-brand-50 transition-colors shadow-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                info@mekanfotografcisi.tr
            </a>
            <button onclick="openQuoteWizard()" class="px-10 py-5 bg-brand-600 text-white rounded-2xl font-bold text-lg hover:bg-brand-500 transition-colors shadow-2xl">Hemen Teklif Al</button>
        </div>
    </div>
</section>'
WHERE slug = 'homepage';
