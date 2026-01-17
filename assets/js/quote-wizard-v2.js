// Quote Wizard Logic

function openQuoteWizard(serviceType = null) {
    const modal = document.getElementById('quote-wizard-modal');
    const backdrop = document.getElementById('wizard-backdrop');
    const panel = document.getElementById('wizard-panel');

    modal.classList.remove('hidden');
    // Simple animation delay
    setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');
    }, 10);

    // reset wizard
    resetWizard();

    // Pre-select service if provided
    if (serviceType) {
        // Map simplified slugs
        let mappedType = 'diger';
        if (serviceType.includes('otel') || serviceType.includes('pansiyon') || serviceType.includes('resort')) mappedType = 'otel';
        else if (serviceType.includes('yemek') || serviceType.includes('restoran') || serviceType.includes('gida')) mappedType = 'yemek';
        else if (serviceType.includes('mimari') || serviceType.includes('ic-mekan') || serviceType.includes('villa') || serviceType.includes('emlak') || serviceType.includes('ofis') || serviceType.includes('konut')) mappedType = 'mimari';

        const input = document.querySelector(`input[name="service_type"][value="${mappedType}"]`);
        if (input) {
            input.checked = true;
            // Auto advance logic could go here
        }
    }
}

function closeQuoteWizard() {
    const modal = document.getElementById('quote-wizard-modal');
    const backdrop = document.getElementById('wizard-backdrop');
    const panel = document.getElementById('wizard-panel');

    backdrop.classList.add('opacity-0');
    panel.classList.add('opacity-0', 'translate-y-4', 'sm:scale-95');

    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Initialize mfQuoteWizardStep if not already declared
if (typeof window.mfQuoteWizardStep === 'undefined') {
    window.mfQuoteWizardStep = 1;
}



function resetWizard() {
    window.mfQuoteWizardStep = 1;
    updateStepUI();
    document.getElementById('quote-form').reset();
}

function updateStepUI() {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.step-content').forEach(el => el.classList.remove('block'));

    // Show current step
    const currentEl = document.getElementById(`step-${window.mfQuoteWizardStep}`);
    if (currentEl) {
        currentEl.classList.remove('hidden');
        currentEl.classList.add('block'); // Important for animation/display
    }

    // Update buttons
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const btnSubmit = document.getElementById('btn-submit');

    if (window.mfQuoteWizardStep === 1) {
        btnPrev.classList.add('hidden');
        btnNext.classList.remove('hidden');
        btnSubmit.classList.add('hidden');
    } else if (window.mfQuoteWizardStep === 2 || window.mfQuoteWizardStep === 3) {
        btnPrev.classList.remove('hidden');
        btnNext.classList.remove('hidden');
        btnSubmit.classList.add('hidden');
    } else if (window.mfQuoteWizardStep === 4) {
        btnPrev.classList.remove('hidden');
        btnNext.classList.add('hidden');
        btnSubmit.classList.remove('hidden');
    }

    // Update Progress Bar
    document.querySelectorAll('.step-indicator').forEach(el => {
        const step = parseInt(el.dataset.step);
        const circle = el.querySelector('div');
        const text = el.querySelector('span');

        if (step === window.mfQuoteWizardStep) {
            // Active
            el.classList.add('active');
            circle.classList.remove('bg-slate-200', 'text-slate-500', 'bg-green-500', 'text-white');
            circle.classList.add('bg-brand-600', 'text-white', 'ring-4', 'ring-brand-100');
            text.classList.remove('text-slate-500');
            text.classList.add('text-slate-900', 'font-bold');
        } else if (step < window.mfQuoteWizardStep) {
            // Completed
            el.classList.add('completed');
            circle.classList.remove('bg-slate-200', 'text-slate-500', 'ring-4', 'ring-brand-100', 'bg-brand-600');
            circle.classList.add('bg-green-500', 'text-white');
            circle.innerHTML = '✓';
            text.classList.remove('text-slate-900', 'font-bold');
            text.classList.add('text-slate-500');

            // Activate line
            const line = document.getElementById(`line-${step}`);
            if (line) {
                line.classList.remove('bg-slate-200');
                line.classList.add('bg-green-500');
            }
        } else {
            // Pending
            circle.classList.remove('bg-brand-600', 'text-white', 'bg-green-500', 'ring-4', 'ring-brand-100');
            circle.classList.add('bg-slate-200', 'text-slate-500');
            circle.innerHTML = step;
            // Deactivate line
            const line = document.getElementById(`line-${step - 1}`); // Line before this step
        }
    });

    // Simple line logic fix
    for (let i = 1; i < 4; i++) {
        const line = document.getElementById(`line-${i}`);
        if (line) {
            if (window.mfQuoteWizardStep > i) {
                line.classList.remove('bg-slate-200');
                line.classList.add('bg-green-500');
            } else {
                line.classList.remove('bg-green-500');
                line.classList.add('bg-slate-200');
            }
        }
    }
}

// Validation
function validateStep(step) {
    const stepEl = document.getElementById(`step-${step}`);
    const inputs = stepEl.querySelectorAll('input[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value || (input.type === 'radio' && !stepEl.querySelector(`input[name="${input.name}"]:checked`))) {
            isValid = false;
            input.parentElement.classList.add('ring-2', 'ring-red-500', 'border-red-500');
        } else {
            input.parentElement.classList.remove('ring-2', 'ring-red-500', 'border-red-500');
        }
    });

    if (!isValid) {
        // Use a more subtle feedback if possible, or Swal
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Eksik Bilgi',
                text: 'Lütfen zorunlu alanları doldurunuz.',
                icon: 'warning',
                confirmButtonColor: '#0ea5e9'
            });
        } else {
            alert("Lütfen zorunlu alanları seçiniz.");
        }
    }

    return isValid;
}

