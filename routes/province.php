<?php
/**
 * Province Detail Page
 * /locations/{province}
 */

require_once __DIR__ . '/../includes/supabase.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/markdown.php';

$provinceSlug = sanitizeSlug($_GET['province'] ?? '');

// Redirect old slug formats to new ones (SEO-friendly redirects)
$slugRedirects = [
    'i-stanbul' => 'istanbul',
    'i-zmir' => 'izmir'
];

if (isset($slugRedirects[$provinceSlug])) {
    $newSlug = $slugRedirects[$provinceSlug];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: /locations/' . $newSlug);
    exit;
}

// Get province from database
$provinceResults = $supabase->select('locations_province', [
    'slug' => 'eq.' . $provinceSlug,
    'is_active' => 'eq.true',
    'select' => 'id,name,slug,region_name'
]);

$province = $provinceResults[0] ?? null;

if (!$province) {
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/../404.html';
    exit;
}

// Get districts for this province
$districts = $supabase->select('locations_district', [
    'province_id' => 'eq.' . $province['id'],
    'is_active' => 'eq.true',
    'select' => 'id,name,slug,local_notes'
]);

// Extended content for provinces
$provinceContent = [
    'antalya' => [
        'description' => 'Türkiye\'nin en önemli turizm merkezlerinden biri olan Antalya\'da profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz.',
        'image' => '/assets/images/portfolio-1.jpg',
        'content' => '
## Antalya Mekan Fotoğrafçısı

Antalya, Türkiye\'nin en önemli turizm merkezlerinden biri olarak, her yıl milyonlarca ziyaretçiyi ağırlıyor. Bu dinamik şehirde, otel işletmecilerinden villa sahiplerine, restoran işletmecilerinden emlak danışmanlarına kadar geniş bir müşteri kitlesine profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz.

### Antalya\'da Hizmet Verdiğimiz Alanlar

- **Turizm Tesisleri**: Otel, resort, tatil köyü fotoğrafçılığı
- **Villa ve Konut Projeleri**: Lüks konutların pazarlama fotoğrafları
- **Ticari Mekanlar**: Restoran, cafe, mağaza fotoğrafçılığı
- **Mimari Projeler**: Modern ve geleneksel yapıların belgelenmesi

### Antalya\'nın Özel Koşulları

Antalya\'nın eşsiz ışık koşulları ve doğal güzellikleri, mekan fotoğrafçılığında büyük avantajlar sağlıyor:

- **Akdeniz Işığı**: Yıl boyunca ideal doğal aydınlatma
- **Deniz Manzarası**: Mekanları değerli kılan manzara faktörü
- **Tarihi Doku**: Antik yapılar ve modern mimarinin uyumu
- **Tropikal Peyzaj**: Palmiye ve egzotik bitki örtüsü

### Çalışma Bölgelerimiz

Antalya merkez ve tüm ilçelerinde hizmet veriyoruz. Özellikle turizm yoğunluğunun fazla olduğu bölgelerde deneyimli ekibimizle çalışıyoruz.
        '
    ],
    'mugla' => [
        'description' => 'Bodrum, Marmaris, Fethiye gibi turistik bölgelerde lüks villa ve otel fotoğrafçılığı hizmetleri.',
        'image' => '/assets/images/portfolio-2.jpg',
        'content' => '
## Muğla Mekan Fotoğrafçısı

Muğla, Türkiye\'nin en prestijli tatil bölgelerinden birini oluşturuyor. Bodrum\'dan Datça\'ya, Marmaris\'ten Fethiye\'ye kadar uzanan geniş coğrafyada, lüks turizm tesisleri ve özel konutlar için profesyonel fotoğrafçılık hizmetleri sunuyoruz.

### Muğla\'da Uzmanlık Alanlarımız

- **Lüks Villa Fotoğrafçılığı**: Özel konutların pazarlama görselleri
- **Butik Otel Çekimleri**: Küçük ölçekli, özel hizmet veren tesisler
- **Marina ve Yat Kulüpleri**: Denizcilik tesisleri fotoğrafçılığı
- **Restoran ve Beach Club**: Sahil işletmeleri özel çekimleri

### Bölgesel Özellikler

Muğla\'nın her ilçesinin kendine özgü karakteristik özellikleri var:

- **Bodrum**: Kozmopolit yaşam tarzı ve modern mimari
- **Marmaris**: Doğal liman ve yeşillik içinde tesisler
- **Fethiye**: Ölüdeniz\'in eşsiz manzarası
- **Datça**: Sakin, butik turizm anlayışı

### Sezonsal Çekim Planlaması

Muğla\'da mevsimsel turizm yoğunluğu nedeniyle çekim planlaması önemli:

- **İlkbahar**: Açılış öncesi tesis hazırlık çekimleri
- **Yaz**: Canlı atmosfer ve müşteri deneyimi fotoğrafları
- **Sonbahar**: Sakin dönem, detay çekimleri
- **Kış**: Renovasyon sonrası yenileme fotoğrafları
        '
    ],
    'mersin' => [
        'description' => 'Mersin\'de iş merkezleri, ticari alanlar ve kurumsal mekanlar için profesyonel mekan fotoğrafçılığı hizmetleri.',
        'image' => '/assets/images/portfolio-6.jpg',
        'content' => '
## Mersin Mekan Fotoğrafçısı

Mersin, Türkiye\'nin önemli ticaret merkezlerinden biri. İş merkezleri, ticari kompleksler ve kurumsal mekanları ile öne çıkan bu bölgede, profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz.

### Mersin\'de Hizmet Verdiğimiz Alanlar

- **İş Merkezi Fotoğrafçılığı**: Ticari kompleksler ve iş merkezlerinin profesyonel görünümü
- **Ticari Alan Fotoğrafçılığı**: Mağaza, showroom ve perakende işletmelerinin çekici görselleri
- **Ofis Fotoğrafçılığı**: Kurumsal ofislerin modern görünümü
- **Emlak Fotoğrafçılığı**: Konut ve ticari gayrimenkul pazarlama görselleri
- **Mimari Fotoğrafçılık**: Bina dış cephe ve çevre düzenlemeleri

### Mersin\'de İş Merkezi Fotoğrafçılığı

Mersin\'in gelişen iş merkezleri için profesyonel fotoğrafçılık hizmetleri:
- Modern iş merkezlerinin dış cephe çekimleri
- Lobi ve ortak kullanım alanlarının görsel tanıtımı
- Ofis alanlarının ferah ve profesyonel görünümü
- Teknik altyapı ve özelliklerin vurgulanması
- İş merkezi tanıtımı için pazarlama görselleri

### Mersin\'de Ticari Alan Fotoğrafçılığı

Mersin\'in dinamik ticaret sektörü için özel hizmetler:
- Mağaza ve showroom iç mekan çekimleri
- Perakende işletmelerinin ürün sunumu
- Vitrin ve cephe görünümleri
- E-ticaret için ürün fotoğrafları
- Pazarlama kampanyaları için görsel içerik

### Mersin\'in Özel Koşulları

Mersin\'in ticari potansiyeli ve bölgesel özellikleri:
- **Ticaret Merkezi**: Liman kenti olarak ticari önemi
- **Gelişen Yapılaşma**: Yeni iş merkezleri ve ticari kompleksler
- **Karışık Ekonomi**: Turizm, tarım ve sanayi sektörleri
- **Modern Mimari**: Yeni yapılan iş merkezleri
        '
    ]
];

