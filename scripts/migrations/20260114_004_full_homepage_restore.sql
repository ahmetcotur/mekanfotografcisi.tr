-- Migration: M20260114_004_Full_Homepage_Restore_V3
-- Description: Full restoration with 6-service grid, fixed Hero and Process sections.

UPDATE posts SET content = '<!-- Hero Section -->
<section class="relative min-h-[95vh] flex items-center justify-center overflow-hidden bg-slate-900" id="hero-slider">
    <div class="absolute inset-0 z-0">
        <div id="hero-slides" class="w-full h-full"></div>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px]"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/60 via-transparent to-slate-900"></div>
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:30px_30px]"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 container mx-auto px-4 overflow-visible">
        <div class="max-w-7xl mx-auto text-center overflow-visible">
            <div class="inline-flex items-center gap-2 py-1 px-4 rounded-full bg-white/10 border border-white/20 text-brand-300 text-xs font-semibold tracking-widest uppercase mb-12 backdrop-blur-xl animate-fade-in shadow-2xl">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-50"></span>
                </span>
                Profesyonel Mimari & Mekan Fotoğrafçılığı
            </div>
            
            <h1 class="font-heading font-black text-5xl md:text-7xl lg:text-8xl text-white mb-16 leading-[1.1] tracking-tight drop-shadow-2xl overflow-visible">
                <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-6 mb-8 overflow-visible">
                    <div id="prefix-morph" class="h-24 md:h-32 lg:h-40 min-w-[320px] md:min-w-[480px] lg:min-w-[650px] overflow-visible"></div>
                    <div id="suffix-morph" class="h-24 md:h-32 lg:h-40 min-w-[220px] md:min-w-[380px] lg:min-w-[500px] text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-cyan-300 font-black italic overflow-visible"></div>
                </div>
                <div class="text-white mt-8 drop-shadow-2xl">Dönüştürüyoruz</div>
            </h1>

            <p class="text-xl md:text-2xl lg:text-3xl text-slate-200/90 max-w-4xl mx-auto mb-16 leading-relaxed font-light drop-shadow-md">
                Mekanlarınızın ruhunu ve estetiğini profesyonel karelerle ölümsüzleştiriyoruz.
                <span class="block mt-3 font-medium text-brand-300">İşletmenize değer katan prestijli çekim çözümleri.</span>
            </p>

            <div class="flex flex-col sm:flex-row gap-8 justify-center items-center">
                <button onclick="openQuoteWizard()" class="group relative w-full sm:w-auto px-12 py-6 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl font-black text-xl shadow-[0_20px_50px_rgba(14,165,233,0.4)] transition-all hover:scale-110 active:scale-95 overflow-hidden">
                    <span class="relative z-10">Hemen Fiyat Hesapla</span>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                </button>
                <a href="/portfolio" class="w-full sm:w-auto px-12 py-6 bg-white/5 hover:bg-white/10 text-white border border-white/20 rounded-2xl font-black text-xl backdrop-blur-xl transition-all hover:scale-110 active:scale-95 flex items-center justify-center gap-4">
                    Portfolyoyu İncele
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    if (window.initGooeyText) {
        window.initGooeyText("#prefix-morph", ["Mekanınızı", "Otelinizi", "Restoranınızı", "Villanızı", "Ofisinizi"], { 
            morphTime: 2.0, 
            cooldownTime: 1.5, 
            textClassName: "text-white" 
        });
        window.initGooeyText("#suffix-morph", ["Sanata", "Markaya", "Satışa", "Hikayeye", "Prestije"], { 
            morphTime: 2.0, 
            cooldownTime: 1.5, 
            textClassName: "text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-cyan-300 font-black italic" 
        });
    }
});
</script>

<!-- Stats Section -->
<section class="py-24 bg-white border-b border-slate-100">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-12">
            <div class="text-center group border-r border-slate-50 last:border-0">
                <div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">[stat_services]</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Hizmet Alanı</div>
            </div>
            <div class="text-center group border-r border-slate-50 last:border-0">
                <div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">[stat_provinces]</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Aktif İl</div>
            </div>
            <div class="text-center group border-r border-slate-50 last:border-0">
                <div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">[stat_districts]</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Aktif İlçe</div>
            </div>
             <div class="text-center group last:border-0">
                <div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">[stat_projects]</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Proje Teslimi</div>
            </div>
        </div>
    </div>
</section>

