<?php
/**
 * Quote wizard modal. Behaviour lives in assets/js/quote-wizard-v2.js
 * (openQuoteWizard(service?, location?) / closeQuoteWizard()).
 * Field names are part of the payload sent to save-form.php and the CRM.
 */
$wizardServices = [
    ['value' => 'mimari', 'icon' => 'building', 'title' => 'Mimari & İç Mekan', 'desc' => 'Villa, konut, ofis, ticari alan'],
    ['value' => 'otel', 'icon' => 'hotel', 'title' => 'Otel & Turizm', 'desc' => 'Otel, butik otel, pansiyon, tesis'],
    ['value' => 'yemek', 'icon' => 'utensils', 'title' => 'Yemek & Restoran', 'desc' => 'Menü, restoran, kafe'],
    ['value' => 'diger', 'icon' => 'sparkles', 'title' => 'Diğer', 'desc' => 'Drone, etkinlik, özel proje'],
];
$wizardSteps = ['Hizmet', 'Detaylar', 'Planlama', 'İletişim'];
?>
<div id="quote-wizard-modal" class="fixed inset-0 z-[200]" role="dialog" aria-modal="true" aria-labelledby="wizard-title" hidden>
    <div id="wizard-backdrop" class="absolute inset-0 bg-ink/50 opacity-0 transition-opacity duration-200" onclick="closeQuoteWizard()"></div>

    <div class="pointer-events-none absolute inset-0 flex items-end justify-center sm:items-center sm:p-6">
        <div id="wizard-panel"
            class="pointer-events-auto flex max-h-[92dvh] w-full translate-y-4 flex-col overflow-hidden rounded-t-3xl bg-white opacity-0 shadow-lift transition duration-200 sm:max-w-xl sm:rounded-3xl">

            <div class="flex items-start justify-between gap-4 border-b border-line px-6 pb-4 pt-5">
                <div>
                    <h2 id="wizard-title" class="h-card">Ücretsiz teklif al</h2>
                    <p class="mt-0.5 text-sm text-ink-muted">Talebini ilet, bölgendeki uygun fotoğrafçılar sana dönsün.</p>
                </div>
                <button type="button" onclick="closeQuoteWizard()" class="-mr-2 rounded-full p-2 text-ink-muted hover:bg-stone-100 hover:text-ink" aria-label="Kapat">
                    <?= icon('x') ?>
                </button>
            </div>

            <div id="wizard-progress" class="px-6 pt-4">
                <div class="flex items-center justify-between text-xs font-medium text-ink-muted">
                    <span>Adım <span id="wizard-step-number">1</span> / <?= count($wizardSteps) ?></span>
                    <span id="wizard-step-name"><?= $wizardSteps[0] ?></span>
                </div>
                <div class="mt-2 h-1 overflow-hidden rounded-full bg-stone-100">
                    <div id="wizard-progress-bar" class="h-full rounded-full bg-brand-600 transition-all duration-300" style="width: 25%"></div>
                </div>
                <ol class="sr-only">
                    <?php foreach ($wizardSteps as $i => $name): ?>
                        <li class="step-indicator" data-step="<?= $i + 1 ?>" data-name="<?= e($name) ?>"><?= e($name) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <form id="quote-form" class="flex min-h-0 flex-1 flex-col" novalidate>
                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">

                    <!-- Step 1: Service + location -->
                    <fieldset class="step-content" id="step-1">
                        <legend class="text-base font-semibold">Ne çektirmek istiyorsun?</legend>
                        <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                            <?php foreach ($wizardServices as $i => $service): ?>
                                <label class="choice items-start">
                                    <input type="radio" name="service_type" value="<?= e($service['value']) ?>" class="sr-only" <?= $i === 0 ? 'required' : '' ?>>
                                    <span class="mt-0.5 text-brand-700"><?= icon($service['icon']) ?></span>
                                    <span>
                                        <span class="block font-semibold"><?= e($service['title']) ?></span>
                                        <span class="block text-xs font-normal text-ink-muted"><?= e($service['desc']) ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-5">
                            <label for="wizard_location" class="label">Çekim nerede olacak?</label>
                            <input type="text" name="location" id="wizard_location" required class="input" placeholder="Örn: Kaş, Antalya" autocomplete="address-level2">
                            <p class="hint">Talebini bu bölgede çalışan fotoğrafçılarla eşleştiriyoruz.</p>
                        </div>
                    </fieldset>

                    <!-- Step 2: Details -->
                    <fieldset class="step-content" id="step-2" hidden>
                        <legend class="text-base font-semibold" id="step-2-title">Proje detayları</legend>
                        <div id="dynamic-fields" class="mt-3 space-y-4"></div>
                        <div class="mt-4">
                            <label for="project_desc" class="label">Ek notlar <span class="font-normal text-ink-muted">(opsiyonel)</span></label>
                            <textarea id="project_desc" name="project_desc" rows="3" class="input" placeholder="Örn: Gece çekimi de istiyoruz, havuz alanı önemli…"></textarea>
                        </div>
                    </fieldset>

                    <!-- Step 3: Planning -->
                    <fieldset class="step-content space-y-5" id="step-3" hidden>
                        <legend class="text-base font-semibold">Ne zaman?</legend>
                        <div>
                            <label for="preferred_date" class="label">Tercih ettiğin tarih <span class="font-normal text-ink-muted">(opsiyonel)</span></label>
                            <input type="date" id="preferred_date" name="preferred_date" class="input">
                        </div>
                        <div>
                            <span class="label">Ne kadar acil?</span>
                            <div class="grid gap-2 sm:grid-cols-3">
                                <label class="choice justify-center"><input type="radio" name="urgency" value="hemen" class="sr-only"> 1-3 gün içinde</label>
                                <label class="choice justify-center"><input type="radio" name="urgency" value="normal" class="sr-only" checked> 1-2 hafta</label>
                                <label class="choice justify-center"><input type="radio" name="urgency" value="ileride" class="sr-only"> Henüz planlıyorum</label>
                            </div>
                        </div>
                        <div>
                            <span class="label">Işık tercihi <span class="font-normal text-ink-muted">(opsiyonel)</span></span>
                            <div class="grid gap-2 sm:grid-cols-3">
                                <label class="choice justify-center"><input type="radio" name="preferred_time" value="sabah" class="sr-only"> Gündüz</label>
                                <label class="choice justify-center"><input type="radio" name="preferred_time" value="aksam" class="sr-only"> Gün batımı / gece</label>
                                <label class="choice justify-center"><input type="radio" name="preferred_time" value="farketmez" class="sr-only"> Fark etmez</label>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Step 4: Contact -->
                    <fieldset class="step-content space-y-4" id="step-4" hidden>
                        <legend class="text-base font-semibold">Sana nasıl ulaşalım?</legend>
                        <div>
                            <label for="wizard_name" class="label">Ad soyad / firma</label>
                            <input type="text" name="name" id="wizard_name" required class="input" autocomplete="name">
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="wizard_email" class="label">E-posta</label>
                                <input type="email" name="email" id="wizard_email" required class="input" placeholder="ornek@mail.com" autocomplete="email">
                            </div>
                            <div>
                                <label for="wizard_phone" class="label">Telefon</label>
                                <input type="tel" name="phone" id="wizard_phone" required class="input" placeholder="05XX XXX XX XX" autocomplete="tel">
                            </div>
                        </div>
                        <p class="flex items-start gap-2 text-xs text-ink-muted">
                            <?= icon('shield', 'mt-0.5 h-4 w-4 shrink-0') ?>
                            Bilgilerin yalnızca talebinle eşleşen fotoğrafçılarla paylaşılır. Teklif almak ücretsizdir ve seni bağlamaz.
                        </p>
                    </fieldset>

                    <p id="wizard-error" class="notice notice-error mt-4" role="alert" hidden></p>
                </div>

                <div class="flex items-center gap-3 border-t border-line px-6 py-4" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                    <button type="button" id="btn-prev" class="btn btn-ghost" hidden><?= icon('arrow-left', 'h-4 w-4') ?> Geri</button>
                    <button type="button" id="btn-next" class="btn btn-primary ml-auto px-6">Devam <?= icon('arrow-right', 'h-4 w-4') ?></button>
                    <button type="submit" id="btn-submit" class="btn btn-primary ml-auto px-6" hidden>Talebi gönder</button>
                </div>
            </form>

            <div id="wizard-success" class="px-6 py-10 text-center" hidden></div>
        </div>
    </div>
</div>
