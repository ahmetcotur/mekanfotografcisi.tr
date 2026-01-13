<div id="quote-wizard-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity opacity-0" id="wizard-backdrop"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                id="wizard-panel">

                <!-- Header -->
                <div
                    class="bg-gradient-to-r from-brand-600 to-brand-800 px-6 py-6 sm:px-10 flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold leading-6 text-white" id="modal-title">Teklif Sihirbazı</h3>
                        <p class="mt-1 text-brand-100 text-sm">Projeniz için en doğru fiyatı 3 adımda alın.</p>
                    </div>
                    <button type="button" class="text-white/70 hover:text-white transition-colors"
                        onclick="closeQuoteWizard()">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Steps Progress -->
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center justify-between max-w-md mx-auto">
                        <div class="flex flex-col items-center step-indicator active" data-step="1">
                            <div
                                class="w-8 h-8 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-sm mb-1 ring-4 ring-brand-100">
                                1</div>
                            <span class="text-[10px] uppercase tracking-tighter font-bold text-slate-900">Hizmet</span>
                        </div>
                        <div class="h-0.5 w-8 bg-slate-200" id="line-1"></div>
                        <div class="flex flex-col items-center step-indicator" data-step="2">
                            <div
                                class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm mb-1">
                                2</div>
                            <span
                                class="text-[10px] uppercase tracking-tighter font-bold text-slate-500">Detaylar</span>
                        </div>
                        <div class="h-0.5 w-8 bg-slate-200" id="line-2"></div>
                        <div class="flex flex-col items-center step-indicator" data-step="3">
                            <div
                                class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm mb-1">
                                3</div>
                            <span
                                class="text-[10px] uppercase tracking-tighter font-bold text-slate-500">Planlama</span>
                        </div>
                        <div class="h-0.5 w-8 bg-slate-200" id="line-3"></div>
                        <div class="flex flex-col items-center step-indicator" data-step="4">
                            <div
                                class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm mb-1">
                                4</div>
                            <span
                                class="text-[10px] uppercase tracking-tighter font-bold text-slate-500">İletişim</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <form id="quote-form" class="px-6 py-8 sm:px-10">

                    <!-- Step 1: Service Type -->
                    <div class="step-content block" id="step-1">
                        <label class="block text-lg font-bold text-slate-900 mb-4">Hangi hizmet için teklif almak
                            istiyorsunuz?</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Options will be populated via JS or PHP -->
                            <label
                                class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm focus:outline-none hover:border-brand-500 hover:ring-1 hover:ring-brand-500 transition-all group">
                                <input type="radio" name="service_type" value="mimari" class="peer sr-only" required>
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span
                                            class="block text-base font-medium text-slate-900 peer-checked:text-brand-600">Mimari
                                            & İç Mekan</span>
                                        <span class="mt-1 flex items-center text-sm text-slate-500">Villa, Ofis,
                                            Mağaza</span>
                                    </span>
                                </span>
                                <svg class="h-5 w-5 text-brand-600 opacity-0 peer-checked:opacity-100"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div
                                    class="absolute inset-0 rounded-xl ring-2 ring-transparent peer-checked:ring-brand-600 pointer-events-none">
                                </div>
                            </label>

                            <label
                                class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm focus:outline-none hover:border-brand-500 hover:ring-1 hover:ring-brand-500 transition-all group">
                                <input type="radio" name="service_type" value="otel" class="peer sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span
                                            class="block text-base font-medium text-slate-900 peer-checked:text-brand-600">Otel
                                            & Turizm</span>
                                        <span class="mt-1 flex items-center text-sm text-slate-500">Otel, Pansiyon,
                                            Resort</span>
                                    </span>
                                </span>
                                <svg class="h-5 w-5 text-brand-600 opacity-0 peer-checked:opacity-100"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div
                                    class="absolute inset-0 rounded-xl ring-2 ring-transparent peer-checked:ring-brand-600 pointer-events-none">
                                </div>
                            </label>

                            <label
                                class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm focus:outline-none hover:border-brand-500 hover:ring-1 hover:ring-brand-500 transition-all group">
                                <input type="radio" name="service_type" value="yemek" class="peer sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span
                                            class="block text-base font-medium text-slate-900 peer-checked:text-brand-600">Yemek
                                            & Restoran</span>
                                        <span class="mt-1 flex items-center text-sm text-slate-500">Menü, Sosyal
                                            Medya</span>
                                    </span>
                                </span>
                                <svg class="h-5 w-5 text-brand-600 opacity-0 peer-checked:opacity-100"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div
                                    class="absolute inset-0 rounded-xl ring-2 ring-transparent peer-checked:ring-brand-600 pointer-events-none">
                                </div>
                            </label>

                            <label
                                class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm focus:outline-none hover:border-brand-500 hover:ring-1 hover:ring-brand-500 transition-all group">
                                <input type="radio" name="service_type" value="diger" class="peer sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span
                                            class="block text-base font-medium text-slate-900 peer-checked:text-brand-600">Diğer
                                            / Özel Proje</span>
                                        <span class="mt-1 flex items-center text-sm text-slate-500">Hava çekimi,
                                            Endüstriyel vb.</span>
                                    </span>
                                </span>
                                <svg class="h-5 w-5 text-brand-600 opacity-0 peer-checked:opacity-100"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div
                                    class="absolute inset-0 rounded-xl ring-2 ring-transparent peer-checked:ring-brand-600 pointer-events-none">
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Step 2: Dynamic Details -->
                    <div class="step-content hidden" id="step-2">
                        <h4 class="text-lg font-semibold text-slate-900 mb-4" id="step-2-title">Proje Detayları</h4>

                        <!-- Dynamic Fields Container -->
                        <div id="dynamic-fields" class="space-y-4">
                            <!-- Injected via JS -->
                        </div>

                        <div class="mt-6">
                            <label for="project_desc" class="block text-sm font-medium text-slate-700 mb-1">Ek Notlar /
                                Beklentiler</label>
                            <textarea id="project_desc" name="project_desc" rows="3"
                                class="w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        </div>
                    </div>

                    <!-- Step 3: Shoot Planning -->
                    <div class="step-content hidden" id="step-3">
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Çekim Planlaması</h4>
                        <p class="text-slate-500 text-sm mb-6">Çekimi ne zaman gerçekleştirmeyi düşünüyorsunuz?</p>

                        <div class="space-y-6">
                            <div>
                                <label for="preferred_date" class="block text-sm font-medium text-slate-700 mb-1">Tercih
                                    Edilen Tarih</label>
                                <input type="date" id="preferred_date" name="preferred_date"
                                    class="w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 py-3 px-4">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-3">Tercih Edilen Zaman
                                    Aralığı</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label
                                        class="relative flex cursor-pointer rounded-xl border border-slate-200 p-3 hover:bg-slate-50 transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                                        <input type="radio" name="preferred_time" value="sabah" class="sr-only">
                                        <span class="text-xs font-bold text-slate-600">Gündüz (Soft Işık)</span>
                                    </label>
                                    <label
                                        class="relative flex cursor-pointer rounded-xl border border-slate-200 p-3 hover:bg-slate-50 transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                                        <input type="radio" name="preferred_time" value="aksam" class="sr-only">
                                        <span class="text-xs font-bold text-slate-600">Gün Batımı / Akşam</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-3">Çekim Aciliyeti</label>
                                <div class="flex flex-wrap gap-2">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="urgency" value="hemen" class="peer sr-only">
                                        <span
                                            class="px-4 py-2 rounded-full border border-slate-200 text-xs font-bold text-slate-500 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-600 transition-all block">Hemen
                                            (1-3 Gün)</span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="urgency" value="normal" class="peer sr-only" checked>
                                        <span
                                            class="px-4 py-2 rounded-full border border-slate-200 text-xs font-bold text-slate-500 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-600 transition-all block">Normal
                                            (1-2 Hafta)</span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="urgency" value="ileride" class="peer sr-only">
                                        <span
                                            class="px-4 py-2 rounded-full border border-slate-200 text-xs font-bold text-slate-500 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-600 transition-all block">İleri
                                            Tarihli</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Contact Info -->
                    <div class="step-content hidden" id="step-4">
                        <div class="text-center mb-6">
                            <h4 class="text-lg font-bold text-slate-900">Son Adım! İletişim Bilgileriniz</h4>
                            <p class="text-slate-500 text-sm">Teklifi size iletebilmemiz için bilgilerinizi girin.</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="wizard_name" class="block text-sm font-medium text-slate-700">Ad
                                    Soyad</label>
                                <input type="text" name="name" id="wizard_name" required
                                    class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 focus:ring-brand-500 py-3 px-4">
                            </div>
                            <div>
                                <label for="wizard_email" class="block text-sm font-medium text-slate-700">E-posta
                                    Adresi</label>
                                <input type="email" name="email" id="wizard_email" required
                                    class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 focus:ring-brand-500 py-3 px-4">
                            </div>
                            <div>
                                <label for="wizard_phone" class="block text-sm font-medium text-slate-700">Telefon
                                    Numarası</label>
                                <input type="tel" name="phone" id="wizard_phone" required
                                    class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 focus:ring-brand-500 py-3 px-4">
                            </div>
                            <div>
                                <label for="wizard_location" class="block text-sm font-medium text-slate-700">Proje
                                    Konumu (İl/İlçe)</label>
                                <input type="text" name="location" id="wizard_location"
                                    class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 focus:ring-brand-500 py-3 px-4">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-10 flex justify-between pt-6 border-t border-slate-100">
                        <button type="button" id="btn-prev"
                            class="hidden px-6 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-medium transition-colors">
                            ← Geri
                        </button>
                        <button type="button" id="btn-next"
                            class="ml-auto px-8 py-2.5 bg-brand-600 text-white rounded-xl font-bold shadow-lg shadow-brand-500/30 hover:bg-brand-500 transition-all hover:scale-105">
                            Devam Et →
                        </button>
                        <button type="submit" id="btn-submit"
                            class="hidden ml-auto px-8 py-2.5 bg-green-600 text-white rounded-xl font-bold shadow-lg shadow-green-500/30 hover:bg-green-500 transition-all hover:scale-105">
                            Teklif İste ✨
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>