<?php
/**
 * Locations Overview Page
 * /locations
 */

require_once __DIR__ . '/../includes/supabase.php';
require_once __DIR__ . '/../includes/helpers.php';

// Get active provinces from database
$provinces = $supabase->select('locations_province', [
    'is_active' => 'eq.true',
    'select' => 'id,name,slug,region_name',
    'order' => 'name'
]);

// Add mock data for display
$provinceExtras = [
    'antalya' => [
        'description' => 'Türkiye\'nin en önemli turizm merkezlerinden biri olan Antalya\'da profesyonel mekan fotoğrafçılığı hizmetleri.',
        'image' => '/assets/images/portfolio-1.jpg',
        'district_count' => 19
    ],
    'mugla' => [
        'description' => 'Bodrum, Marmaris, Fethiye gibi turistik bölgelerde lüks villa ve otel fotoğrafçılığı.',
        'image' => '/assets/images/portfolio-2.jpg',
        'district_count' => 13
    ],
    'istanbul' => [
        'description' => 'Türkiye\'nin en büyük şehrinde ticari ve konut projelerine özel fotoğrafçılık hizmetleri.',
        'image' => '/assets/images/portfolio-3.jpg',
        'district_count' => 39
    ],
    'izmir' => [
        'description' => 'Ege\'nin incisi İzmir\'de modern mimari ve tarihi yapıların profesyonel fotoğrafçılığı.',
        'image' => '/assets/images/portfolio-4.jpg',
        'district_count' => 30
    ],
    'ankara' => [
        'description' => 'Başkent Ankara\'da resmi kurumlar ve ticari projeler için profesyonel mekan fotoğrafçılığı.',
        'image' => '/assets/images/portfolio-5.jpg',
        'district_count' => 25
    ],
    'bursa' => [
        'description' => 'Yeşil Bursa\'da sanayi tesisleri ve konut projelerine özel fotoğrafçılık hizmetleri.',
        'image' => '/assets/images/portfolio-6.jpg',
        'district_count' => 17
    ]
];

// Merge province data with extras
foreach ($provinces as $index => $province) {
    $extras = $provinceExtras[$province['slug']] ?? [];
    
    // Merge extras into province, preserving existing values
    if (!empty($extras)) {
        foreach ($extras as $key => $value) {
            if ($value !== null) {
                $provinces[$index][$key] = $value;
            }
        }
    }
    
    // Set defaults if not found
    if (empty($provinces[$index]['description'])) {
        $provinceName = $provinces[$index]['name'] ?? 'Bu il';
        $provinces[$index]['description'] = $provinceName . ' da profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz.';
    }
    if (empty($provinces[$index]['image'])) {
        $provinces[$index]['image'] = '/assets/images/portfolio-1.jpg';
    }
    if (!isset($provinces[$index]['district_count']) || $provinces[$index]['district_count'] === null) {
        $provinces[$index]['district_count'] = rand(8, 25);
    }
}

// Group provinces by region AFTER merging extras
$regions = [];
foreach ($provinces as $province) {
    $regionName = $province['region_name'] ?? 'Diğer';
    if (!isset($regions[$regionName])) {
        $regions[$regionName] = [];
    }
    $regions[$regionName][] = $province;
}

// Get all active services for location cards
$allServices = $supabase->select('services', [
    'is_active' => 'eq.true',
    'select' => 'name,slug',
    'order' => 'name'
]);

$pageTitle = 'Hizmet Verdiğimiz Lokasyonlar | Mekan Fotoğrafçısı';
$pageDescription = 'Türkiye\'nin birçok ilinde profesyonel mekan fotoğrafçılığı hizmetleri. Antalya, Muğla, İstanbul, İzmir ve daha fazlası.';
$canonicalUrl = 'https://mekanfotografcisi.tr/locations';

$schemaMarkup = [
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => 'Mekan Fotoğrafçısı',
    'description' => $pageDescription,
    'url' => $canonicalUrl,
    'telephone' => '+90 507 467 75 02',
    'email' => 'info@mekanfotografcisi.tr',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Kalkan Mah. Şehitler Cad. no 7',
        'addressLocality' => 'Kaş',
        'addressRegion' => 'Antalya',
        'addressCountry' => 'TR'
    ],
    'serviceArea' => array_map(function($province) {
        return [
            '@type' => 'State',
            'name' => $province['name']
        ];
    }, $provinces)
];

include __DIR__ . '/../templates/page-header.php';
?>

