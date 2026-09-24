<?php
/**
 * Template: Nasıl Çalışır (How It Works)
 * Explains both the client workflow and the photographer workflow.
 */
$pageTitle = 'Nasıl Çalışır?';
$pageDescription = 'Mekanını çektirmek isteyenler için de, kolektife katılacak fotoğrafçılar için de adım adım iş akışını keşfedin.';
include __DIR__ . '/../page-header.php';

// Workflow steps (client / venue owner track)
$workflowSteps = [
    [
        'number' => '01',
        'title' => 'Talebini oluştur',
        'description' => 'Teklif formunda mekanının türünü, konumunu ve ne zaman çekim istediğini anlat. İki dakika sürer, ücretsizdir.',
        'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'
    ],
    [
        'number' => '02',
        'title' => 'Uygun fotoğrafçılarla eşleş',
        'description' => 'Talebin, bölgende çalışan ve uzmanlığı mekanına uyan onaylı fotoğrafçılara iletilir.',
        'icon' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>'
    ],
    [
        'number' => '03',
        'title' => 'Teklifleri karşılaştır ve seç',
        'description' => 'Fotoğrafçıların portfolyolarına, müşteri yorumlarına ve tekliflerine bak; sana en uygun olanla anlaş.',
        'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'
    ],
    [
        'number' => '04',
        'title' => 'Çekimi planla',
        'description' => 'Fotoğrafçınla tarihi, ışık koşullarını ve çekilecek alanları netleştir. Mekanın hazırlığı için öneriler alırsın.',
        'icon' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>'
    ],
    [
        'number' => '05',
        'title' => 'Fotoğraflarını teslim al',
        'description' => 'Düzenlenmiş, yayına hazır fotoğraflar genellikle birkaç iş günü içinde teslim edilir. Çekim sonrası fotoğrafçını değerlendirebilirsin.',
        'icon' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>'
    ],
];

// Workflow steps (photographer / freelancer track)
$photographerWorkflowSteps = [
    [
        'number' => '01',
        'title' => 'Kayıt Ol',
        'description' => 'Birkaç dakika içinde ücretsiz kayıt ol, kolektife katıl.',
        'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/>'
    ],
    [
        'number' => '02',
        'title' => 'Profilini Oluştur',
        'description' => 'Uzmanlık alanlarını, bölgeni ve portfolyonu ekle; müşteriler seni bu bilgilerle bulsun.',
        'icon' => '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>'
    ],
    [
        'number' => '03',
        'title' => 'Açık Talepleri Gör',
        'description' => 'Panelindeki "Açık İşler" sekmesinden bölgene ve uzmanlığına uygun çekim taleplerini incele.',
        'icon' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>'
    ],
    [
        'number' => '04',
        'title' => 'Üstlen & Teklif Ver',
        'description' => 'İlgilendiğin talebi üstlen veya kendi teklifini gönder; müşteriyle doğrudan iletişime geç.',
        'icon' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="9" x2="15" y1="15" y2="15"/>'
    ],
    [
        'number' => '05',
        'title' => 'Çekimi Tamamla & Ödeme Al',
        'description' => 'Çekimi gerçekleştir, teslim et ve ödemeni al. Profilin büyüdükçe daha fazla talebe erişirsin.',
        'icon' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>'
    ],
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
        'question' => 'Fotoğrafçılar nasıl onaylanıyor?',
        'answer' => 'Kolektife katılan her fotoğrafçının başvurusu, deneyimi ve portfolyosu incelenir. Yalnızca onaylanan fotoğrafçılar herkese açık dizinde görünür ve talepleri üstlenebilir. Tamamlanan işlerden sonra müşteri değerlendirmeleri profillerde yayınlanır.'
    ],
    [
        'question' => 'Fotoğrafçı olarak nasıl kolektife katılabilirim?',
        'answer' => 'Fotoğrafçı kayıt formundan ücretsiz kayıt olabilir, profilinizi oluşturduktan sonra panelinizden açık çekim taleplerine erişebilirsiniz. Detaylı süreç için yukarıdaki "Fotoğrafçılar için" bölümüne göz atın.'
    ]
];