// Add default content for other provinces
$defaultContent = [
    'description' => $province['name'] . '\'da profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz.',
    'image' => '/assets/images/portfolio-1.jpg',
    'content' => '
## ' . $province['name'] . ' Mekan Fotoğrafçısı

' . $province['name'] . '\'da profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz. Deneyimli ekibimiz ve modern ekipmanlarımızla, her türlü mekan için kaliteli fotoğraflar üretiyoruz.

### Hizmet Alanlarımız

- **Mimari Fotoğrafçılık**: Binaların dış cephe ve detay çekimleri
- **İç Mekan Fotoğrafçılığı**: Ev, ofis ve ticari alan fotoğrafları
- **Emlak Fotoğrafçılığı**: Satış ve kiralama için pazarlama görselleri
- **Ticari Fotoğrafçılık**: İş yerleri ve kurumsal mekan çekimleri

### Neden Bizi Tercih Etmelisiniz?

- **Yerel Deneyim**: ' . $province['name'] . '\'nın özel koşullarını bilen ekip
- **Profesyonel Ekipman**: Son teknoloji kameralar ve aydınlatma
- **Hızlı Hizmet**: Esnek randevu ve hızlı teslimat
- **Kalite Garantisi**: Her projede mükemmellik standardı
    '
];