// Navigation Events
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btn-next').addEventListener('click', () => {
        if (!validateStep(window.mfQuoteWizardStep)) return;

        if (window.mfQuoteWizardStep === 1) {
            setupStep2();
        }

        window.mfQuoteWizardStep++;
        updateStepUI();
    });

    document.getElementById('btn-prev').addEventListener('click', () => {
        window.mfQuoteWizardStep--;
        updateStepUI();
    });

    document.getElementById('quote-form').addEventListener('submit', (e) => {
        e.preventDefault();
        submitQuote();
    });
});

// Dynamic Fields based on Type
const serviceQuestions = {
    'mimari': [
        { label: 'Çekim projenizin boyutu hakkında lütfen detaylıca bilgi veriniz.', name: 'project_size_detail', type: 'textarea', placeholder: 'Örn: 4+1 Villa, yaklaşık 250 m2, tüm odalar ve dış çekim dahil...', required: true }
    ],
    'otel': [
        { label: 'Toplam Oda Sayısı', name: 'total_rooms', type: 'number', placeholder: 'Örn: 50' },
        { label: 'Çekilecek Oda Tipleri', name: 'room_types', type: 'text', placeholder: 'Standart, Deluxe, Suit vb.' },
        { label: 'Drone / Havadan Çekim', name: 'drone_needed', type: 'select', options: ['İstiyorum', 'İstemiyorum', 'Kararsızım'] }
    ],
    'yemek': [
        { label: 'Çekilecek Ürün/Tabak Sayısı', name: 'dish_count', type: 'number', placeholder: 'Örn: 15' },
        { label: 'Styling / Sunum Desteği', name: 'styling_needed', type: 'select', options: ['İhtiyacım var', 'Kendimiz hazırlayacağız'] }
    ],
    'diger': [
        { label: 'Proje Detayları', name: 'project_type', type: 'text', placeholder: 'Lütfen proje amacını kısaca belirtiniz' }
    ]
};

function setupStep2() {
    const serviceType = document.querySelector('input[name="service_type"]:checked').value;
    const container = document.getElementById('dynamic-fields');
    container.innerHTML = ''; // Clear previous

    const questions = serviceQuestions[serviceType] || serviceQuestions['diger'];

    questions.forEach(q => {
        const div = document.createElement('div');

        let header = `<label class="block text-sm font-bold text-slate-700 mb-1">${q.label}${q.required ? ' *' : ''}</label>`;
        let input = '';

        if (q.type === 'select') {
            let opts = q.options.map(o => `<option value="${o}">${o}</option>`).join('');
            input = `<select name="${q.name}" ${q.required ? 'required' : ''} class="w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 py-3 px-4 transition-all">${opts}</select>`;
        } else if (q.type === 'textarea') {
            input = `<textarea name="${q.name}" ${q.required ? 'required' : ''} placeholder="${q.placeholder || ''}" rows="3" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 py-3 px-4 transition-all"></textarea>`;
        } else {
            input = `<input type="${q.type}" name="${q.name}" ${q.required ? 'required' : ''} placeholder="${q.placeholder || ''}" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-brand-500 py-3 px-4 transition-all">`;
        }

        div.innerHTML = header + input;
        container.appendChild(div);
    });
}

function submitQuote() {
    const btn = document.getElementById('btn-submit');
    const originalText = btn.innerText;
    btn.innerText = 'Gönderiliyor...';
    btn.disabled = true;

    // Configure Data
    const formData = new FormData(document.getElementById('quote-form'));

    // Base data
    const payload = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        location: formData.get('location'),
        service: formData.get('service_type'),
        message: formData.get('project_desc') || 'Sihirbaz üzerinden detaylı teklif isteği.'
    };

    // Extract dynamic fields for "Wizard Details"
    const wizardDetails = {};
    const baseFields = ['name', 'email', 'phone', 'location', 'service_type', 'project_desc'];
    for (let [key, value] of formData.entries()) {
        if (!baseFields.includes(key)) {
            wizardDetails[key] = value;
        }
    }
    payload.wizard_details = wizardDetails;

    // Send
    fetch('/save-form.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Show success styling in modal
                document.getElementById('wizard-panel').innerHTML = `
                <div class="p-10 text-center">
                    <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                             <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Talebiniz Alındı!</h3>
                    <p class="text-slate-600 mb-4">Teşekkürler ${payload.name}, proje detaylarını inceleyip en kısa sürede size dönüş yapacağız.</p>
                    
                    <div class="bg-slate-50 border border-brand-100 rounded-2xl p-4 mb-8">
                        <span class="text-xs text-slate-400 uppercase font-bold tracking-widest block mb-1">Teklif Numaranız</span>
                        <span class="text-2xl font-black text-brand-600 tracking-tighter">${data.quote_number || '#' + data.quote_id}</span>
                    </div>

                    <button onclick="closeQuoteWizard()" class="px-8 py-3 bg-slate-100 font-bold rounded-xl hover:bg-slate-200">Kapat</button>
                </div>
            `;
            } else {
                alert("Hata: " + data.message);
                btn.innerText = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert("Bağlantı hatası.");
            btn.innerText = originalText;
            btn.disabled = false;
        });
}