$heroEyebrow = 'Nasıl çalışır?';
$heroTitle = 'İki taraf, tek bir kolay süreç';
$heroLead = 'Mekan sahipleri talebini iletir, bölgedeki fotoğrafçılar teklif verir. İşte adım adım nasıl ilerlediği.';
$heroCrumbs = [['href' => '/nasil-calisir', 'label' => 'Nasıl çalışır?']];
$heroActions = '<a href="#mekan-sahipleri" class="btn btn-dark btn-lg">Mekan sahibiyim</a><a href="#fotografcilar" class="btn btn-outline btn-lg">Fotoğrafçıyım</a>';

$renderSteps = function ($steps) {
    ob_start(); ?>
    <ol class="relative mt-10 space-y-4 before:absolute before:bottom-6 before:left-6 before:top-6 before:w-px before:bg-line md:before:left-7">
        <?php foreach ($steps as $step): ?>
            <li class="relative flex gap-5 md:gap-6">
                <span class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-line bg-white text-brand-700 md:h-14 md:w-14">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $step['icon'] ?></svg>
                </span>
                <div class="card flex-1 p-5 md:p-6">
                    <p class="text-sm font-semibold text-brand-700">Adım <?= e($step['number']) ?></p>
                    <h3 class="mt-1 font-semibold"><?= e($step['title']) ?></h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-ink-soft"><?= e($step['description']) ?></p>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php return ob_get_clean();
};
?>

<main id="main">
    <?php include __DIR__ . '/../partials/page-hero.php'; ?>

    <section class="section" id="mekan-sahipleri">
        <div class="container-page grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <div class="lg:sticky lg:top-[calc(var(--header-h)+2rem)]">
                    <p class="eyebrow">Mekan sahipleri için</p>
                    <h2 class="h-section mt-3">Talepten teslimata</h2>
                    <p class="mt-4 text-ink-soft">Tek bir form doldurursun; gerisini bölgendeki fotoğrafçılarla birlikte planlarsın.</p>
                    <button type="button" onclick="openQuoteWizard()" class="btn btn-primary btn-lg mt-8">Ücretsiz teklif al <?= icon('arrow-right', 'h-4 w-4') ?></button>
                </div>
            </div>
            <div class="lg:col-span-8"><?= $renderSteps($workflowSteps) ?></div>
        </div>
    </section>

    <section class="section border-y border-line bg-white" id="fotografcilar">
        <div class="container-page grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <div class="lg:sticky lg:top-[calc(var(--header-h)+2rem)]">
                    <p class="eyebrow">Fotoğrafçılar için</p>
                    <h2 class="h-section mt-3">Kolektife katıl, işini seç</h2>
                    <p class="mt-4 text-ink-soft">Kayıt ücretsiz. Profilin onaylandıktan sonra bölgene ve uzmanlığına uyan talepleri panelinde görürsün.</p>
                    <a href="/kayit/fotografci" class="btn btn-dark btn-lg mt-8">Fotoğrafçı olarak katıl</a>
                </div>
            </div>
            <div class="lg:col-span-8"><?= $renderSteps($photographerWorkflowSteps) ?></div>
        </div>
    </section>

    <section class="section">
        <div class="container-page grid gap-10 lg:grid-cols-3">
            <div>
                <p class="eyebrow">SSS</p>
                <h2 class="h-section mt-3">Merak edilenler</h2>
            </div>
            <div class="divide-y divide-line border-y border-line lg:col-span-2">
                <?php foreach ($faqItems as $faq): ?>
                    <details class="group py-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold [&::-webkit-details-marker]:hidden">
                            <?= e($faq['question']) ?>
                            <span class="text-ink-muted transition group-open:rotate-180"><?= icon('chevron-down') ?></span>
                        </summary>
                        <p class="mt-3 max-w-2xl text-ink-soft"><?= e($faq['answer']) ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../partials/cta-band.php'; ?>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