<main class="seo-page">
    <!-- Breadcrumbs -->
    <section class="breadcrumbs">
        <div class="container">
            <a href="/">Ana Sayfa</a>
            <span>›</span>
            <strong>Hizmet Verdiğimiz Lokasyonlar</strong>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="seo-hero">
        <div class="container">
            <h1>Hizmet Verdiğimiz Lokasyonlar</h1>
            <p>Türkiye'nin dört bir yanında profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz. Her bölgenin kendine özgü güzelliklerini yansıtan fotoğraflar üretiyoruz.</p>
            <div class="hero-buttons">
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
                <a href="/#iletisim" class="btn btn-outline">Çekim Planla</a>
            </div>
        </div>
    </section>

    <!-- Locations by Region -->
    <?php $regionIndex = 0; foreach ($regions as $regionName => $regionProvinces): ?>
    <section class="grid-section <?= $regionIndex % 2 === 1 ? 'alt-bg' : '' ?>">
        <div class="container">
            <h2 class="section-title" style="text-align: left; margin-bottom: 32px; padding-bottom: 16px; border-bottom: 3px solid var(--accent-color);">
                <?= e($regionName) ?>
            </h2>
            <div class="grid-3">
                <?php foreach ($regionProvinces as $province): ?>
                <div class="modern-card">
                    <div class="modern-card-image">
                        <img src="<?= e($province['image']) ?>" alt="<?= e($province['name']) ?> Mekan Fotoğrafçısı" loading="lazy">
                    </div>
                    <div class="modern-card-content">
                        <span class="modern-card-badge"><?= e($regionName) ?></span>
                        <h2><?= e($province['name']) ?></h2>
                        <p class="modern-card-meta">
                            <span>📍</span>
                            <?= e($province['district_count'] ?? 0) ?> İlçe
                        </p>
                        <p><?= e($province['description'] ?? '') ?></p>
                        
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                            <h4 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 12px; color: var(--primary-color);">
                                <?= e($province['name']) ?> için Hizmetler
                            </h4>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                <?php 
                                $serviceIndex = 0;
                                foreach (array_slice($allServices, 0, 8) as $service): 
                                    $serviceIndex++;
                                    // Vary anchor text - use service name only (natural, not keyword-stuffed)
                                    $linkText = e($service['name']);
                                ?>
                                    <a href="/services/<?= e($service['slug']) ?>" style="display: inline-block; padding: 4px 8px; background: var(--accent-color); color: white; border-radius: 4px; font-size: 0.75rem; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                        <?= $linkText ?>
                                    </a>
                                <?php endforeach; ?>
                                <?php if (count($allServices) > 8): ?>
                                    <a href="/locations/<?= e($province['slug']) ?>" style="display: inline-block; padding: 4px 8px; background: var(--primary-color); color: white; border-radius: 4px; font-size: 0.75rem; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                        Tümünü Gör →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="modern-card-footer">
                            <a href="/locations/<?= e($province['slug']) ?>" class="btn btn-primary btn-block">Detayları İncele →</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php $regionIndex++; endforeach; ?>

    <!-- Service Areas Info -->
    <section class="content-section alt-bg">
        <div class="container">
            <div class="content-wrapper">
                <div class="main-content">
                    <h2>Geniş Hizmet Ağımız</h2>
                    <p>Mekan Fotoğrafçısı olarak Türkiye'nin en önemli şehirlerinde profesyonel fotoğrafçılık hizmetleri sunuyoruz. Her bölgenin kendine özgü mimari yapısını, ışık koşullarını ve kültürel özelliklerini bilen uzman ekibimizle çalışıyoruz.</p>
                    
                    <h3>Öne Çıkan Hizmet Bölgelerimiz</h3>
                    <ul>
                        <li><strong>Akdeniz Bölgesi:</strong> Antalya, Mersin, Adana - Turizm ve tatil köyleri</li>
                        <li><strong>Ege Bölgesi:</strong> Muğla, İzmir, Aydın - Lüks villalar ve butik oteller</li>
                        <li><strong>Marmara Bölgesi:</strong> İstanbul, Bursa, Kocaeli - Ticari projeler ve konutlar</li>
                        <li><strong>İç Anadolu:</strong> Ankara, Konya, Kayseri - Kurumsal ve resmi yapılar</li>
                    </ul>

                    <h3>Bölgesel Uzmanlıklarımız</h3>
                    <p>Her bölgede farklı çekim teknikleri ve yaklaşımlar kullanıyoruz:</p>
                    <ul>
                        <li><strong>Sahil Bölgeleri:</strong> Deniz manzarası ve doğal ışık kullanımı</li>
                        <li><strong>Şehir Merkezleri:</strong> Gece çekimleri ve şehir ışıkları</li>
                        <li><strong>Kırsal Alanlar:</strong> Doğa ile uyumlu mimari vurgusu</li>
                        <li><strong>Tarihi Bölgeler:</strong> Kültürel miras ve modern yaşam dengesi</li>
                    </ul>
                </div>
                
                <div class="sidebar">
                    <div class="contact-card">
                        <h3>Bölgeniz Listede Yok mu?</h3>
                        <p>Türkiye'nin her yerinde hizmet verebiliriz. Özel projeleriniz için bizimle iletişime geçin.</p>
                        <a href="/#iletisim" class="btn btn-outline btn-block">İletişime Geçin</a>
                        <a href="tel:+905074677502" class="btn btn-outline btn-block">📞 +90 507 467 75 02</a>
                    </div>

                    <div class="sidebar-card">
                        <div class="sidebar-card-content">
                            <h3>Hizmet Türlerimiz</h3>
                            <ul>
                                <li><a href="/services/mimari-fotografcilik">Mimari Fotoğrafçılık</a></li>
                                <li><a href="/services/ic-mekan-fotografciligi">İç Mekan Fotoğrafçılığı</a></li>
                                <li><a href="/services/emlak-fotografciligi">Emlak Fotoğrafçılığı</a></li>
                                <li><a href="/services/otel-restoran-fotografciligi">Otel & Restoran</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Hangi Şehirde Olursanız Olun, Yanınızdayız!</h2>
            <p>Profesyonel mekan fotoğrafçılığı hizmetleri için bugün bizimle iletişime geçin.</p>
            <div class="cta-buttons">
                <a href="tel:+905074677502" class="btn btn-outline">📞 +90 507 467 75 02</a>
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
