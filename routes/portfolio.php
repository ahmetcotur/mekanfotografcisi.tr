<?php
/**
 * Portfolio Overview Page
 * /portfolio
 */

require_once __DIR__ . '/../includes/supabase.php';
require_once __DIR__ . '/../includes/helpers.php';

// Get published portfolio projects from database
$portfolioProjects = $supabase->select('portfolio_projects', [
    'is_published' => 'eq.true',
    'select' => 'id,title,slug,description,year,locations_province(name),locations_district(name)',
    'order' => 'year.desc'
]);

// Add default images and categories for display
$projectExtras = [
    'modern-villa-kas' => [
        'category' => 'Villa Fotoğrafçılığı',
        'image' => '/assets/images/portfolio-1.jpg'
    ],
    'luks-otel-kalkan' => [
        'category' => 'Otel Fotoğrafçılığı',
        'image' => '/assets/images/portfolio-2.jpg'
    ],
    'butik-otel-fethiye' => [
        'category' => 'Otel Fotoğrafçılığı',
        'image' => '/assets/images/portfolio-3.jpg'
    ],
    'villa-kompleksi-bodrum' => [
        'category' => 'Emlak Fotoğrafçılığı',
        'image' => '/assets/images/portfolio-4.jpg'
    ],
    'modern-ofis-istanbul' => [
        'category' => 'Ticari Fotoğrafçılık',
        'image' => '/assets/images/portfolio-5.jpg'
    ],
    'restoran-ic-mekan-antalya' => [
        'category' => 'Restoran Fotoğrafçılığı',
        'image' => '/assets/images/portfolio-6.jpg'
    ]
];

// Add extras to projects
foreach ($portfolioProjects as &$project) {
    $extras = $projectExtras[$project['slug']] ?? [
        'category' => 'Mekan Fotoğrafçılığı',
        'image' => '/assets/images/portfolio-1.jpg'
    ];
    $project = array_merge($project, $extras);
    
    // Format location
    $location = '';
    if (isset($project['locations_district']['name'])) {
        $location = e($project['locations_district']['name']);
    }
    if (isset($project['locations_province']['name'])) {
        $location .= ($location ? ', ' : '') . e($project['locations_province']['name']);
    }
    $project['location'] = $location ?: 'Türkiye';
}

// If no projects from database, use mock data
if (empty($portfolioProjects)) {
    $portfolioProjects = [
        [
            'title' => 'Modern Villa Projesi - Kaş',
            'slug' => 'modern-villa-kas',
            'location' => 'Kaş, Antalya',
            'category' => 'Villa Fotoğrafçılığı',
            'year' => '2023',
            'image' => '/assets/images/portfolio-1.jpg',
            'description' => 'Kaş\'ta deniz manzaralı modern villa projesi için gerçekleştirdiğimiz profesyonel mekan fotoğrafçılığı çalışması.'
        ],
        [
            'title' => 'Lüks Otel İç Mekan - Kalkan',
            'slug' => 'luks-otel-kalkan',
            'location' => 'Kalkan, Antalya',
            'category' => 'Otel Fotoğrafçılığı',
            'year' => '2023',
            'image' => '/assets/images/portfolio-2.jpg',
            'description' => 'Kalkan\'da butik otel projesi için lobby, odalar ve ortak alanların profesyonel fotoğrafçılığı.'
        ],
        [
            'title' => 'Butik Otel Projesi - Fethiye',
            'slug' => 'butik-otel-fethiye',
            'location' => 'Fethiye, Muğla',
            'category' => 'Otel Fotoğrafçılığı',
            'year' => '2022',
            'image' => '/assets/images/portfolio-3.jpg',
            'description' => 'Fethiye\'de yer alan butik otelin tüm alanları için gerçekleştirilen kapsamlı fotoğraf çekimi.'
        ],
        [
            'title' => 'Villa Kompleksi - Bodrum',
            'slug' => 'villa-kompleksi-bodrum',
            'location' => 'Bodrum, Muğla',
            'category' => 'Emlak Fotoğrafçılığı',
            'year' => '2023',
            'image' => '/assets/images/portfolio-4.jpg',
            'description' => 'Bodrum\'da lüks villa kompleksi için pazarlama amaçlı profesyonel emlak fotoğrafçılığı.'
        ],
        [
            'title' => 'Modern Ofis Tasarımı - İstanbul',
            'slug' => 'modern-ofis-istanbul',
            'location' => 'İstanbul',
            'category' => 'Ticari Fotoğrafçılık',
            'year' => '2022',
            'image' => '/assets/images/portfolio-5.jpg',
            'description' => 'İstanbul\'da modern ofis binası için iç mekan ve mimari fotoğrafçılık çalışması.'
        ],
        [
            'title' => 'Restoran İç Mekan - Antalya',
            'slug' => 'restoran-ic-mekan-antalya',
            'location' => 'Antalya',
            'category' => 'Restoran Fotoğrafçılığı',
            'year' => '2023',
            'image' => '/assets/images/portfolio-6.jpg',
            'description' => 'Antalya\'da fine dining restoran için ambiyans ve iç mekan fotoğrafçılığı projesi.'
        ]
    ];
}

