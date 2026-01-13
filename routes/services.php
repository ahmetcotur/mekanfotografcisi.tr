<?php
/**
 * Services Overview Page
 * /services
 */

require_once __DIR__ . '/../includes/supabase.php';
require_once __DIR__ . '/../includes/helpers.php';

// Get services from database
$services = $supabase->select('services', [
    'is_active' => 'eq.true',
    'select' => 'id,name,slug,short_intro'
]);

// Add default images for services
$serviceImages = [
    'mimari-fotografcilik' => '/assets/images/mimari-fotograf.jpg',
    'ic-mekan-fotografciligi' => '/assets/images/ic-mekan.jpg',
    'emlak-fotografciligi' => '/assets/images/emlak-fotograf.jpg',
    'otel-restoran-fotografciligi' => '/assets/images/otel-restoran.jpg',
    'butik-otel-fotografciligi' => '/assets/images/portfolio-1.jpg',
    'yemek-fotografciligi' => '/assets/images/portfolio-2.jpg',
    'lifestyle-fotografciligi' => '/assets/images/portfolio-3.jpg',
    'villa-fotografciligi' => '/assets/images/portfolio-1.jpg',
    'otel-fotografciligi' => '/assets/images/portfolio-2.jpg',
    'yat-fotografciligi' => '/assets/images/portfolio-3.jpg',
    'konut-projeleri-fotografciligi' => '/assets/images/portfolio-4.jpg',
    'ofis-fotografciligi' => '/assets/images/portfolio-5.jpg',
    'is-merkezi-fotografciligi' => '/assets/images/portfolio-6.jpg',
    'ticari-alan-fotografciligi' => '/assets/images/portfolio-1.jpg',
    'pansiyon-fotografciligi' => '/assets/images/portfolio-2.jpg',
    'termal-tesis-fotografciligi' => '/assets/images/portfolio-3.jpg'
];

// Add images to services
$servicesWithImages = [];
foreach ($services as $service) {
    $service['image'] = $serviceImages[$service['slug']] ?? '/assets/images/portfolio-1.jpg';
    $servicesWithImages[] = $service;
}
$services = $servicesWithImages;

// Get top provinces for service cards
$topProvinces = $supabase->select('locations_province', [
    'is_active' => 'eq.true',
    'select' => 'name,slug',
    'order' => 'name',
    'limit' => 4
]);

$pageTitle = 'Hizmetlerimiz | Mekan Fotoğrafçısı';
$pageDescription = 'Antalya ve Muğla bölgesinde sunduğumuz profesyonel mekan fotoğrafçılığı hizmetleri. Mimari, iç mekan, emlak ve otel fotoğrafçılığı.';
$canonicalUrl = 'https://mekanfotografcisi.tr/services';

$schemaMarkup = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => 'Mekan Fotoğrafçılığı Hizmetleri',
    'description' => $pageDescription,
    'provider' => [
        '@type' => 'Organization',
        'name' => 'Mekan Fotoğrafçısı',
        'url' => 'https://mekanfotografcisi.tr'
    ],
    'areaServed' => 'TR',
    'serviceType' => 'Fotoğrafçılık Hizmetleri'
];

include __DIR__ . '/../templates/page-header.php';
?>

<main class="seo-page">
    <!-- Breadcrumbs -->
    <section class="breadcrumbs">
        <div class="container">
            <a href="/">Ana Sayfa</a>
            <span>›</span>
            <strong>Hizmetlerimiz</strong>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="seo-hero">
        <div class="container">
            <h1>Profesyonel Mekan Fotoğrafçılığı Hizmetlerimiz</h1>
            <p>Antalya ve Muğla bölgesinde 10 yılı aşkın deneyimimizle, her türlü mekan için profesyonel fotoğrafçılık hizmetleri sunuyoruz.</p>
            <div class="hero-buttons">
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
                <a href="/#iletisim" class="btn btn-outline">Çekim Planla</a>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="grid-section">
        <div class="container">
            <div class="grid-2">
                <?php foreach ($services as $index => $service): ?>
                <div class="modern-card">
                    <div class="modern-card-image">
                        <img src="<?= e($service['image']) ?>" alt="<?= e($service['name']) ?>" loading="lazy">
                    </div>
                    <div class="modern-card-content">
                        <span class="modern-card-badge">Hizmet <?= $index + 1 ?></span>
                        <h2><?= e($service['name']) ?></h2>
                        <p><?= e($service['short_intro']) ?></p>
                        
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                            <p style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 8px;">
                                <strong>Bu hizmeti sunduğumuz bölgeler:</strong>
                            </p>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                                <?php foreach ($topProvinces as $province): ?>
                                    <a href="/locations/<?= e($province['slug']) ?>" style="display: inline-block; padding: 3px 8px; background: var(--primary-color); color: white; border-radius: 4px; font-size: 0.75rem; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                        <?= e($province['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                                <a href="/locations" style="display: inline-block; padding: 3px 8px; background: var(--accent-color); color: white; border-radius: 4px; font-size: 0.75rem; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                    Tümünü Gör →
                                </a>
                            </div>
                        </div>
                        
                        <div class="modern-card-footer">
                            <a href="/services/<?= e($service['slug']) ?>" class="btn btn-primary btn-block">Detayları İncele →</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section class="process-section alt-bg">
        <div class="container">
            <h2 class="section-title">Çalışma Sürecimiz</h2>
            <p class="section-subtitle">Profesyonel fotoğrafçılık hizmetlerimizde izlediğimiz adımlar</p>
            
            <div class="process-grid">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <h3>İlk Görüşme</h3>
                    <p>Projenizi detaylı olarak dinliyor, beklentilerinizi anlıyor ve size özel çözümler sunuyoruz.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <h3>Planlama</h3>
                    <p>Mekanın özelliklerine göre çekim planını hazırlıyor, en uygun zamanı belirliyoruz.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <h3>Profesyonel Çekim</h3>
                    <p>Uzman ekibimiz ve son teknoloji ekipmanlarla yüksek kaliteli fotoğraflar çekiyoruz.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">4</div>
                    <h3>Post-Prodüksiyon</h3>
                    <p>Fotoğraflarınızı profesyonel yazılımlarla düzenleyip, kalite kontrolünden geçiriyoruz.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">5</div>
                    <h3>Teslimat</h3>
                    <p>3-5 iş günü içinde yüksek çözünürlüklü fotoğraflarınızı dijital ortamda teslim ediyoruz.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <div class="stat-label">Tamamlanan Proje</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">80+</span>
                    <div class="stat-label">Hizmet Verdiğimiz Şehir</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">10+</span>
                    <div class="stat-label">Yıl Deneyim</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">%100</span>
                    <div class="stat-label">Müşteri Memnuniyeti</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Hangi Hizmeti Tercih Edersiniz?</h2>
            <p>Profesyonel ekibimiz ve modern ekipmanlarımızla, her türlü mekan fotoğrafçılığı ihtiyacınızı karşılıyoruz.</p>
            <div class="cta-buttons">
                <a href="tel:+905074677502" class="btn btn-outline">📞 +90 507 467 75 02</a>
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
