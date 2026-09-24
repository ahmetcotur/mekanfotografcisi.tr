// Quote Wizard Logic (markup: templates/partials/quote-wizard.php)

const WIZARD_TOTAL_STEPS = 4;
let wizardLastFocus = null;

if (typeof window.mfQuoteWizardStep === 'undefined') {
    window.mfQuoteWizardStep = 1;
}

// Map a service/page slug (e.g. "hizmetlerimiz/otel-fotografciligi") onto one
// of the wizard's four service types.
function mapServiceType(serviceType) {
    const s = String(serviceType || '').toLowerCase();
    if (['mimari', 'otel', 'yemek', 'diger'].includes(s)) return s;
    if (/otel|pansiyon|resort|termal|turizm/.test(s)) return 'otel';
    if (/yemek|restoran|gida|kafe|cafe/.test(s)) return 'yemek';
    if (/mimari|ic-mekan|mekan|villa|emlak|ofis|konut|is-merkezi|ticari/.test(s)) return 'mimari';
    return 'diger';
}

function openQuoteWizard(serviceType = null, location = null) {
    const modal = document.getElementById('quote-wizard-modal');
    const backdrop = document.getElementById('wizard-backdrop');
    const panel = document.getElementById('wizard-panel');

    wizardLastFocus = document.activeElement;
    resetWizard();

    if (serviceType) {
        const input = document.querySelector(`input[name="service_type"][value="${mapServiceType(serviceType)}"]`);
        if (input) input.checked = true;
    }
    if (location) {
        document.getElementById('wizard_location').value = location;
    }

    modal.hidden = false;
    document.documentElement.classList.add('overflow-hidden');
    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'translate-y-4');
    });

    const firstField = serviceType
        ? document.getElementById('wizard_location')
        : document.querySelector('input[name="service_type"]');
    if (firstField && !(serviceType && location)) setTimeout(() => firstField.focus(), 50);
}

function closeQuoteWizard() {
    const modal = document.getElementById('quote-wizard-modal');
    if (modal.hidden) return;
    document.getElementById('wizard-backdrop').classList.add('opacity-0');
    document.getElementById('wizard-panel').classList.add('opacity-0', 'translate-y-4');
    document.documentElement.classList.remove('overflow-hidden');
    setTimeout(() => {
        modal.hidden = true;
        if (wizardLastFocus && wizardLastFocus.focus) wizardLastFocus.focus();
    }, 200);
}

function resetWizard() {
    const form = document.getElementById('quote-form');
    form.reset();
    form.hidden = false;
    document.getElementById('wizard-progress').hidden = false;
    document.getElementById('wizard-success').hidden = true;
    const btn = document.getElementById('btn-submit');
    btn.disabled = false;
    btn.textContent = 'Talebi gönder';
    window.mfQuoteWizardStep = 1;
    showWizardError('');
    updateStepUI();
}

function showWizardError(message) {
    const el = document.getElementById('wizard-error');
    el.textContent = message;
    el.hidden = !message;
}

function updateStepUI() {
    const step = window.mfQuoteWizardStep;

    document.querySelectorAll('#quote-form .step-content').forEach(el => {
        el.hidden = el.id !== `step-${step}`;
    });

    document.getElementById('btn-prev').hidden = step === 1;
    document.getElementById('btn-next').hidden = step === WIZARD_TOTAL_STEPS;
    document.getElementById('btn-submit').hidden = step !== WIZARD_TOTAL_STEPS;

    const indicator = document.querySelector(`.step-indicator[data-step="${step}"]`);
    document.getElementById('wizard-step-number').textContent = step;
    document.getElementById('wizard-step-name').textContent = indicator ? indicator.dataset.name : '';
    document.getElementById('wizard-progress-bar').style.width = `${(step / WIZARD_TOTAL_STEPS) * 100}%`;
}

