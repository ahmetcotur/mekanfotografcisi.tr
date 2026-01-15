<?php
// Fetch custom color for the quote wizard
$customColor = get_setting('primary_color', '#fa7000'); // Use primary color (orange)
$customColorRgb = sscanf($customColor, "#%02x%02x%02x");
$customColorRgbString = implode(', ', $customColorRgb);
?>

<style>
    /* Custom color overrides for quote wizard */
    #quote-wizard-modal .wizard-brand-bg {
        background-color:
            <?= $customColor ?>
            !important;
    }

    #quote-wizard-modal .wizard-brand-text {
        color:
            <?= $customColor ?>
            !important;
    }

    #quote-wizard-modal .wizard-brand-border {
        border-color:
            <?= $customColor ?>
            !important;
    }

    #quote-wizard-modal .wizard-brand-ring {
        --tw-ring-color:
            <?= $customColor ?>
            !important;
    }

    #quote-wizard-modal .wizard-brand-shadow {
        --tw-shadow-color: rgb(<?= $customColorRgbString ?> / 0.3) !important;
        --tw-shadow: var(--tw-shadow-colored);
    }

    .glass-modal {
        background: rgba(255, 255, 255, 0.4) !important;
        backdrop-filter: blur(40px) saturate(200%) !important;
        -webkit-backdrop-filter: blur(40px) saturate(200%) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.3) !important;
        backdrop-filter: blur(10px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        background: rgba(255, 255, 255, 0.5) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
        transform: translateY(-2px);
    }

    .glass-input {
        background: rgba(255, 255, 255, 0.2) !important;
        backdrop-filter: blur(5px) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }
</style>

