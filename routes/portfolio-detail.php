<?php
/**
 * Portfolio Detail Page
 * /portfolio/{slug}
 */

require_once __DIR__ . '/../includes/supabase.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/markdown.php';

$projectSlug = sanitizeSlug($_GET['slug'] ?? '');

// Get portfolio project from database
$projectResults = $supabase->select('portfolio_projects', [
    'slug' => 'eq.' . $projectSlug,
    'is_published' => 'eq.true',
    'select' => 'id,title,slug,description,year,locations_province(name,slug),locations_district(name,slug)'
]);

$project = $projectResults[0] ?? null;

if (!$project) {
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/../404.html';
    exit;
}

// Extended content for portfolio projects
$projectContent = [
    'modern-villa-kas' => [
        'image' => '/assets/images/portfolio-1.jpg',
        'category' => 'Villa Fotoğrafçılığı',
        'client' => 'Özel Villa Projesi',
        'duration' => '2 Gün',
        'photos_count' => '45 Fotoğraf',
        'content' => '
## Modern Villa Projesi - Kaş

Kaş\'ın eşsiz manzarasına sahip bu modern villa projesi, Akdeniz mimarisinin çağdaş yorumunu sergiliyor. Deniz manzaralı infinity havuz, geniş teraslar ve minimalist iç mekan tasarımı ile öne çıkan bu projede, mekanın doğal güzelliklerini vurgulayan bir fotoğraf çekimi gerçekleştirdik.

### Proje Detayları

- **Lokasyon**: Kaş, Antalya - Deniz manzaralı yamaç
- **Mimari Stil**: Modern Akdeniz mimarisi
- **Özel Özellikler**: Infinity havuz, panoramik manzara, minimalist tasarım
- **Çekim Süresi**: 2 gün (gündüz ve gece çekimleri)

### Çekim Yaklaşımımız

Bu projede Kaş\'ın eşsiz ışık koşullarından maksimum fayda sağladık:

- **Sabah Çekimleri**: Yumuşak ışıkta dış mekan ve havuz alanları
- **Öğle Çekimleri**: İç mekanların doğal aydınlatması
- **Gün Batımı**: Manzara ve infinity havuzun dramatik görünümü
- **Gece Çekimleri**: Aydınlatma tasarımı ve ambiyans

### Teknik Detaylar

- **Drone Çekimleri**: Villanın konumunu ve manzarayı vurgulayan havadan görünümler
- **Geniş Açı Objektifler**: Mekanların ferahlığını gösteren iç mekan çekimleri
- **HDR Teknikleri**: Yüksek kontrast durumlarında detay korunması
- **Perspektif Düzeltme**: Mimari çizgilerin doğru görünümü

### Sonuç

Bu proje, modern villa fotoğrafçılığında Kaş\'ın doğal güzelliklerini mimari tasarımla harmanlama konusundaki uzmanlığımızı gösteriyor. Elde edilen fotoğraflar, villanın pazarlama sürecinde büyük başarı sağladı.
        ',
        'gallery' => [
            '/assets/images/portfolio-1.jpg',
            '/assets/images/portfolio-2.jpg',
            '/assets/images/portfolio-3.jpg',
            '/assets/images/hero-bg.jpg'
        ]
    ],
    'luks-otel-kalkan' => [
        'image' => '/assets/images/portfolio-2.jpg',
        'category' => 'Otel Fotoğrafçılığı',
        'client' => 'Butik Otel Kalkan',
        'duration' => '3 Gün',
        'photos_count' => '80 Fotoğraf',
        'content' => '
## Lüks Otel İç Mekan - Kalkan

Kalkan\'ın prestijli konumunda yer alan bu butik otel projesi, Akdeniz\'in eşsiz manzarasını lüks konaklama deneyimi ile buluşturuyor. Otel\'in tüm alanları için gerçekleştirdiğimiz kapsamlı fotoğraf çekimi, mekanın atmosferini ve konfor seviyesini en iyi şekilde yansıtıyor.

### Proje Kapsamı

- **Lobby ve Resepsiyon**: Karşılama alanının sıcak atmosferi
- **Otel Odaları**: Farklı oda tiplerinin konfor vurgusu
- **Restoran ve Bar**: Gastronomi alanlarının ambiyansı
- **Havuz ve Terras**: Dış mekan yaşam alanları
- **Spa ve Wellness**: Huzur ve rahatlama mekanları

### Çekim Stratejisi

Otelin 24 saat yaşayan atmosferini yakalamak için:

- **Gündüz Çekimleri**: Doğal ışıkta mekan fotoğrafları
- **Akşam Çekimleri**: Restoran ve bar\'ın canlı atmosferi
- **Gece Çekimleri**: Romantik aydınlatma ve manzara
- **Detay Çekimleri**: Dekorasyon ve tasarım öğeleri

### Özel Teknikler

- **Ambient Light Mixing**: Doğal ve yapay ışık dengesi
- **Lifestyle Photography**: Konukların deneyim anları
- **Architectural Details**: Tasarım öğelerinin vurgulanması
- **Panoramic Views**: Kalkan manzarasının entegrasyonu
        ',
        'gallery' => [
            '/assets/images/portfolio-2.jpg',
            '/assets/images/portfolio-1.jpg',
            '/assets/images/portfolio-4.jpg',
            '/assets/images/otel-restoran.jpg'
        ]
    ],
    'butik-otel-fethiye' => [
        'image' => '/assets/images/portfolio-3.jpg',
        'category' => 'Otel Fotoğrafçılığı',
        'client' => 'Fethiye Butik Otel',
        'duration' => '2 Gün',
        'photos_count' => '60 Fotoğraf',
        'content' => '
## Butik Otel Projesi - Fethiye

Fethiye\'nin doğal güzellikleri arasında yer alan bu butik otel, geleneksel Akdeniz mimarisi ile modern konfor anlayışını harmanlıyor. Otelin samimi atmosferi ve özel tasarım detaylarını öne çıkaran fotoğraf çekimi gerçekleştirdik.

### Proje Özellikleri

- **Konum**: Fethiye merkez, denize yakın
- **Konsept**: Butik otel deneyimi
- **Özellik**: Geleneksel-modern karışımı tasarım
- **Hedef**: Rezervasyon artışı için pazarlama materyali

### Çekim Alanları

- **Giriş ve Lobby**: Karşılama alanının sıcak atmosferi
- **Otel Odaları**: Konfor ve estetik vurgusu
- **Kahvaltı Salonu**: Sabah ışığında doğal ambiyans
- **Bahçe ve Terras**: Dış mekan dinlenme alanları

Bu proje, butik otel işletmeciliğinde fotoğrafın pazarlama gücünü gösteren başarılı bir örnek oldu.
        ',
        'gallery' => [
            '/assets/images/portfolio-3.jpg',
            '/assets/images/portfolio-2.jpg',
            '/assets/images/portfolio-1.jpg'
        ]
    ],
    'villa-kompleksi-bodrum' => [
        'image' => '/assets/images/portfolio-4.jpg',
        'category' => 'Emlak Fotoğrafçılığı',
        'client' => 'Bodrum Villa Kompleksi',
        'duration' => '3 Gün',
        'photos_count' => '90 Fotoğraf',
        'content' => '
## Villa Kompleksi - Bodrum

Bodrum\'un prestijli bölgesinde yer alan lüks villa kompleksi için gerçekleştirdiğimiz emlak fotoğrafçılığı projesi. Satış sürecini desteklemek amacıyla her villanın kendine özgü karakterini yansıtan fotoğraflar ürettik.

### Proje Kapsamı

- **Villa Tipleri**: 3+1, 4+1 ve 5+1 villa seçenekleri
- **Ortak Alanlar**: Havuz, peyzaj, sosyal tesisler
- **Mimari Detaylar**: Taş işçiliği, ahşap detaylar
- **Manzara**: Bodrum Kalesi ve deniz manzarası

### Emlak Fotoğrafçılığı Yaklaşımı

- **Geniş Açı Çekimler**: Mekanların ferahlığını gösterme
- **Detay Fotoğrafları**: Kaliteli malzeme ve işçilik vurgusu
- **Yaşam Alanları**: Potansiyel alıcıların kendilerini görebileceği sahneler
- **Dış Mekan**: Peyzaj ve çevre düzenlemesi

Proje sonucunda villa satışlarında %40 artış kaydedildi.
        ',
        'gallery' => [
            '/assets/images/portfolio-4.jpg',
            '/assets/images/portfolio-1.jpg',
            '/assets/images/portfolio-2.jpg'
        ]
    ],
    'modern-ofis-istanbul' => [
        'image' => '/assets/images/portfolio-5.jpg',
        'category' => 'Ticari Fotoğrafçılık',
        'client' => 'İstanbul Ofis Projesi',
        'duration' => '1 Gün',
        'photos_count' => '40 Fotoğraf',
        'content' => '
## Modern Ofis Tasarımı - İstanbul

İstanbul\'da modern ofis binası için gerçekleştirdiğimiz ticari mekan fotoğrafçılığı projesi. Çalışma alanlarının fonksiyonelliği ve estetik tasarımını vurgulayan fotoğraflar ürettik.

### Çekim Alanları

- **Açık Ofis**: Modern çalışma alanları
- **Toplantı Odaları**: Profesyonel görüşme mekanları
- **Dinlenme Alanları**: Sosyal alanlar ve kafeterya
- **Resepsiyon**: Karşılama ve bekleme alanı

### Ticari Fotoğrafçılık Teknikleri

- **Profesyonel Aydınlatma**: Çalışma ortamının doğru yansıtılması
- **İnsan Faktörü**: Çalışanların doğal halleri
- **Teknoloji Vurgusu**: Modern ekipman ve altyapı
- **Marka Kimliği**: Kurumsal renk ve tasarım öğeleri

Bu çekim, şirketin kurumsal kimlik çalışmalarında kullanıldı.
        ',
        'gallery' => [
            '/assets/images/portfolio-5.jpg',
            '/assets/images/portfolio-1.jpg',
            '/assets/images/portfolio-3.jpg'
        ]
    ],
    'restoran-ic-mekan-antalya' => [
        'image' => '/assets/images/portfolio-6.jpg',
        'category' => 'Restoran Fotoğrafçılığı',
        'client' => 'Antalya Fine Dining',
        'duration' => '1 Gün',
        'photos_count' => '50 Fotoğraf',
        'content' => '
## Restoran İç Mekan - Antalya

Antalya\'da fine dining konseptinde hizmet veren restoran için gerçekleştirdiğimiz iç mekan fotoğrafçılığı projesi. Restoranın şık atmosferi ve gastronomi deneyimini yansıtan fotoğraflar ürettik.

### Çekim Konsepti

- **Ambiyans**: Romantik ve şık atmosfer
- **Masa Düzeni**: Özel servis sunumu
- **Mutfak**: Açık mutfak konsepti
- **Bar**: İçecek sunumu ve atmosfer

### Restoran Fotoğrafçılığı Detayları

- **Işık Oyunu**: Mum ışığı ve ambient aydınlatma
- **Yemek Sunumu**: Gastronomi sanatının görsel yansıması
- **Servis Detayları**: Profesyonel sunum teknikleri
- **Müşteri Deneyimi**: Yemek deneyiminin görsel hikayesi

Fotoğraflar, restoranın sosyal medya ve pazarlama materyallerinde kullanıldı.
        ',
        'gallery' => [
            '/assets/images/portfolio-6.jpg',
            '/assets/images/otel-restoran.jpg',
            '/assets/images/portfolio-2.jpg'
        ]
    ]
];

