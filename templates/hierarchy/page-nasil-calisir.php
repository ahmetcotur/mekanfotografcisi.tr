<?php
/**
 * Template: Nasıl Çalışır (How It Works)
 * Explains the photography service workflow and answers common questions
 */
include __DIR__ . '/../page-header.php';

$pageTitle = 'Nasıl Çalışır?';
$pageDescription = 'Profesyonel mekan fotoğrafçılığı hizmetimizin adım adım iş akışını keşfedin.';

// Workflow steps
$workflowSteps = [
    [
        'number' => '01',
        'title' => 'Talep Gönderimi',
        'description' => 'İşiniz için online form veya iletişim kanallarımız üzerinden teklif talebi gönderin. Size en kısa sürede dönüş yapıyoruz.',
        'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'
    ],
    [
        'number' => '02',
        'title' => 'Keşif & Planlama',
        'description' => 'Projenizin boyutunu ve özel gereksinimlerini anlamak için detaylı bir toplantı yapıyoruz. Mekanınızı ve beklentilerinizi dinliyoruz.',
        'icon' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>'
    ],
    [
        'number' => '03',
        'title' => 'Tarih & Hazırlık',
        'description' => 'Çekim tarihini birlikte belirliyor ve mekanın hazırlıkları için size öneriler sunuyoruz. Işık koşulları ve detaylar planlanır.',
        'icon' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>'
    ],
    [
        'number' => '04',
        'title' => 'Çekim Programı',
        'description' => 'Detaylı bir çekim programı oluşturuyoruz. Hangi açıların, alanların ve detayların çekileceğini planlıyoruz.',
        'icon' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="9" x2="15" y1="15" y2="15"/>'
    ],
    [
        'number' => '05',
        'title' => 'Çekim Günü',
        'description' => 'Profesyonel ekipmanlarımız ve deneyimimizle mekanınızı en iyi şekilde fotoğraflıyoruz. Çekim sırasında size rehberlik ediyoruz.',
        'icon' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>'
    ],
    [
        'number' => '06',
        'title' => 'Hızlı Teslimat',
        'description' => 'Çekimden sonra 48-96 saat içinde profesyonel olarak düzenlenmiş fotoğraflarınızı teslim ediyoruz. Revizyon desteği sunuyoruz.',
        'icon' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>'
    ]
];

// FAQ items
$faqItems = [
    [
        'question' => 'Hizmetlere göre iş süreleri nasıl değişir?',
        'answer' => 'Her hizmet türünün kendine özgü gereksinimleri vardır. Örneğin, bir restoran çekimi 2-4 saat sürerken, büyük bir otel projesi birkaç gün alabilir. Mimari fotoğrafçılık için doğal ışık koşullarını beklemek gerekebilir. Proje kapsamını değerlendirdikten sonra size net bir zaman çizelgesi sunuyoruz.'
    ],
    [
        'question' => 'Proje planlaması neden bu kadar önemlidir?',
        'answer' => 'İyi bir planlama, çekimin kalitesini doğrudan etkiler. Işık koşulları, mekan hazırlığı, çekim açıları ve zamanlamanın hepsi önceden düşünülmelidir. Planlama sayesinde çekim günü verimli geçer, gereksiz zaman kaybı olmaz ve sonuçlar beklentilerinizi karşılar. Ayrıca, özel isteklerinizi ve markanızın kimliğini yansıtan görseller elde etmenizi sağlar.'
    ],
    [
        'question' => 'Günübirlik işlerde müşteri hazırlığı nedir?',
        'answer' => 'Günübirlik acil çekimlerde, mekanınızın çekime hazır olması kritik öneme sahiptir. Bu, alanın temiz ve düzenli olması, gereksiz eşyaların kaldırılması, ışıklandırmanın kontrol edilmesi ve çekilecek ürünlerin/alanların hazır bulundurulması anlamına gelir. Size önceden bir hazırlık listesi gönderiyor ve çekim öncesi kısa bir kontrol yapıyoruz.'
    ],
    [
        'question' => 'Teslimat süresi neden 48-96 saat arasında değişir?',
        'answer' => 'Teslimat süresi, çekilen fotoğraf sayısına ve düzenleme gereksinimine göre değişir. Basit bir mekan çekimi 48 saat içinde teslim edilebilirken, kapsamlı renk düzeltmesi, perspektif düzeltmesi ve özel efektler gerektiren projeler 96 saate kadar sürebilir. Acil teslimat ihtiyacınız varsa, ek ücret karşılığında ekspres hizmet sunuyoruz.'
    ],
    [
        'question' => 'Revizyon süreci nasıl işler?',
        'answer' => 'İlk teslimat sonrası, fotoğrafları incelemeniz için size zaman tanıyoruz. Renk düzeltmesi, kırpma veya küçük düzenlemeler gibi makul revizyonlar paket fiyatımıza dahildir (genellikle 2 revizyon hakkı). Büyük değişiklikler veya ek çekim gerektiren talepler için ayrı fiyat teklifi sunuyoruz. Amacımız, %100 memnuniyetinizi sağlamaktır.'
    ],
    [
        'question' => 'Çekim için hangi ekipmanları kullanıyorsunuz?',
        'answer' => 'Profesyonel full-frame DSLR/mirrorless kameralar, geniş açı ve tilt-shift lensler, tripod, harici flaşlar ve gerektiğinde drone kullanıyoruz. Tüm ekipmanlarımız düzenli olarak bakımdan geçirilir ve yedek ekipman her zaman hazırdır. Bu sayede teknik sorunlardan kaynaklı aksaklıklar yaşanmaz.'
    ]
];
?>