<!-- Working Steps (Process) Section -->
<section class="py-32 bg-slate-50 overflow-hidden" id="nasil-calisiriz">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-4xl mx-auto mb-24">
            <span class="text-brand-600 font-black tracking-[0.2em] uppercase text-xs mb-6 block drop-shadow-sm">Süreç</span>
            <h2 class="font-heading font-black text-4xl md:text-6xl text-slate-900 mb-8">Nasıl Çalışıyoruz?</h2>
            <p class="text-slate-500 text-xl lg:text-2xl font-light leading-relaxed">Mükemmel sonucu elde etmek için her aşamada titizlikle çalışıyoruz.</p>
        </div>

        <div class="grid md:grid-cols-4 gap-16 relative">
            <!-- Connector Line (Desktop) -->
            <div class="absolute top-[40px] left-0 w-full h-1 bg-brand-100/50 z-0 hidden md:block rounded-full"></div>
            
            <!-- Step 1 -->
            <div class="relative z-10 text-center group">
                <div class="w-24 h-24 bg-white rounded-[2rem] flex items-center justify-center mx-auto mb-10 group-hover:bg-brand-600 group-hover:rotate-12 transition-all duration-500 shadow-2xl shadow-brand-200/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-brand-600 group-hover:text-white transition-colors"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-5">Keşif & Planlama</h3>
                <p class="text-slate-500 text-base leading-relaxed font-medium">Mekanınızın ihtiyaçlarını analiz ediyor, çekim konseptini birlikte planlıyoruz.</p>
            </div>

            <!-- Step 2 -->
            <div class="relative z-10 text-center group">
                <div class="w-24 h-24 bg-white rounded-[2rem] flex items-center justify-center mx-auto mb-10 group-hover:bg-brand-600 group-hover:-rotate-12 transition-all duration-500 shadow-2xl shadow-brand-200/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-brand-600 group-hover:text-white transition-colors"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-5">Profesyonel Çekim</h3>
                <p class="text-slate-500 text-base leading-relaxed font-medium">En son teknoloji ekipmanlar ve sanatsal bakış açımızla çekimi gerçekleştiriyoruz.</p>
            </div>

            <!-- Step 3 -->
            <div class="relative z-10 text-center group">
                <div class="w-24 h-24 bg-white rounded-[2rem] flex items-center justify-center mx-auto mb-10 group-hover:bg-brand-600 group-hover:rotate-12 transition-all duration-500 shadow-2xl shadow-brand-200/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-brand-600 group-hover:text-white transition-colors"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-5">Post-Prodüksiyon</h3>
                <p class="text-slate-500 text-base leading-relaxed font-medium">Gerekli renk düzenlemeleri ve rötüş işlemleriyle fotoğrafları kusursuzlaştırıyoruz.</p>
            </div>

            <!-- Step 4 -->
            <div class="relative z-10 text-center group">
                <div class="w-24 h-24 bg-white rounded-[2rem] flex items-center justify-center mx-auto mb-10 group-hover:bg-brand-600 group-hover:-rotate-12 transition-all duration-500 shadow-2xl shadow-brand-200/50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-brand-600 group-hover:text-white transition-colors"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-5">Hızlı Teslimat</h3>
                <p class="text-slate-500 text-base leading-relaxed font-medium">İşlenen tüm görselleri dijital platformlar üzerinden hızlı ve güvenli şekilde iletiyoruz.</p>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section class="py-32 bg-white" id="hizmetler">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-4xl mx-auto mb-24">
             <span class="text-brand-600 font-black tracking-[0.2em] uppercase text-xs mb-6 block">Kategoriler</span>
            <h2 class="font-heading font-black text-4xl md:text-6xl text-slate-900 mb-8">Neler Yapıyoruz?</h2>
            <p class="text-slate-500 text-xl lg:text-2xl font-light leading-relaxed">Her mekanın kendine has bir dili vardır. Biz o dili görselleştiriyoruz.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <!-- Service 1: Mimari -->
            <div class="group relative bg-slate-900 rounded-[2.5rem] h-[500px] overflow-hidden shadow-2xl">
                <img src="https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg" alt="Mimari" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full p-10 transform translate-y-6 group-hover:translate-y-0 transition-transform duration-500">
                    <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                    </div>
                    <h3 class="text-3xl font-black text-white mb-4 tracking-tight">Mimari Çekimler</h3>
                    <p class="text-slate-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">Yapıların görkemini ve geometrik estetiğini vurgulayan profesyonel dış cephe ve yapı çekimleri.</p>
                    <a href="/hizmetlerimiz/mimari-fotografcilik" class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn">
                        Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Service 2: İç Mekan -->
            <div class="group relative bg-slate-900 rounded-[2.5rem] h-[500px] overflow-hidden shadow-2xl">
                <img src="https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg" alt="İç Mekan" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full p-10 transform translate-y-6 group-hover:translate-y-0 transition-transform duration-500">
                    <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                    </div>
                    <h3 class="text-3xl font-black text-white mb-4 tracking-tight">İç Mekan & Tasarım</h3>
                    <p class="text-slate-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">Mekanların atmosferini, kullanılan dokuları ve ışık oyunlarını sanatsal bir dille yansıtan çekimler.</p>
                    <a href="/hizmetlerimiz/ic-mekan-fotografciligi" class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn">
                        Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Service 3: Otel -->
            <div class="group relative bg-slate-900 rounded-[2.5rem] h-[500px] overflow-hidden shadow-2xl">
                <img src="https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg" alt="Otel" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full p-10 transform translate-y-6 group-hover:translate-y-0 transition-transform duration-500">
                    <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                    </div>
                    <h3 class="text-3xl font-black text-white mb-4 tracking-tight">Otel & Tatil Köyü</h3>
                    <p class="text-slate-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">Konaklama tesisleri için prestij artıran, misafirlerinize o anı yaşatan profesyonel çekim çözümleri.</p>
                    <a href="/hizmetlerimiz/otel-fotografciligi" class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn">
                        Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Service 4: Emlak -->
            <div class="group relative bg-slate-900 rounded-[2.5rem] h-[500px] overflow-hidden shadow-2xl">
                <img src="https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg" alt="Emlak" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full p-10 transform translate-y-6 group-hover:translate-y-0 transition-transform duration-500">
                    <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                    </div>
                    <h3 class="text-3xl font-black text-white mb-4 tracking-tight">Emlak & Villa</h3>
                    <p class="text-slate-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">Gayrimenkul satışlarını hızlandıran, villa ve rezidanslar için ferah ve çekici görsel çalışmalar.</p>
                    <a href="/hizmetlerimiz/emlak-fotografciligi" class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn">
                        Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

             <!-- Service 5: Restoran -->
             <div class="group relative bg-slate-900 rounded-[2.5rem] h-[500px] overflow-hidden shadow-2xl">
                <img src="https://images.pexels.com/photos/67468/pexels-photo-67468.jpeg" alt="Restoran" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full p-10 transform translate-y-6 group-hover:translate-y-0 transition-transform duration-500">
                    <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                    </div>
                    <h3 class="text-3xl font-black text-white mb-4 tracking-tight">Restoran & Mekan</h3>
                    <p class="text-slate-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">Yeme-içme mekanları için lezzeti ve ambiyansı aynı karede buluşturan profesyonel çekimler.</p>
                    <a href="/hizmetlerimiz/ic-mekan-fotografciligi" class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn">
                        Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Service 6: Drone -->
            <div class="group relative bg-slate-900 rounded-[2.5rem] h-[500px] overflow-hidden shadow-2xl">
                <img src="https://images.pexels.com/photos/7045926/pexels-photo-7045926.jpeg" alt="Drone" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-70">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full p-10 transform translate-y-6 group-hover:translate-y-0 transition-transform duration-500">
                    <div class="w-12 h-1 bg-brand-500 mb-6 rounded-full overflow-hidden">
                        <div class="w-full h-full bg-white -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                    </div>
                    <h3 class="text-3xl font-black text-white mb-4 tracking-tight">Havadan Video & Drone</h3>
                    <p class="text-slate-300 text-sm font-medium leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-500 delay-100">Mekanınızın konumunu, çevresini ve görkemli duruşunu havadan profesyonelce vurgulayan çekimler.</p>
                    <a href="/hizmetlerimiz" class="mt-8 inline-flex items-center gap-2 text-white font-bold text-xs uppercase tracking-widest group/btn">
                        Detaylar <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="group-hover/btn:translate-x-2 transition-transform"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-32 relative bg-slate-900" id="iletisim">
    <div class="absolute inset-0 z-0 opacity-40">
         <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg" alt="Contact BG" class="w-full h-full object-cover">
         <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>
    </div>
    <div class="container mx-auto px-4 relative z-10 text-center">
        <h2 class="font-heading font-black text-5xl md:text-7xl text-white mb-10 tracking-tight">Hayalinizdeki Kareler İçin</h2>
        <p class="text-2xl text-brand-100 mb-16 font-light max-w-3xl mx-auto leading-relaxed">Profesyonel çekimler ve markanızın ihtiyacı olan prestijli görseller için bizimle hemen iletişime geçebilirsiniz.</p>
        <div class="flex flex-col sm:flex-row gap-8 justify-center items-center">
             <a href="mailto:info@mekanfotografcisi.tr" class="group inline-flex items-center justify-center gap-4 px-12 py-6 bg-white text-slate-900 rounded-[2rem] font-black text-xl hover:bg-brand-50 transition-all shadow-2xl hover:scale-105 active:scale-95">
                <div class="w-12 h-12 bg-slate-900 text-white rounded-full flex items-center justify-center group-hover:bg-brand-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </div>
                info@mekanfotografcisi.tr
            </a>
            <button onclick="openQuoteWizard()" class="px-12 py-6 bg-brand-600 text-white rounded-[2rem] font-black text-xl hover:bg-brand-500 transition-all shadow-2xl hover:scale-105 active:scale-95 shadow-brand-500/40">Hemen Teklif Al</button>
        </div>
    </div>
</section>'
WHERE slug = 'homepage';
-- STATEMENT
UPDATE post_meta SET meta_value = REPLACE(meta_value::text, '/services/', '/hizmetlerimiz/')::jsonb;
-- STATEMENT
UPDATE posts SET content = REPLACE(content, '/services/', '/hizmetlerimiz/');
-- STATEMENT
UPDATE posts SET content = REPLACE(content, '/services"', '/hizmetlerimiz"');
-- STATEMENT
UPDATE posts SET content = REPLACE(content, '/locations', '/hizmet-bolgeleri');