// Add default content for other projects
$defaultContent = [
    'image' => '/assets/images/portfolio-1.jpg',
    'category' => 'Mekan Fotoğrafçılığı',
    'client' => 'Özel Proje',
    'duration' => '1-2 Gün',
    'photos_count' => '30+ Fotoğraf',
    'content' => '
## ' . $project['title'] . '

Bu projede profesyonel mekan fotoğrafçılığı hizmetlerimizle, mekanın en iyi yönlerini öne çıkaran kaliteli fotoğraflar ürettik.

### Proje Hakkında

' . ($project['description'] ?: 'Profesyonel mekan fotoğrafçılığı projesi.') . '

### Çalışma Sürecimiz

1. **Ön İnceleme**: Mekanın özelliklerini analiz ettik
2. **Çekim Planlaması**: En uygun açılar ve zamanları belirledik  
3. **Profesyonel Çekim**: Modern ekipmanlarla fotoğraf çekimi
4. **Post-Prodüksiyon**: Kalite kontrolü ve düzenleme

### Teknik Yaklaşım

- **Profesyonel Ekipman**: Son teknoloji kameralar ve objektifler
- **Işık Optimizasyonu**: Doğal ve yapay ışık dengesi
- **Kompozisyon**: Mekanın en güzel açılarının yakalanması
- **Detay Odaklı**: Özel tasarım öğelerinin vurgulanması

Bu proje, mekan fotoğrafçılığındaki deneyimimizi ve kalite standardımızı göstermektedir.
    ',
    'gallery' => [
        '/assets/images/portfolio-1.jpg',
        '/assets/images/portfolio-2.jpg',
        '/assets/images/portfolio-3.jpg'
    ]
];