$provinceData = array_merge($province, $provinceContent[$provinceSlug] ?? $defaultContent);

// Add district specialties
foreach ($districts as $index => $district) {
    $districts[$index]['specialty'] = $district['local_notes'] ?? 'Mekan Fotoğrafçılığı';
}

// Get all active services for province services section
$allServices = $supabase->select('services', [
    'is_active' => 'eq.true',
    'select' => 'name,slug,short_intro',
    'order' => 'name'
]);

$pageTitle = e($province['name']) . ' Mekan Fotoğrafçısı | Profesyonel Mimari ve İç Mekan Fotoğrafçılığı';
$pageDescription = e($provinceData['description']);
$canonicalUrl = 'https://mekanfotografcisi.tr/locations/' . e($provinceSlug);

$schemaMarkup = [
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => 'Mekan Fotoğrafçısı',
    'description' => e($pageDescription),
    'url' => e($canonicalUrl),
    'telephone' => '+90 507 467 75 02',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Kalkan Mah. Şehitler Cad. no 7',
        'addressLocality' => e($province['name']),
        'addressRegion' => e($province['region_name']),
        'postalCode' => '07580',
        'addressCountry' => 'TR'
    ],
    'serviceArea' => [
        '@type' => 'State',
        'name' => e($province['name'])
    ]
];

include __DIR__ . '/../templates/page-header.php';
?>