$pageTitle = 'Portfolyo | Mekan Fotoğrafçısı Çalışmalarımız';
$pageDescription = 'Antalya, Muğla ve Türkiye\'nin çeşitli bölgelerinde gerçekleştirdiğimiz profesyonel mekan fotoğrafçılığı projelerimizi inceleyin.';
$canonicalUrl = 'https://mekanfotografcisi.tr/portfolio';

$schemaMarkup = [
    '@context' => 'https://schema.org',
    '@type' => 'CreativeWork',
    'name' => 'Mekan Fotoğrafçısı Portfolio',
    'description' => e($pageDescription),
    'url' => e($canonicalUrl),
    'creator' => [
        '@type' => 'Organization',
        'name' => 'Mekan Fotoğrafçısı',
        'url' => 'https://mekanfotografcisi.tr'
    ],
    'workExample' => array_map(function($project) {
        return [
            '@type' => 'CreativeWork',
            'name' => e($project['title']),
            'description' => e($project['description']),
            'dateCreated' => e($project['year']),
            'locationCreated' => e($project['location'])
        ];
    }, $portfolioProjects)
];

include __DIR__ . '/../templates/page-header.php';
?>

<main class="seo-page">
    <!-- Breadcrumbs -->
    <section class="breadcrumbs">
        <div class="container">
            <a href="/">Ana Sayfa</a>
            <span>›</span>
            <strong>Portfolyo</strong>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="seo-hero">
        <div class="container">
            <h1>Portfolyomuz</h1>
            <p>Türkiye'nin çeşitli bölgelerinde gerçekleştirdiğimiz profesyonel mekan fotoğrafçılığı projelerimizi keşfedin. Her proje, mekanın kendine özgü karakterini yansıtan özel bir hikaye anlatıyor.</p>
            <div class="hero-buttons">
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
                <a href="/#iletisim" class="btn btn-outline">Çekim Planla</a>
            </div>
        </div>
    </section>

    <!-- Portfolio Filter -->
    <section class="portfolio-filter-section">
        <div class="container">
            <div class="portfolio-filter">
                <button class="filter-btn active" data-filter="all">Tümü</button>
                <button class="filter-btn" data-filter="villa">Villa</button>
                <button class="filter-btn" data-filter="otel">Otel</button>
                <button class="filter-btn" data-filter="emlak">Emlak</button>
                <button class="filter-btn" data-filter="ticari">Ticari</button>
                <button class="filter-btn" data-filter="restoran">Restoran</button>
            </div>
        </div>
    </section>

    <!-- Portfolio Grid -->
    <section class="grid-section">
        <div class="container">
            <div class="portfolio-grid">
                <?php foreach ($portfolioProjects as $project): ?>
                <div class="portfolio-item" data-category="<?= e(strtolower(explode(' ', $project['category'])[0])) ?>">
                    <div class="portfolio-image">
                        <img src="<?= e($project['image']) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
                        <div class="portfolio-overlay">
                            <div class="portfolio-info">
                                <h3><?= e($project['title']) ?></h3>
                                <p class="portfolio-location">
                                    <span>📍</span>
                                    <?= e($project['location']) ?> • <?= e($project['year']) ?>
                                </p>
                                <p class="portfolio-category"><?= e($project['category']) ?></p>
                                <a href="/portfolio/<?= e($project['slug']) ?>" class="btn btn-primary btn-sm">Detayları İncele →</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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
                    <span class="stat-number">%100</span>
                    <div class="stat-label">Müşteri Memnuniyeti</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">10+</span>
                    <div class="stat-label">Yıl Deneyim</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section class="process-section alt-bg">
        <div class="container">
            <h2 class="section-title center">Çalışma Sürecimiz</h2>
            <p class="section-subtitle">Her projede izlediğimiz profesyonel yaklaşım</p>
            
            <div class="process-grid">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <h3>Keşif ve Planlama</h3>
                    <p>Projenizi detaylı olarak inceleyip, mekanın özelliklerini analiz ederek çekim planını oluşturuyoruz.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <h3>Profesyonel Çekim</h3>
                    <p>Uzman ekibimiz ve son teknoloji ekipmanlarımızla, mekanınızın en iyi açılarını yakalıyoruz.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <h3>Post-Prodüksiyon</h3>
                    <p>Fotoğraflarınızı profesyonel yazılımlarla düzenleyip, kalite kontrolünden geçiriyoruz.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">4</div>
                    <h3>Hızlı Teslimat</h3>
                    <p>3-5 iş günü içinde yüksek çözünürlüklü fotoğraflarınızı dijital ortamda teslim ediyoruz.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Projeniz de Portfolyomuzda Yer Alsın!</h2>
            <p>Mekanınızın profesyonel fotoğrafları için bugün bizimle iletişime geçin.</p>
            <div class="cta-buttons">
                <a href="tel:+905074677502" class="btn btn-outline">📞 +90 507 467 75 02</a>
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Portfolio filtering
    const filterBtns = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-filter');
            
            // Filter portfolio items
            portfolioItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