$projectData = array_merge($project, $projectContent[$projectSlug] ?? $defaultContent);

// Get gallery images from database if gallery_media_ids exists
if (!empty($project['gallery_media_ids']) && is_array($project['gallery_media_ids'])) {
    $galleryImages = [];
    foreach ($project['gallery_media_ids'] as $mediaId) {
        $mediaResults = $supabase->select('media', [
            'id' => 'eq.' . $mediaId,
            'select' => 'public_url,alt'
        ]);
        if (!empty($mediaResults)) {
            $galleryImages[] = $mediaResults[0]['public_url'];
        }
    }
    // If we got images from database, use them instead of default gallery
    if (!empty($galleryImages)) {
        $projectData['gallery'] = $galleryImages;
    }
}

// Format location
$location = '';
if (isset($project['locations_district']['name'])) {
    $location = $project['locations_district']['name'];
}
if (isset($project['locations_province']['name'])) {
    $location .= ($location ? ', ' : '') . $project['locations_province']['name'];
}
$projectData['location'] = $location ?: 'Türkiye';

// Get other portfolio projects for sidebar
$otherProjects = $supabase->select('portfolio_projects', [
    'is_published' => 'eq.true',
    'select' => 'title,slug',
    'limit' => 6
]);

$pageTitle = e($project['title']) . ' | Mekan Fotoğrafçısı Portfolio';
$pageDescription = e($project['description'] ?: ($project['title'] . ' projesi detayları ve fotoğrafları.'));
$canonicalUrl = 'https://mekanfotografcisi.tr/portfolio/' . e($projectSlug);