<main class="seo-page">
    <!-- Breadcrumbs -->
    <section class="breadcrumbs">
        <div class="container">
            <a href="/">Ana Sayfa</a>
            <span>›</span>
            <a href="/locations">Lokasyonlar</a>
            <span>›</span>
            <strong><?= e($province['name']) ?></strong>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="seo-hero">
        <div class="container">
            <h1><?= e($province['name']) ?> Mekan Fotoğrafçısı</h1>
            <p><?= e($provinceData['description']) ?></p>
            <div class="hero-buttons">
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
                <a href="/#iletisim" class="btn btn-outline">Çekim Planla</a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content-section">
        <div class="container">
            <div class="content-wrapper">
                <div class="main-content">
                    <?= markdownToHtml($provinceData['content'] ?? 'İçerik hazırlanıyor...') ?>
                    
                    <!-- Services Section -->
                    <section style="margin-top: 48px; padding-top: 48px; border-top: 2px solid var(--border-color);">
                        <h2 style="margin-bottom: 32px; padding-bottom: 16px; border-bottom: 3px solid var(--accent-color);">
                            <?= e($province['name']) ?> için Hizmetlerimiz
                        </h2>
                        <p style="margin-bottom: 32px; color: var(--text-light);">
                            <?= e($province['name']) ?>'da sunduğumuz tüm profesyonel mekan fotoğrafçılığı hizmetlerimiz. Her hizmet için detaylı bilgi almak üzere ilgili sayfayı ziyaret edebilirsiniz.
                        </p>
                        <div class="grid-3" style="margin-top: 32px;">
                            <?php 
                            $serviceCount = 0;
                            foreach ($allServices as $service): 
                                $serviceCount++;
                                // Vary anchor text for SEO
                                if ($serviceCount <= 4) {
                                    $linkText = e($service['name']);
                                } elseif ($serviceCount <= 8) {
                                    $linkText = e($province['name']) . ' ' . e($service['name']);
                                } else {
                                    $linkText = e($service['name']) . ' hizmeti';
                                }
                            ?>
                            <div class="modern-card">
                                <div class="modern-card-content">
                                    <h3 style="font-size: 1.1rem; margin-bottom: 8px;">
                                        <a href="/services/<?= e($service['slug']) ?>" style="color: var(--primary-color); text-decoration: none;">
                                            <?= $linkText ?>
                                        </a>
                                    </h3>
                                    <p style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 16px;">
                                        <?= e($service['short_intro']) ?>
                                    </p>
                                    <a href="/services/<?= e($service['slug']) ?>" class="btn btn-outline btn-block" style="font-size: 0.85rem; padding: 8px 16px;">
                                        Detayları İncele →
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
                
                <div class="sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-card-image">
                            <img src="<?= e($provinceData['image'] ?? '/assets/images/portfolio-1.jpg') ?>" alt="<?= e($province['name']) ?> Mekan Fotoğrafçısı" loading="lazy">
                        </div>
                        <div class="sidebar-card-content">
                            <h3><?= e($province['name']) ?> Bilgileri</h3>
                            <p><strong>Bölge:</strong> <?= e($province['region_name']) ?></p>
                            <p><strong>Hizmet Alanları:</strong> <?= count($districts) ?> İlçe</p>
                            <p><strong>Uzmanlık:</strong> Turizm ve Emlak Fotoğrafçılığı</p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <h3><?= e($province['name']) ?>'da Çekim Planlayın</h3>
                        <p>Bu bölgedeki projeleriniz için özel fiyat teklifi alın.</p>
                        <a href="/#iletisim" class="btn btn-outline btn-block">İletişime Geçin</a>
                        <a href="tel:+905074677502" class="btn btn-outline btn-block">📞 +90 507 467 75 02</a>
                    </div>

                    <?php if (!empty($districts)): ?>
                    <div class="sidebar-card">
                        <div class="sidebar-card-content">
                            <h3><?= e($province['name']) ?> İlçeleri</h3>
                            <ul>
                                <?php foreach ($districts as $district): ?>
                                <li>
                                    <a href="/locations/<?= e($provinceSlug) ?>/<?= e($district['slug']) ?>">
                                        <?= e($district['name']) ?>
                                    </a>
                                    <?php if (!empty($district['specialty']) && $district['specialty'] !== 'Mekan Fotoğrafçılığı'): ?>
                                    <span style="color: var(--text-light); font-size: 0.85em; display: block; margin-left: 1em;">
                                        - <?= e($district['specialty']) ?>
                                    </span>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Districts Section -->
    <?php if (!empty($districts)): ?>
    <section class="grid-section alt-bg">
        <div class="container">
            <h2 class="section-title" style="text-align: left; margin-bottom: 48px; padding-bottom: 16px; border-bottom: 3px solid var(--accent-color);">
                Hizmet Verdiğimiz İlçeler
            </h2>
            <div class="grid-4">
                <?php foreach ($districts as $district): ?>
                <div class="modern-card">
                    <div class="modern-card-content">
                        <h3 style="font-size: 1.25rem; margin-bottom: 12px;">
                            <a href="/locations/<?= e($provinceSlug) ?>/<?= e($district['slug']) ?>" style="color: var(--primary-color); text-decoration: none;">
                                <?= e($district['name']) ?>
                            </a>
                        </h3>
                        <p class="modern-card-meta">
                            <span>📍</span>
                            <?= e($district['specialty']) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2><?= e($province['name']) ?>'da Profesyonel Fotoğrafçılık Hizmeti</h2>
            <p>Bu bölgedeki projeleriniz için deneyimli ekibimizle iletişime geçin.</p>
            <div class="cta-buttons">
                <a href="tel:+905074677502" class="btn btn-outline">📞 +90 507 467 75 02</a>
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