function validateStep(step) {
    const stepEl = document.getElementById(`step-${step}`);
    const fields = stepEl.querySelectorAll('input[required], select[required], textarea[required]');
    let firstInvalid = null;

    fields.forEach(field => {
        let valid;
        if (field.type === 'radio') {
            valid = !!stepEl.querySelector(`input[name="${field.name}"]:checked`);
        } else {
            valid = field.value.trim() !== '' && field.checkValidity();
        }
        const target = field.type === 'radio' ? null : field;
        if (target) {
            target.setAttribute('aria-invalid', valid ? 'false' : 'true');
            target.classList.toggle('border-red-400', !valid);
        }
        if (!valid && !firstInvalid) firstInvalid = field;
    });

    if (firstInvalid) {
        let message = 'Lütfen işaretli alanları doldurun.';
        if (firstInvalid.name === 'service_type') message = 'Lütfen bir hizmet türü seçin.';
        else if (firstInvalid.name === 'location') message = 'Lütfen çekimin yapılacağı yeri yazın.';
        else if (firstInvalid.type === 'email' && firstInvalid.value) message = 'Lütfen geçerli bir e-posta adresi girin.';
        showWizardError(message);
        firstInvalid.focus();
        return false;
    }

    showWizardError('');
    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('quote-form');
    if (!form) return;

    document.getElementById('btn-next').addEventListener('click', () => {
        if (!validateStep(window.mfQuoteWizardStep)) return;
        if (window.mfQuoteWizardStep === 1) setupStep2();
        window.mfQuoteWizardStep++;
        updateStepUI();
    });

    document.getElementById('btn-prev').addEventListener('click', () => {
        window.mfQuoteWizardStep--;
        showWizardError('');
        updateStepUI();
    });

    // Enter moves forward instead of submitting half-way through.
    form.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && window.mfQuoteWizardStep < WIZARD_TOTAL_STEPS) {
            e.preventDefault();
            document.getElementById('btn-next').click();
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!validateStep(WIZARD_TOTAL_STEPS)) return;
        submitQuote();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeQuoteWizard();
    });
});

// Dynamic Fields based on Type
const serviceQuestions = {
    'mimari': [
        { label: 'Mekanı kısaca anlat', name: 'project_size_detail', type: 'textarea', placeholder: 'Örn: 4+1 villa, yaklaşık 250 m², tüm odalar ve dış çekim dahil', required: true }
    ],
    'otel': [
        { label: 'Toplam oda sayısı', name: 'total_rooms', type: 'number', placeholder: 'Örn: 50' },
        { label: 'Çekilecek oda tipleri', name: 'room_types', type: 'text', placeholder: 'Standart, deluxe, suit…' },
        { label: 'Drone / havadan çekim', name: 'drone_needed', type: 'select', options: ['İstiyorum', 'İstemiyorum', 'Kararsızım'] }
    ],
    'yemek': [
        { label: 'Çekilecek ürün / tabak sayısı', name: 'dish_count', type: 'number', placeholder: 'Örn: 15' },
        { label: 'Styling / sunum desteği', name: 'styling_needed', type: 'select', options: ['İhtiyacım var', 'Kendimiz hazırlayacağız'] }
    ],
    'diger': [
        { label: 'Proje nedir?', name: 'project_type', type: 'text', placeholder: 'Örn: Drone çekimi, etkinlik, tanıtım filmi', required: true }
    ]
};

const serviceTitles = {
    mimari: 'Mekan hakkında',
    otel: 'Tesis hakkında',
    yemek: 'Çekim hakkında',
    diger: 'Proje hakkında'
};

function setupStep2() {
    const serviceType = document.querySelector('input[name="service_type"]:checked').value;
    const container = document.getElementById('dynamic-fields');
    container.innerHTML = '';
    document.getElementById('step-2-title').textContent = serviceTitles[serviceType] || 'Proje detayları';

    const questions = serviceQuestions[serviceType] || serviceQuestions['diger'];

    questions.forEach(q => {
        const div = document.createElement('div');
        const id = `wizard_${q.name}`;
        const optional = q.required ? '' : ' <span class="font-normal text-ink-muted">(opsiyonel)</span>';
        const header = `<label for="${id}" class="label">${q.label}${optional}</label>`;
        const req = q.required ? 'required' : '';
        let input;

        if (q.type === 'select') {
            const opts = q.options.map(o => `<option value="${o}">${o}</option>`).join('');
            input = `<select id="${id}" name="${q.name}" ${req} class="input">${opts}</select>`;
        } else if (q.type === 'textarea') {
            input = `<textarea id="${id}" name="${q.name}" ${req} placeholder="${q.placeholder || ''}" rows="3" class="input"></textarea>`;
        } else {
            const extra = q.type === 'number' ? 'min="0" inputmode="numeric"' : '';
            input = `<input id="${id}" type="${q.type}" name="${q.name}" ${req} ${extra} placeholder="${q.placeholder || ''}" class="input">`;
        }

        div.innerHTML = header + input;
        container.appendChild(div);
    });
}

