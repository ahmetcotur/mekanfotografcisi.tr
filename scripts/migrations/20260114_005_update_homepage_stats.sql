-- Migration: Update Homepage Stats with Dynamic Shortcodes
-- Description: Replace static stats with dynamic shortcodes and improve badge visibility

UPDATE posts SET content = REPLACE(
    content,
    '<div class="inline-flex items-center gap-2 py-1 px-4 rounded-full bg-white/10 border border-white/20 text-brand-300 text-xs font-semibold tracking-widest uppercase mb-12 backdrop-blur-xl animate-fade-in shadow-2xl">',
    '<div class="inline-flex items-center gap-2 py-2 px-5 rounded-full bg-brand-600/90 border-2 border-brand-400 text-white text-sm font-black tracking-widest uppercase mb-12 backdrop-blur-xl animate-fade-in shadow-2xl shadow-brand-500/50">'
)
WHERE slug = 'homepage';

-- STATEMENT

UPDATE posts SET content = REPLACE(
    content,
    '<div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">500+</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Mutlu Müşteri</div>',
    '<div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">[stat_services]</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Hizmet Alanı</div>'
)
WHERE slug = 'homepage';

-- STATEMENT

UPDATE posts SET content = REPLACE(
    content,
    '<div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">1000+</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Proje Teslimi</div>',
    '<div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">[stat_provinces]</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Aktif İl</div>'
)
WHERE slug = 'homepage';

-- STATEMENT

UPDATE posts SET content = REPLACE(
    content,
    '<div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">8+</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Yıllık Deneyim</div>',
    '<div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">[stat_districts]</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Aktif İlçe</div>'
)
WHERE slug = 'homepage';

-- STATEMENT

UPDATE posts SET content = REPLACE(
    content,
    '<div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">81</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Şehirde Hizmet</div>',
    '<div class="text-4xl md:text-6xl font-black text-brand-600 mb-3 group-hover:scale-110 transition-transform">[stat_projects]</div>
                <div class="text-slate-600 font-bold uppercase tracking-tighter text-sm">Proje Teslimi</div>'
)
WHERE slug = 'homepage';