$schemaMarkup = [
    '@context' => 'https://schema.org',
    '@type' => 'CreativeWork',
    'name' => e($project['title']),
    'description' => e($pageDescription),
    'url' => e($canonicalUrl),
    'dateCreated' => e($project['year']),
    'locationCreated' => e($projectData['location']),
    'creator' => [
        '@type' => 'Organization',
        'name' => 'Mekan Fotoğrafçısı',
        'url' => 'https://mekanfotografcisi.tr'
    ]
];

include __DIR__ . '/../templates/page-header.php';
?>

<main class="seo-page">
    <!-- Breadcrumbs -->
    <section class="breadcrumbs">
        <div class="container">
            <a href="/">Ana Sayfa</a><span>›</span>
            <a href="/portfolio">Portfolio</a><span>›</span>
            <strong><?= e($project['title']) ?></strong>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="seo-hero">
        <div class="container">
            <h1><?= e($project['title']) ?></h1>
            <p><?= e($project['description'] ?: 'Profesyonel mekan fotoğrafçılığı projesi detayları.') ?></p>
            <div class="hero-buttons">
                <a href="/#iletisim" class="btn btn-primary">Benzer Proje İçin Teklif Al</a>
                <a href="/portfolio" class="btn btn-outline">Tüm Portfolio</a>
            </div>
        </div>
    </section>

    <!-- Project Info Card -->
    <section class="content-section alt-bg">
        <div class="container">
            <div class="project-info-card">
                <h3>Proje Bilgileri</h3>
                <div class="grid-3">
                    <p>
                        <strong>Kategori:</strong>
                        <span><?= e($projectData['category']) ?></span>
                    </p>
                    <p>
                        <strong>Lokasyon:</strong>
                        <span><?= e($projectData['location']) ?></span>
                    </p>
                    <p>
                        <strong>Yıl:</strong>
                        <span><?= e($project['year']) ?></span>
                    </p>
                    <p>
                        <strong>Müşteri:</strong>
                        <span><?= e($projectData['client']) ?></span>
                    </p>
                    <p>
                        <strong>Çekim Süresi:</strong>
                        <span><?= e($projectData['duration']) ?></span>
                    </p>
                    <p>
                        <strong>Teslimat:</strong>
                        <span><?= e($projectData['photos_count']) ?></span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content-section">
        <div class="container">
            <div class="content-wrapper">
                <div class="main-content">
                    <?= markdownToHtml($projectData['content'] ?? 'İçerik hazırlanıyor...') ?>
                </div>
                
                <div class="sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-card-image">
                            <img src="<?= e($projectData['image'] ?? '/assets/images/portfolio-1.jpg') ?>" alt="<?= e($project['title']) ?>" loading="lazy">
                        </div>
                        <div class="sidebar-card-content">
                            <h3><?= e($project['title']) ?></h3>
                            <p><?= e($project['description'] ?: 'Profesyonel mekan fotoğrafçılığı projesi') ?></p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <h3>Benzer Proje İstiyorsunuz?</h3>
                        <p>Bu tür projeler için özel fiyat teklifi alın.</p>
                        <a href="/#iletisim" class="btn btn-outline btn-block">Teklif Al</a>
                        <a href="tel:+905074677502" class="btn btn-outline btn-block">📞 +90 507 467 75 02</a>
                    </div>

                    <?php if (!empty($otherProjects)): ?>
                    <div class="sidebar-card">
                        <div class="sidebar-card-content">
                            <h3>Diğer Projelerimiz</h3>
                            <ul>
                                <?php foreach ($otherProjects as $otherProject): ?>
                                    <?php if ($otherProject['slug'] !== $projectSlug): ?>
                                    <li><a href="/portfolio/<?= e($otherProject['slug']) ?>"><?= e($otherProject['title']) ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Project Gallery -->
    <?php if (!empty($projectData['gallery'])): ?>
    <section class="project-gallery">
        <div class="container">
            <h2 class="section-title center">Proje Fotoğrafları</h2>
            <p class="section-subtitle center" style="margin-bottom: 48px;">Proje fotoğraflarını büyütmek için tıklayın</p>
            <div class="gallery-grid">
                <?php foreach ($projectData['gallery'] as $index => $image): ?>
                <div class="gallery-item" onclick="openLightbox(<?= $index ?>)">
                    <img src="<?= e($image) ?>" alt="<?= e($project['title']) ?> - Fotoğraf <?= $index + 1 ?>" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="gallery-icon">🔍</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <span class="lightbox-prev" onclick="changeImage(-1)">&#10094;</span>
        <span class="lightbox-next" onclick="changeImage(1)">&#10095;</span>
        <div class="lightbox-content">
            <img id="lightbox-image" src="" alt="">
            <div class="lightbox-caption">
                <span id="lightbox-counter"></span>
                <span id="lightbox-title"><?= e($project['title']) ?></span>
            </div>
        </div>
    </div>

    <script>
    const galleryImages = <?= json_encode($projectData['gallery']) ?>;
    let currentImageIndex = 0;

    function openLightbox(index) {
        currentImageIndex = index;
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxCounter = document.getElementById('lightbox-counter');
        
        lightboxImage.src = galleryImages[index];
        lightboxCounter.textContent = (index + 1) + ' / ' + galleryImages.length;
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function changeImage(direction) {
        currentImageIndex += direction;
        
        if (currentImageIndex < 0) {
            currentImageIndex = galleryImages.length - 1;
        } else if (currentImageIndex >= galleryImages.length) {
            currentImageIndex = 0;
        }
        
        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxCounter = document.getElementById('lightbox-counter');
        
        lightboxImage.src = galleryImages[currentImageIndex];
        lightboxCounter.textContent = (currentImageIndex + 1) + ' / ' + galleryImages.length;
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const lightbox = document.getElementById('lightbox');
        if (lightbox.style.display === 'flex') {
            if (e.key === 'Escape') {
                closeLightbox();
            } else if (e.key === 'ArrowLeft') {
                changeImage(-1);
            } else if (e.key === 'ArrowRight') {
                changeImage(1);
            }
        }
    });

    // Close on outside click
    document.getElementById('lightbox').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLightbox();
        }
    });
    </script>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Siz de Böyle Bir Proje İstiyor musunuz?</h2>
            <p>Benzer kalitede profesyonel mekan fotoğrafçılığı hizmetleri için bizimle iletişime geçin.</p>
            <div class="cta-buttons">
                <a href="tel:+905074677502" class="btn btn-outline">📞 +90 507 467 75 02</a>
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>