function showWizardSuccess(name, quoteNumber) {
    const success = document.getElementById('wizard-success');
    const safeName = typeof escapeHtml === 'function' ? escapeHtml(name) : '';
    success.innerHTML = `
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
        </div>
        <h3 class="h-card mt-5">Talebin alındı</h3>
        <p class="mx-auto mt-2 max-w-sm text-sm text-ink-muted">Teşekkürler${safeName ? ' ' + safeName : ''}. Talebini bölgendeki uygun fotoğrafçılarla paylaşıyoruz; en kısa sürede sana dönüş yapılacak.</p>
        ${quoteNumber ? `
        <div class="mx-auto mt-6 max-w-xs rounded-2xl bg-stone-50 p-4">
            <span class="block text-xs text-ink-muted">Talep numaran</span>
            <span class="mt-1 block text-xl font-semibold tracking-wide">${typeof escapeHtml === 'function' ? escapeHtml(quoteNumber) : ''}</span>
        </div>` : ''}
        <div class="mt-8 flex flex-col justify-center gap-2 sm:flex-row">
            <a href="/kayit/musteri" class="btn btn-outline">Taleplerini takip et</a>
            <button type="button" onclick="closeQuoteWizard()" class="btn btn-dark">Kapat</button>
        </div>`;
    document.getElementById('quote-form').hidden = true;
    document.getElementById('wizard-progress').hidden = true;
    success.hidden = false;
}

function submitQuote() {
    const btn = document.getElementById('btn-submit');
    btn.textContent = 'Gönderiliyor…';
    btn.disabled = true;
    showWizardError('');

    const formData = new FormData(document.getElementById('quote-form'));

    // Construct meaningful summary for the CRM
    let summary = `Hizmet: ${formData.get('service_type')}\n`;
    if (formData.get('project_desc')) summary += `Not: ${formData.get('project_desc')}\n`;

    // Form fields to exclude from dynamic summary loop (they are added manually or handled differently)
    const baseFields = ['name', 'email', 'phone', 'location', 'service_type', 'project_desc', 'preferred_date', 'preferred_time', 'urgency'];

    for (let [key, value] of formData.entries()) {
        if (!baseFields.includes(key) && value) {
            summary += `${key}: ${value}\n`;
        }
    }

    if (formData.get('preferred_date')) summary += `Tarih: ${formData.get('preferred_date')}\n`;
    if (formData.get('preferred_time')) summary += `Işık: ${formData.get('preferred_time')}\n`;
    if (formData.get('urgency')) summary += `Aciliyet: ${formData.get('urgency')}`;

    const payload = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        location: formData.get('location'),
        service: formData.get('service_type'),
        message: summary.trim()
    };

    // Flatten all fields into payload for detailed view
    for (let [key, value] of formData.entries()) {
        if (!['name', 'email', 'phone', 'location', 'service_type', 'project_desc'].includes(key)) {
            payload[key] = value;
        }
    }

    const readJson = r => r.json().catch(() => ({}));

    // Local save feeds the marketplace matching and issues the MF-xxxxx number
    // that "Talep sorgula" looks up; the CRM copy is for the sales pipeline.
    // Either one landing means the request isn't lost, so the visitor sees
    // success unless both fail.
    const localSave = fetch('/save-form.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(readJson);

    const crmSave = fetch(window.LEADS_API_URL || 'https://lead.ahmetcotur.com/api/leads/form', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            website_uuid: window.LEADS_WEBSITE_UUID || '1be2f821-28cd-4c86-aeb0-dabe0c05aa0a',
            business_name: payload.name,
            email: payload.email,
            phone: payload.phone,
            details: payload,
            page_url: window.location.href
        })
    }).then(readJson);

    Promise.allSettled([localSave, crmSave]).then(([local, crm]) => {
        const localData = local.status === 'fulfilled' ? local.value : {};
        const crmData = crm.status === 'fulfilled' ? crm.value : {};
        if (crm.status === 'rejected') console.error('CRM lead failed', crm.reason);

        if (localData.success || crmData.success) {
            const number = localData.quote_number || crmData.quote_number || (crmData.id ? '#' + crmData.id : '');
            showWizardSuccess(payload.name, number);
            return;
        }

        const reason = localData.message || crmData.message;
        showWizardError(reason
            ? 'Talep gönderilemedi: ' + reason
            : 'Bağlantı hatası. İnternet bağlantını kontrol edip tekrar dene.');
        btn.textContent = 'Talebi gönder';
        btn.disabled = false;
    });
}