<div id="quote-wizard-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <!-- Backdrop with more blur -->
    <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-md transition-opacity opacity-0" id="wizard-backdrop"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <!-- Modal Panel -->
            <div class="relative transform overflow-hidden rounded-[2.5rem] glass-modal text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 shadow-black/5"
                id="wizard-panel">

                <!-- Header: Glassy and airy -->
                <div
                    class="px-8 py-10 sm:px-12 flex justify-between items-center relative overflow-hidden border-b border-white/20">
                    <div class="absolute inset-0 opacity-10 bg-brand-gradient"></div>
                    <div class="relative z-10">
                        <h3 class="text-3xl font-black leading-none text-slate-900 tracking-tight" id="modal-title">
                            Teklif
                            Sihirbazı</h3>
                        <p class="mt-2 text-slate-600 text-sm font-medium">Projeniz için en doğru fiyatı 3 adımda alın.
                        </p>
                    </div>
                    <button type="button"
                        class="relative z-10 w-12 h-12 flex items-center justify-center rounded-2xl bg-white/20 text-slate-900 hover:bg-white/40 transition-all hover:scale-110 active:scale-90 border border-white/20"
                        onclick="closeQuoteWizard()">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Steps Progress -->
                <div class="bg-white/10 px-6 py-6 border-b border-white/10">
                    <div class="flex items-center justify-between max-w-md mx-auto relative px-4">
                        <!-- Progress Line Background -->
                        <div class="absolute left-10 right-10 top-4 h-0.5 bg-black/5 -z-10"></div>
                        <div class="flex flex-col items-center step-indicator active" data-step="1">
                            <div class="w-8 h-8 rounded-full wizard-brand-bg text-white flex items-center justify-center font-bold text-sm mb-2 shadow-lg shadow-brand-500/20"
                                style="background-color: <?= $customColor ?>;">
                                1</div>
                            <span
                                class="text-[10px] uppercase tracking-wider font-extrabold text-slate-900">Hizmet</span>
                        </div>
                        <div class="h-0.5 w-8 bg-black/5 rounded-full" id="line-1"></div>
                        <div class="flex flex-col items-center step-indicator" data-step="2">
                            <div
                                class="w-8 h-8 rounded-full bg-white/40 backdrop-blur-sm text-slate-400 flex items-center justify-center font-bold text-sm mb-2 border border-white/40 shadow-sm">
                                2</div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Detaylar</span>
                        </div>
                        <div class="h-0.5 w-8 bg-black/5 rounded-full" id="line-2"></div>
                        <div class="flex flex-col items-center step-indicator" data-step="3">
                            <div
                                class="w-8 h-8 rounded-full bg-white/40 backdrop-blur-sm text-slate-400 flex items-center justify-center font-bold text-sm mb-2 border border-white/40 shadow-sm">
                                3</div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Planlama</span>
                        </div>
                        <div class="h-0.5 w-8 bg-black/5 rounded-full" id="line-3"></div>
                        <div class="flex flex-col items-center step-indicator" data-step="4">
                            <div
                                class="w-8 h-8 rounded-full bg-white/40 backdrop-blur-sm text-slate-400 flex items-center justify-center font-bold text-sm mb-2 border border-white/40 shadow-sm">
                                4</div>
                            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">İletişim</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="px-6 py-8 sm:px-10 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    <form id="quote-form">

                        <!-- Step 1: Service Type -->
                        <div class="step-content block" id="step-1">
                            <label class="block text-xl font-black text-slate-900 mb-6 text-center">İhtiyacınız olan
                                hizmeti seçin</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Mimari -->
                                <label
                                    class="relative flex cursor-pointer rounded-2xl glass-card p-5 focus:outline-none transition-all group overflow-hidden">
                                    <input type="radio" name="service_type" value="mimari" class="peer sr-only"
                                        required>
                                    <div
                                        class="absolute inset-0 bg-brand-500/5 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    </div>
                                    <span class="flex flex-1 relative z-10">
                                        <span class="flex flex-col">
                                            <span
                                                class="block text-lg font-bold text-slate-900 group-hover:text-brand-600 transition-colors">Mimari
                                                & İç Mekan</span>
                                            <span class="mt-1 text-sm text-slate-500 leading-snug">Villa, Ofis, Mağaza
                                                ve Konut Çekimleri</span>
                                        </span>
                                    </span>
                                    <div
                                        class="absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-slate-200 peer-checked:border-brand-500 peer-checked:bg-brand-500 flex items-center justify-center transition-all">
                                        <svg class="h-3 w-3 text-white scale-0 peer-checked:scale-100 transition-transform"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </label>

                                <!-- Otel -->
                                <label
                                    class="relative flex cursor-pointer rounded-2xl glass-card p-5 focus:outline-none transition-all group overflow-hidden">
                                    <input type="radio" name="service_type" value="otel" class="peer sr-only">
                                    <div
                                        class="absolute inset-0 bg-brand-500/5 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    </div>
                                    <span class="flex flex-1 relative z-10">
                                        <span class="flex flex-col">
                                            <span
                                                class="block text-lg font-bold text-slate-900 group-hover:text-brand-600 transition-colors">Otel
                                                & Turizm</span>
                                            <span class="mt-1 text-sm text-slate-500 leading-snug">Otel, Pansiyon ve
                                                Tatil Köyü Tanıtımları</span>
                                        </span>
                                    </span>
                                    <div
                                        class="absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-slate-200 peer-checked:border-brand-500 peer-checked:bg-brand-500 flex items-center justify-center transition-all">
                                        <svg class="h-3 w-3 text-white scale-0 peer-checked:scale-100 transition-transform"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </label>

                                <!-- Yemek -->
                                <label
                                    class="relative flex cursor-pointer rounded-2xl glass-card p-5 focus:outline-none transition-all group overflow-hidden">
                                    <input type="radio" name="service_type" value="yemek" class="peer sr-only">
                                    <div
                                        class="absolute inset-0 bg-brand-500/5 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    </div>
                                    <span class="flex flex-1 relative z-10">
                                        <span class="flex flex-col">
                                            <span
                                                class="block text-lg font-bold text-slate-900 group-hover:text-brand-600 transition-colors">Yemek
                                                & Restoran</span>
                                            <span class="mt-1 text-sm text-slate-500 leading-snug">Menü Çekimleri ve
                                                Sosyal Medya İçerikleri</span>
                                        </span>
                                    </span>
                                    <div
                                        class="absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-slate-200 peer-checked:border-brand-500 peer-checked:bg-brand-500 flex items-center justify-center transition-all">
                                        <svg class="h-3 w-3 text-white scale-0 peer-checked:scale-100 transition-transform"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </label>

                                <!-- Diğer -->
                                <label
                                    class="relative flex cursor-pointer rounded-2xl glass-card p-5 focus:outline-none transition-all group overflow-hidden">
                                    <input type="radio" name="service_type" value="diger" class="peer sr-only">
                                    <div
                                        class="absolute inset-0 bg-brand-500/5 opacity-0 peer-checked:opacity-100 transition-opacity">
                                    </div>
                                    <span class="flex flex-1 relative z-10">
                                        <span class="flex flex-col">
                                            <span
                                                class="block text-lg font-bold text-slate-900 group-hover:text-brand-600 transition-colors">Özel
                                                Proje</span>
                                            <span class="mt-1 text-sm text-slate-500 leading-snug">Drone Çekimi,
                                                Etkinlik veya Endüstriyel</span>
                                        </span>
                                    </span>
                                    <div
                                        class="absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-slate-200 peer-checked:border-brand-500 peer-checked:bg-brand-500 flex items-center justify-center transition-all">
                                        <svg class="h-3 w-3 text-white scale-0 peer-checked:scale-100 transition-transform"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Step 2: Details -->
                        <div class="step-content hidden animate-slide-up" id="step-2">
                            <h4 class="text-xl font-black text-slate-900 mb-6" id="step-2-title">Proje Detayları</h4>
                            <div id="dynamic-fields" class="space-y-6">
                                <!-- Injected via JS -->
                            </div>
                            <div class="mt-8">
                                <label for="project_desc"
                                    class="block text-sm font-extrabold text-slate-700 mb-2 uppercase tracking-wider">Ek
                                    Notlar & Beklentiler</label>
                                <textarea id="project_desc" name="project_desc" rows="3"
                                    class="w-full rounded-2xl glass-input shadow-inner focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 transition-all p-4 text-slate-900"
                                    placeholder="Örn: 'Web sitemiz için yüksek çözünürlüklü ve gece çekimi de içeren bir paket istiyoruz...'"></textarea>
                            </div>
                        </div>

                        <!-- Step 3: Planning -->
                        <div class="step-content hidden animate-slide-up" id="step-3">
                            <h4 class="text-xl font-black text-slate-900 mb-2">Çekim Planlaması</h4>
                            <p class="text-slate-500 text-sm mb-8">Takvimimizi sizin için hazırlayalım.</p>

                            <div class="space-y-8">
                                <div>
                                    <label for="preferred_date"
                                        class="block text-sm font-extrabold text-slate-700 mb-2 uppercase tracking-wider">Tercih
                                        Edilen Tarih</label>
                                    <input type="date" id="preferred_date" name="preferred_date"
                                        class="w-full rounded-2xl glass-input shadow-inner focus:ring-4 focus:ring-brand-500/20 py-4 px-6 text-slate-900">
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-extrabold text-slate-700 mb-3 uppercase tracking-wider">Işık
                                        Tercihi</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <label
                                            class="relative flex cursor-pointer rounded-2xl glass-card p-4 hover:bg-white/50 transition-all has-[:checked]:bg-brand-500 has-[:checked]:text-white">
                                            <input type="radio" name="preferred_time" value="sabah" class="sr-only">
                                            <span class="text-sm font-bold mx-auto">Gündüz / Soft Işık</span>
                                        </label>
                                        <label
                                            class="relative flex cursor-pointer rounded-2xl glass-card p-4 hover:bg-white/50 transition-all has-[:checked]:bg-brand-500 has-[:checked]:text-white">
                                            <input type="radio" name="preferred_time" value="aksam" class="sr-only">
                                            <span class="text-sm font-bold mx-auto">Gün Batımı / Gece</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-extrabold text-slate-700 mb-3 uppercase tracking-wider">Çekim
                                        Aciliyeti</label>
                                    <div class="flex flex-wrap gap-3">
                                        <label class="cursor-pointer group">
                                            <input type="radio" name="urgency" value="hemen" class="peer sr-only">
                                            <span
                                                class="px-6 py-2.5 rounded-full glass-card text-xs font-black text-slate-600 peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-400 transition-all block uppercase tracking-widest">Hemen
                                                (1-3 Gün)</span>
                                        </label>
                                        <label class="cursor-pointer group">
                                            <input type="radio" name="urgency" value="normal" class="peer sr-only"
                                                checked>
                                            <span
                                                class="px-6 py-2.5 rounded-full glass-card text-xs font-black text-slate-600 peer-checked:bg-brand-500 peer-checked:text-white peer-checked:border-brand-400 transition-all block uppercase tracking-widest">Normal
                                                (1-2 Hafta)</span>
                                        </label>
                                        <label class="cursor-pointer group">
                                            <input type="radio" name="urgency" value="ileride" class="peer sr-only">
                                            <span
                                                class="px-6 py-2.5 rounded-full glass-card text-xs font-black text-slate-600 peer-checked:bg-slate-700 peer-checked:text-white transition-all block uppercase tracking-widest">Planlama
                                                Aşamasında</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Contact -->
                        <div class="step-content hidden animate-slide-up" id="step-4">
                            <div class="text-center mb-8">
                                <h4 class="text-2xl font-black text-slate-900 mb-2">Harika! Son bir adım...</h4>
                                <p class="text-slate-500 font-medium">Size ulaşabilmemiz için iletişim detaylarınızı
                                    rica ediyoruz.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="sm:col-span-2">
                                    <label for="wizard_name"
                                        class="block text-xs font-black text-slate-500 uppercase tracking-[0.2em] mb-2 px-1">Ad
                                        Soyad / Firma</label>
                                    <input type="text" name="name" id="wizard_name" required placeholder="John Doe"
                                        class="w-full rounded-2xl glass-input shadow-inner focus:ring-4 focus:ring-brand-500/20 py-4 px-6 text-slate-900 font-bold">
                                </div>
                                <div>
                                    <label for="wizard_email"
                                        class="block text-xs font-black text-slate-500 uppercase tracking-[0.2em] mb-2 px-1">E-Posta</label>
                                    <input type="email" name="email" id="wizard_email" required
                                        placeholder="ornek@mail.com"
                                        class="w-full rounded-2xl glass-input shadow-inner focus:ring-4 focus:ring-brand-500/20 py-4 px-6 text-slate-900 font-bold">
                                </div>
                                <div>
                                    <label for="wizard_phone"
                                        class="block text-xs font-black text-slate-500 uppercase tracking-[0.2em] mb-2 px-1">Telefon</label>
                                    <input type="tel" name="phone" id="wizard_phone" required
                                        placeholder="05XX XXX XX XX"
                                        class="w-full rounded-2xl glass-input shadow-inner focus:ring-4 focus:ring-brand-500/20 py-4 px-6 text-slate-900 font-bold">
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="wizard_location"
                                        class="block text-xs font-black text-slate-500 uppercase tracking-[0.2em] mb-2 px-1">Proje
                                        Yeri</label>
                                    <input type="text" name="location" id="wizard_location"
                                        placeholder="Örn: Kaş, Antalya"
                                        class="w-full rounded-2xl glass-input shadow-inner focus:ring-4 focus:ring-brand-500/20 py-4 px-6 text-slate-900 font-bold">
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-12 flex justify-between items-center pt-8 border-t border-white/10">
                            <button type="button" id="btn-prev"
                                class="hidden px-8 py-4 rounded-2xl text-slate-600 hover:bg-white/50 font-black uppercase text-xs tracking-widest transition-all">
                                ← Geri Dön
                            </button>
                            <button type="button" id="btn-next"
                                class="ml-auto flex items-center gap-3 px-10 py-5 bg-brand-600 text-white rounded-2xl font-black uppercase text-sm tracking-[0.15em] shadow-xl shadow-brand-500/25 hover:bg-brand-500 hover:scale-105 active:scale-95 transition-all">
                                Devam Et
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                            <button type="submit" id="btn-submit"
                                class="hidden ml-auto flex items-center gap-3 px-10 py-5 bg-green-600 text-white rounded-2xl font-black uppercase text-sm tracking-[0.15em] shadow-xl shadow-green-500/25 hover:bg-green-500 hover:scale-105 active:scale-95 transition-all">
                                Teklifi Gönder ✨
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>