<!-- Hero Section -->
<section
    class="relative h-[50vh] md:h-[60vh] min-h-[400px] md:min-w-[500px] flex items-center justify-center overflow-hidden bg-slate-950">
    <img src="https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&w=1920"
        alt="Nasıl Çalışır" class="absolute inset-0 w-full h-full object-cover opacity-40">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/80 via-slate-950/50 to-slate-950"></div>

    <div class="relative z-10 container mx-auto px-4 text-center pt-32 md:pt-0">
        <span class="text-brand-400 font-black tracking-[0.3em] uppercase text-xs mb-6 block">Süreç</span>
        <h1 class="font-heading font-black text-4xl md:text-7xl text-white mb-6 tracking-tight">
            Nasıl Çalışır?
        </h1>
        <p class="text-slate-300 text-lg md:text-2xl font-light max-w-3xl mx-auto leading-relaxed">
            Profesyonel mekan fotoğrafçılığı hizmetimizin adım adım iş akışını keşfedin
        </p>
    </div>
</section>

<!-- Workflow Timeline -->
<section class="py-16 md:py-32 bg-white relative overflow-hidden">
    <!-- Decorative elements -->
    <div
        class="absolute top-0 right-0 w-[600px] h-[600px] bg-brand-50 rounded-full blur-[150px] translate-x-1/2 -translate-y-1/2 opacity-50">
    </div>
    <div
        class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-accent-50 rounded-full blur-[120px] -translate-x-1/2 translate-y-1/2 opacity-40">
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-16 md:mb-20">
            <span class="text-brand-600 font-black tracking-[0.2em] uppercase text-xs mb-6 block">İş Akışı</span>
            <h2 class="font-heading font-black text-3xl md:text-6xl text-slate-900 mb-6 md:mb-8">6 Adımda Mükemmel Sonuç
            </h2>
            <p class="text-slate-500 text-lg md:text-xl leading-relaxed">
                Teklif talebinden teslimat aşamasına kadar her adımda yanınızdayız
            </p>
        </div>

        <div class="max-w-6xl mx-auto">
            <?php foreach ($workflowSteps as $index => $step): ?>
                <div class="relative mb-16 last:mb-0">
                    <!-- Timeline connector -->
                    <?php if ($index < count($workflowSteps) - 1): ?>
                        <div
                            class="hidden md:block absolute left-[72px] top-32 w-0.5 h-24 bg-gradient-to-b from-brand-200 to-transparent">
                        </div>
                    <?php endif; ?>

                    <div class="group flex flex-col md:flex-row gap-6 md:gap-8 items-center md:items-start">
                        <!-- Step number circle -->
                        <div class="flex-shrink-0 relative">
                            <div
                                class="w-24 h-24 md:w-36 md:h-36 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-2xl shadow-brand-500/30 group-hover:scale-110 transition-transform duration-500">
                                <span class="font-heading font-black text-3xl md:text-5xl text-white">
                                    <?= $step['number'] ?>
                                </span>
                            </div>
                            <div
                                class="absolute inset-0 rounded-full bg-brand-400 blur-xl opacity-0 group-hover:opacity-50 transition-opacity duration-500">
                            </div>
                        </div>

                        <!-- Content card -->
                        <div
                            class="flex-1 glass-panel p-6 md:p-10 rounded-3xl md:rounded-4xl border-white/60 group-hover:border-brand-200 transition-all duration-500 hover-lift text-center md:text-left">
                            <div class="flex flex-col md:flex-row items-center md:items-start gap-4 md:gap-6">
                                <div
                                    class="flex-shrink-0 w-12 h-12 md:w-16 md:h-16 rounded-xl md:rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center group-hover:bg-brand-600 group-hover:text-white transition-all duration-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" md:width="28"
                                        md:height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <?= $step['icon'] ?>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3
                                        class="text-xl md:text-3xl font-black text-slate-900 mb-3 md:mb-4 group-hover:text-brand-600 transition-colors">
                                        <?= $step['title'] ?>
                                    </h3>
                                    <p class="text-slate-600 text-sm md:text-lg leading-relaxed">
                                        <?= $step['description'] ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-32 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto mb-20">
            <span class="text-brand-600 font-black tracking-[0.2em] uppercase text-xs mb-6 block">Sık Sorulan
                Sorular</span>
            <h2 class="font-heading font-black text-4xl md:text-6xl text-slate-900 mb-8">Merak Edilenler</h2>
            <p class="text-slate-500 text-xl leading-relaxed">
                Hizmetlerimiz hakkında en çok sorulan soruların yanıtları
            </p>
        </div>

        <div class="max-w-4xl mx-auto space-y-4">
            <?php foreach ($faqItems as $index => $faq): ?>
                <div class="glass-panel rounded-3xl border-white/60 overflow-hidden hover-lift">
                    <button onclick="toggleFaq(<?= $index ?>)"
                        class="w-full px-8 py-6 flex items-center justify-between text-left group">
                        <span class="text-xl font-bold text-slate-900 pr-8 group-hover:text-brand-600 transition-colors">
                            <?= $faq['question'] ?>
                        </span>
                        <svg id="faq-icon-<?= $index ?>" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                            stroke-linejoin="round" class="flex-shrink-0 text-brand-600 transition-transform duration-300">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                    <div id="faq-content-<?= $index ?>" class="grid grid-rows-[0fr] transition-all duration-300">
                        <div class="min-h-0 overflow-hidden">
                            <div class="px-8 pb-6 pt-2">
                                <p class="text-slate-600 text-lg leading-relaxed">
                                    <?= $faq['answer'] ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 md:py-32 bg-gradient-to-br from-brand-600 to-brand-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 text-center relative z-10">
        <h2 class="font-heading font-black text-3xl md:text-6xl text-white mb-6 md:mb-8">
            Projenize Başlayalım
        </h2>
        <p class="text-brand-100 text-lg md:text-2xl mb-10 md:mb-12 max-w-2xl mx-auto leading-relaxed">
            Mekanınızı en iyi şekilde yansıtan profesyonel fotoğraflar için hemen teklif alın
        </p>
        <button onclick="openQuoteWizard()"
            class="inline-flex items-center gap-4 px-8 md:px-12 py-5 md:py-6 bg-white text-brand-600 rounded-full text-base md:text-lg font-black uppercase tracking-widest shadow-2xl hover:scale-105 transition-all active:scale-95">
            Ücretsiz Teklif Al
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14" />
                <path d="m12 5 7 7-7 7" />
            </svg>
        </button>
    </div>
</section>

<script>
    function toggleFaq(index) {
        const content = document.getElementById(`faq-content-${index}`);
        const icon = document.getElementById(`faq-icon-${index}`);
        const isOpen = content.style.gridTemplateRows === '1fr';

        // Close all other FAQs
        document.querySelectorAll('[id^="faq-content-"]').forEach((el, i) => {
            if (i !== index) {
                el.style.gridTemplateRows = '0fr';
                document.getElementById(`faq-icon-${i}`).style.transform = 'rotate(0deg)';
            }
        });

        // Toggle current FAQ
        content.style.gridTemplateRows = isOpen ? '0fr' : '1fr';
        icon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    }
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>