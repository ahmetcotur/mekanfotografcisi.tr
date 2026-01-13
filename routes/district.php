<?php
/**
 * District Detail Page
 * /locations/{province}/{district}
 */

require_once __DIR__ . '/../includes/supabase.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/markdown.php';

$provinceSlug = sanitizeSlug($_GET['province'] ?? '');
$districtSlug = sanitizeSlug($_GET['district'] ?? '');

// Redirect old slug formats to new ones (SEO-friendly redirects)
$slugRedirects = [
    'i-stanbul' => 'istanbul',
    'i-zmir' => 'izmir'
];

if (isset($slugRedirects[$provinceSlug])) {
    $newSlug = $slugRedirects[$provinceSlug];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: /locations/' . $newSlug . '/' . $districtSlug);
    exit;
}

// First, check if there's a published SEO page for this route
$seoPageSlug = '/locations/' . $provinceSlug . '/' . $districtSlug;
$seoPages = $supabase->select('seo_pages', [
    'slug' => $seoPageSlug,
    'published' => true,
    'type' => 'district'
]);

$seoPage = !empty($seoPages) ? $seoPages[0] : null;

// Get province first
$provinceResults = $supabase->select('locations_province', [
    'slug' => $provinceSlug,
    'select' => 'id,name,slug'
]);

$province = $provinceResults[0] ?? null;

if (!$province) {
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/../404.html';
    exit;
}

// Get district
$districtResults = $supabase->select('locations_district', [
    'province_id' => $province['id'],
    'slug' => $districtSlug,
    'is_active' => true,
    'select' => 'id,name,slug,local_notes'
]);

$district = $districtResults[0] ?? null;

if (!$district) {
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/../404.html';
    exit;
}

// If SEO page exists, use its content
if ($seoPage) {
    $district['seo_title'] = $seoPage['title'];
    $district['seo_h1'] = $seoPage['h1'];
    $district['seo_meta_description'] = $seoPage['meta_description'];
    $district['seo_content_md'] = $seoPage['content_md'];
    $district['use_seo_content'] = true;
} else {
    $district['use_seo_content'] = false;
}

// Extended content for districts
$districtContent = [
    'kas' => [
        'description' => 'Kaş\'ta butik oteller ve lüks villalar için profesyonel mekan fotoğrafçılığı hizmetleri.',
        'image' => '/assets/images/portfolio-1.jpg',
        'specialty' => 'Butik Oteller ve Lüks Villalar',
        'content' => '
## Kaş Mekan Fotoğrafçısı

Kaş, Antalya\'nın en özel tatil beldelerinden biri. Sakin atmosferi, butik otelleri ve lüks villaları ile öne çıkan bu güzel kasabada, mekanların eşsiz karakterini yansıtan profesyonel fotoğraflar üretiyoruz.

### Kaş\'ta Öne Çıkan Projelerimiz

- **Butik Otel Fotoğrafçılığı**: Kaş\'ın samimi atmosferini yansıtan otel çekimleri
- **Lüks Villa Çekimleri**: Deniz manzaralı özel konutların pazarlama fotoğrafları
- **Restoran ve Cafe**: Kaş\'ın ünlü gastronomi mekanları
- **Pansiyon ve B&B**: Aile işletmesi konaklama tesisleri

### Kaş\'ın Özel Atmosferi

Kaş\'ın kendine özgü özellikleri fotoğraflarımıza yansıyor:

- **Akdeniz Mimarisi**: Geleneksel taş evler ve modern tasarım
- **Doğal Liman**: Tekne ve deniz manzarası entegrasyonu
- **Antik Kalıntılar**: Tarihi doku ve modern yaşam uyumu
- **Bougainville Çiçekleri**: Rengarenk doğal dekorasyon

### Çekim Zamanlaması

Kaş\'ta en ideal çekim zamanları:

- **Sabah Erken**: Sakin sokaklar ve yumuşak ışık
- **Öğle Sonrası**: Denizin en mavi olduğu saatler
- **Gün Batımı**: Romantik atmosfer ve sıcak tonlar
- **Mavi Saat**: Gece aydınlatması ve ambiyans
        ',
        'faq' => [
            [
                'question' => 'Kaş\'ta çekim yapmak için ne kadar süre gerekiyor?',
                'answer' => 'Mekanın büyüklüğüne ve çekim türüne göre değişmekle birlikte, Kaş\'ta ortalama 2-4 saat sürmektedir. Lüks villa çekimleri için özel planlama yapıyoruz.'
            ],
            [
                'question' => 'Kaş\'ta hangi saatlerde çekim yapıyorsunuz?',
                'answer' => 'Kaş\'ın özel ışık koşullarını göz önünde bulundurarak, sabah erken saatlerden gün batımına kadar çekim yapabiliyoruz. Gece çekimleri için de özel ekipmanlarımız mevcuttur.'
            ],
            [
                'question' => 'Fotoğrafları ne kadar sürede teslim ediyorsunuz?',
                'answer' => 'Kaş\'taki çekimlerimiz sonrası 3-5 iş günü içinde düzenlenmiş fotoğraflarınızı dijital ortamda teslim ediyoruz. Acil durumlar için 24 saat içinde teslimat seçeneğimiz de bulunmaktadır.'
            ]
        ]
    ],
    'kalkan' => [
        'description' => 'Kalkan\'da lüks villalar ve butik oteller için özel fotoğrafçılık hizmetleri.',
        'image' => '/assets/images/portfolio-2.jpg',
        'specialty' => 'Lüks Villa Fotoğrafçılığı',
        'content' => '
## Kalkan Mekan Fotoğrafçısı

Kalkan, Türkiye\'nin en prestijli tatil beldelerinden biri. Lüks villaları, butik otelleri ve eşsiz manzaraları ile öne çıkan bu özel destinasyonda, üst segment mekanlar için profesyonel fotoğrafçılık hizmetleri sunuyoruz.

### Kalkan\'da Uzmanlık Alanlarımız

- **Lüks Villa Fotoğrafçılığı**: Infinity havuzlu, deniz manzaralı villalar
- **Butik Otel Çekimleri**: Özel hizmet veren küçük ölçekli tesisler
- **Fine Dining Restoranlar**: Gastronomi ve ambiyans fotoğrafları
- **Beach Club ve Marina**: Sahil tesisleri özel çekimleri

### Kalkan\'ın Prestijli Atmosferi

- **Infinity Havuzlar**: Denizle bütünleşen havuz tasarımları
- **Panoramik Manzaralar**: 180 derece Akdeniz görünümü
- **Lüks İç Mekanlar**: Modern tasarım ve konfor
- **Özel Bahçeler**: Akdeniz peyzaj mimarisi

### Premium Hizmet Yaklaşımı

Kalkan\'ın lüks segmentine uygun özel hizmetler:

- **VIP Çekim Planlaması**: Misafir rahatsızlığı olmadan çekim
- **Drone Çekimleri**: Havadan villa ve çevre görünümü
- **Gece Çekimleri**: Aydınlatma ve ambiyans vurgusu
- **Lifestyle Fotoğrafları**: Yaşam tarzını yansıtan kompozisyonlar
        ',
        'faq' => [
            [
                'question' => 'Kalkan\'da villa çekimleri ne kadar sürer?',
                'answer' => 'Kalkan\'daki lüks villa çekimleri, mekanın büyüklüğüne ve istenen detay seviyesine göre genellikle yarım gün ile tam gün arasında değişir.'
            ],
            [
                'question' => 'Kalkan\'da drone çekimi yapıyor musunuz?',
                'answer' => 'Evet, Kalkan\'ın eşsiz manzaralarını ve villaların konumunu en iyi şekilde göstermek için profesyonel drone çekimleri yapıyoruz.'
            ]
        ]
    ],
    'bodrum' => [
        'description' => 'Bodrum\'da lüks villalar, butik oteller ve marina tesisleri için profesyonel fotoğrafçılık.',
        'image' => '/assets/images/portfolio-3.jpg',
        'specialty' => 'Lüks Villalar ve Marina',
        'content' => '
## Bodrum Mekan Fotoğrafçısı

Bodrum, Türkiye\'nin en kozmopolit tatil destinasyonu. Lüks villaları, marina tesisleri ve gece hayatı ile ünlü bu özel yarımadada, prestijli mekanlar için profesyonel fotoğrafçılık hizmetleri sunuyoruz.

### Bodrum\'da Hizmet Alanlarımız

- **Lüks Villa Kompleksleri**: Özel konut projelerinin pazarlama fotoğrafları
- **Marina ve Yat Kulüpleri**: Denizcilik tesisleri fotoğrafçılığı
- **Beach Club ve Restoran**: Sahil eğlence mekanları
- **Butik Otel ve Resort**: Konaklama tesisleri çekimleri
        '
    ],
    'kemer' => [
        'description' => 'Kemer\'de otel, resort ve tatil köyleri için profesyonel mekan fotoğrafçılığı hizmetleri.',
        'image' => '/assets/images/portfolio-1.jpg',
        'specialty' => 'Otel ve Resort Fotoğrafçılığı',
        'content' => '
## Kemer Mekan Fotoğrafçısı

Kemer, Antalya\'nın en popüler tatil destinasyonlarından biri. Büyük ölçekli otelleri, resort tesisleri ve doğal güzellikleri ile öne çıkan bu bölgede, turizm sektörüne özel fotoğrafçılık hizmetleri sunuyoruz.

### Kemer\'de Uzmanlık Alanlarımız

- **Otel ve Resort Çekimleri**: Büyük ölçekli konaklama tesisleri
- **Spa ve Wellness Merkezleri**: Dinlenme ve sağlık tesisleri
- **Restoran ve Bar**: Yeme-içme mekanları fotoğrafçılığı
- **Açık Hava Alanları**: Havuz, plaj ve peyzaj çekimleri
- **Olympos ve Çıralı**: Butik otel ve pansiyon çekimleri

### Kemer\'in Turizm Potansiyeli

- **5 Yıldızlı Oteller**: Geniş tesislerin kapsamlı çekimleri
- **All-Inclusive Resortlar**: Tüm tesis alanlarının fotoğraflanması
- **Doğal Güzellikler**: Olympos ve Yanartaş entegrasyonu
- **Deniz ve Plaj**: Sahil tesisleri özel çekimleri
        '
    ],
    'marmaris' => [
        'description' => 'Marmaris\'te otel, resort, marina ve tatil köyleri için profesyonel fotoğrafçılık.',
        'image' => '/assets/images/portfolio-2.jpg',
        'specialty' => 'Otel ve Resort Fotoğrafçılığı',
        'content' => '
## Marmaris Mekan Fotoğrafçısı

Marmaris, Muğla\'nın en büyük tatil merkezlerinden biri. Geniş otel yelpazesi, marina tesisleri ve canlı gece hayatı ile öne çıkan bu bölgede, turizm sektörüne özel fotoğrafçılık hizmetleri sunuyoruz.

### Marmaris\'te Hizmet Alanlarımız

- **Büyük Ölçekli Oteller**: Resort ve tatil köyü çekimleri
- **Marina Tesisleri**: Yat limanı ve denizcilik tesisleri
- **İçmeler Tatil Köyü**: Özel sahil tesisleri
- **Restoran ve Eğlence Mekanları**: Gece hayatı ve gastronomi
- **Spa ve Wellness**: Dinlenme tesisleri fotoğrafçılığı

### Marmaris\'in Özel Özellikleri

- **Geniş Marina**: Yat ve tekne fotoğrafçılığı
- **Uzun Sahil Şeridi**: Plaj ve sahil tesisleri
- **Dağ Manzarası**: Doğal peyzaj entegrasyonu
- **Canlı Atmosfer**: Gece ve gündüz çekimleri
        '
    ],
    'fethiye' => [
        'description' => 'Fethiye\'de villa, butik otel ve marina tesisleri için profesyonel mekan fotoğrafçılığı.',
        'image' => '/assets/images/portfolio-1.jpg',
        'specialty' => 'Villa Fotoğrafçılığı',
        'content' => '
## Fethiye Mekan Fotoğrafçısı

Fethiye, Muğla\'nın en güzel körfezlerinden biri. Doğal güzellikleri, butik otelleri ve lüks villaları ile öne çıkan bu özel bölgede, mekanların eşsiz karakterini yansıtan profesyonel fotoğraflar üretiyoruz.

### Fethiye\'de Öne Çıkan Projelerimiz

- **Lüks Villa Çekimleri**: Ölüdeniz ve Göcek bölgesi villaları
- **Butik Otel Fotoğrafçılığı**: Özel hizmet veren küçük tesisler
- **Marina ve Yat Tesisleri**: Göcek Marina özel çekimleri
- **Ölüdeniz Lagünü**: Doğal güzellik entegrasyonlu çekimler
- **Restoran ve Cafe**: Sahil ve marina restoranları

### Fethiye\'nin Özel Atmosferi

- **Ölüdeniz**: Dünyaca ünlü lagün manzarası
- **Göcek Marina**: Lüks yat ve tekne fotoğrafçılığı
- **Saklı Koylar**: Özel lokasyon çekimleri
- **Dağ Manzarası**: Babadağ ve çevresi panoramik görünümler
        '
    ],
    'datca' => [
        'description' => 'Datça\'da butik oteller, pansiyonlar ve doğal güzellikler için profesyonel fotoğrafçılık.',
        'image' => '/assets/images/portfolio-2.jpg',
        'specialty' => 'Butik Otel Fotoğrafçılığı',
        'content' => '
## Datça Mekan Fotoğrafçısı

Datça, Muğla\'nın en sakin ve özel tatil beldelerinden biri. Butik otelleri, pansiyonları ve bozulmamış doğası ile öne çıkan bu güzel yarımadada, mekanların özel karakterini yansıtan fotoğraflar üretiyoruz.

### Datça\'da Hizmet Alanlarımız

- **Butik Otel Çekimleri**: Özel hizmet veren küçük ölçekli tesisler
- **Pansiyon ve B&B**: Aile işletmesi konaklama tesisleri
- **Restoran ve Cafe**: Sahil ve merkez mekanları
- **Bozburun**: Özel sahil tesisleri
- **Doğal Güzellikler**: Koy ve plaj entegrasyonlu çekimler

### Datça\'nın Özel Karakteri

- **Sakin Atmosfer**: Huzurlu tatil beldesi
- **Bozulmamış Doğa**: Temiz deniz ve koylar
- **Geleneksel Mimari**: Taş evler ve butik tasarım
- **Yerel Lezzetler**: Gastronomi mekanları
        '
    ],
    'alanya' => [
        'description' => 'Alanya\'da konut projeleri, oteller ve ticari mekanlar için profesyonel fotoğrafçılık.',
        'image' => '/assets/images/portfolio-3.jpg',
        'specialty' => 'Konut Projeleri',
        'content' => '
## Alanya Mekan Fotoğrafçısı

Alanya, Antalya\'nın en büyük turizm merkezlerinden biri. Konut projeleri, oteller ve ticari mekanları ile öne çıkan bu bölgede, emlak ve turizm sektörüne özel fotoğrafçılık hizmetleri sunuyoruz.

### Alanya\'da Hizmet Alanlarımız

- **Konut Projeleri**: Yeni yapılan konut kompleksleri
- **Otel ve Resort**: Büyük ölçekli konaklama tesisleri
- **Ticari Mekanlar**: İş merkezleri ve mağazalar
- **Restoran ve Cafe**: Sahil ve merkez mekanları
- **Villa ve Daire**: Satış ve kiralama fotoğrafları

### Alanya\'nın Özellikleri

- **Geniş Sahil**: Uzun plaj şeridi ve sahil tesisleri
- **Tarihi Kale**: Tarihi doku entegrasyonu
- **Modern Yapılar**: Yeni konut projeleri
- **Turizm Potansiyeli**: Yüksek sezon çekimleri
        '
    ],
    'manavgat' => [
        'description' => 'Manavgat\'ta otel, restoran ve emlak projeleri için profesyonel mekan fotoğrafçılığı.',
        'image' => '/assets/images/portfolio-1.jpg',
        'specialty' => 'Emlak Fotoğrafçılığı',
        'content' => '
## Manavgat Mekan Fotoğrafçısı

Manavgat, Antalya\'nın önemli turizm merkezlerinden biri. Side antik kenti, oteller ve emlak projeleri ile öne çıkan bu bölgede, turizm ve emlak sektörüne özel fotoğrafçılık hizmetleri sunuyoruz.

### Manavgat\'ta Hizmet Alanlarımız

- **Emlak Fotoğrafçılığı**: Satış ve kiralama için pazarlama görselleri
- **Otel ve Restoran**: Side bölgesi turizm tesisleri
- **Tarihi Yapılar**: Antik kent entegrasyonlu çekimler
- **Konut Projeleri**: Yeni yapılan konut kompleksleri
- **Ticari Mekanlar**: İş yerleri ve mağazalar

### Manavgat\'ın Özel Özellikleri

- **Side Antik Kenti**: Tarihi doku ve modern yaşam
- **Manavgat Şelalesi**: Doğal güzellik entegrasyonu
- **Geniş Sahil**: Uzun plaj şeridi
- **Turizm Potansiyeli**: Yüksek sezon çekimleri
        '
    ],
    'finike' => [
        'description' => 'Finike\'de emlak, villa ve ticari mekanlar için profesyonel fotoğrafçılık hizmetleri.',
        'image' => '/assets/images/portfolio-2.jpg',
        'specialty' => 'Emlak Fotoğrafçılığı',
        'content' => '
## Finike Mekan Fotoğrafçısı

Finike, Antalya\'nın sakin tatil beldelerinden biri. Emlak projeleri, villalar ve ticari mekanları ile öne çıkan bu bölgede, satış ve kiralama için profesyonel fotoğrafçılık hizmetleri sunuyoruz.

### Finike\'de Hizmet Alanlarımız

- **Emlak Fotoğrafçılığı**: Satış ve kiralama için pazarlama görselleri
- **Villa Çekimleri**: Özel konutların profesyonel fotoğrafları
- **Ticari Mekanlar**: İş yerleri ve mağazalar
- **Restoran ve Cafe**: Sahil ve merkez mekanları
- **Konut Projeleri**: Yeni yapılan konut kompleksleri

### Finike\'nin Özellikleri

- **Sakin Atmosfer**: Huzurlu tatil beldesi
- **Geniş Sahil**: Uzun plaj şeridi
- **Tarım Bölgesi**: Portakal bahçeleri ve doğal güzellikler
- **Emlak Potansiyeli**: Gelişen konut projeleri
        '
    ],
    'beypazari' => [
        'description' => 'Beypazarı\'nda iş merkezleri, ticari alanlar ve tarihi yapılar için profesyonel mekan fotoğrafçılığı hizmetleri.',
        'image' => '/assets/images/portfolio-6.jpg',
        'specialty' => 'İş Merkezi ve Ticari Alan Fotoğrafçılığı',
        'content' => '
## Beypazarı Mekan Fotoğrafçısı

Beypazarı, Ankara\'nın önemli ilçelerinden biri. Tarihi dokusu, gelişen ticari yapısı ve iş merkezleri ile öne çıkan bu bölgede, profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz.

### Beypazarı\'nda Hizmet Verdiğimiz Alanlar

- **İş Merkezi Fotoğrafçılığı**: Beypazarı\'ndaki iş merkezleri ve ticari komplekslerin profesyonel görünümü
- **Ticari Alan Fotoğrafçılığı**: Mağaza, showroom ve perakende işletmelerinin çekici görselleri
- **Tarihi Yapı Fotoğrafçılığı**: Geleneksel konaklar ve tarihi binaların belgelenmesi
- **Emlak Fotoğrafçılığı**: Konut ve ticari gayrimenkul pazarlama görselleri
- **Ofis Fotoğrafçılığı**: Kurumsal ofislerin modern görünümü

### Beypazarı\'nda İş Merkezi Fotoğrafçılığı

Beypazarı\'nın gelişen ticari yapısı için profesyonel fotoğrafçılık hizmetleri:
- İş merkezlerinin dış cephe ve iç mekan çekimleri
- Ticari komplekslerin profesyonel tanıtımı
- Lobi ve ortak kullanım alanlarının görsel sunumu
- Ofis alanlarının ferah ve çağdaş görünümü
- İş merkezi kiralama için pazarlama görselleri

### Beypazarı\'nda Ticari Alan Fotoğrafçılığı

Beypazarı\'nın dinamik ticaret sektörü için özel hizmetler:
- Mağaza ve showroom iç mekan fotoğrafçılığı
- Perakende işletmelerinin ürün sunumu
- Vitrin ve cephe görünüm çekimleri
- E-ticaret platformları için ürün fotoğrafları
- Pazarlama ve tanıtım kampanyaları için görsel içerik

### Beypazarı\'nın Özel Özellikleri

Beypazarı\'nın kendine özgü karakteristik özellikleri:
- **Tarihi Dokusu**: Geleneksel konaklar ve eski şehir yapısı
- **Gelişen Ticaret**: Modern iş merkezleri ve ticari yapılar
- **Kültürel Miras**: Tarihi binalar ve turizm potansiyeli
- **Yerel Ekonomi**: El sanatları ve geleneksel üretim

### Çalışma Sürecimiz

1. **Keşif Ziyareti**: Mekanın özelliklerini analiz etme
2. **Çekim Planlaması**: En uygun açılar ve zamanları belirleme
3. **Profesyonel Çekim**: İş merkezi veya ticari alanın detaylı fotoğraflanması
4. **Post-Prodüksiyon**: Kalite optimizasyonu ve renk düzeltme
5. **Teslimat**: Pazarlama için hazır görseller
        '
    ]
];

// Add default content for other districts
$defaultContent = [
    'description' => $district['name'] . ', ' . $province['name'] . '\'da profesyonel mekan fotoğrafçılığı hizmetleri.',
    'image' => '/assets/images/portfolio-1.jpg',
    'specialty' => $district['local_notes'] ?: 'Mekan Fotoğrafçılığı',
    'content' => '
## ' . $district['name'] . ' Mekan Fotoğrafçısı

' . $district['name'] . ', ' . $province['name'] . '\'nın önemli bölgelerinden biri. Bu güzel lokasyonda profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz.

### Hizmet Alanlarımız

- **Mimari Fotoğrafçılık**: Binaların dış cephe ve detay çekimleri
- **İç Mekan Fotoğrafçılığı**: Ev, ofis ve ticari alan fotoğrafları
- **Emlak Fotoğrafçılığı**: Satış ve kiralama için pazarlama görselleri
- **Ticari Fotoğrafçılık**: İş yerleri ve kurumsal mekan çekimleri
    ',
    'faq' => []
];

$districtData = array_merge($district, $districtContent[$districtSlug] ?? $defaultContent);
$districtData['province'] = $province['name'];
$districtData['specialty'] = $districtData['specialty'] ?? ($district['local_notes'] ?: 'Mekan Fotoğrafçılığı');

// Get other districts in the same province for sidebar
$allDistrictsInProvince = $supabase->select('locations_district', [
    'province_id' => $province['id'],
    'is_active' => true,
    'select' => 'name,slug,local_notes'
]);

$otherDistricts = array_filter($allDistrictsInProvince, function($d) use ($districtSlug) {
    return $d['slug'] !== $districtSlug;
});
$otherDistricts = array_slice(array_values($otherDistricts), 0, 10);

foreach ($otherDistricts as &$otherDistrict) {
    $otherDistrict['specialty'] = $otherDistrict['local_notes'] ?: 'Mekan Fotoğrafçılığı';
}

// Get all active services for district sidebar
$allServices = $supabase->select('services', [
    'is_active' => true,
    'select' => 'name,slug',
    'order' => 'name'
]);

// Use SEO page content if available
if ($seoPage && isset($district['use_seo_content']) && $district['use_seo_content']) {
    $pageTitle = e($seoPage['title']);
    $pageDescription = e($seoPage['meta_description']);
    $pageH1 = e($seoPage['h1']);
    $pageContent = $seoPage['content_md'] ?? '';
} else {
    $pageTitle = e($district['name']) . ', ' . e($province['name']) . ' Mekan Fotoğrafçısı | Profesyonel Fotoğrafçılık Hizmetleri';
    $pageDescription = e($districtData['description']);
    $pageH1 = e($district['name']) . ' Mekan Fotoğrafçısı';
    $pageContent = $districtData['content'] ?? 'İçerik hazırlanıyor...';
}

$canonicalUrl = 'https://mekanfotografcisi.tr/locations/' . e($provinceSlug) . '/' . e($districtSlug);

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
        'addressLocality' => e($district['name']),
        'addressRegion' => e($province['name']),
        'postalCode' => '07580',
        'addressCountry' => 'TR'
    ],
    'serviceArea' => [
        '@type' => 'City',
        'name' => e($district['name']),
        'containedInPlace' => [
            '@type' => 'State',
            'name' => e($province['name'])
        ]
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
            <a href="/locations/<?= e($provinceSlug) ?>"><?= e($province['name']) ?></a>
            <span>›</span>
            <strong><?= e($district['name']) ?></strong>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="seo-hero">
        <div class="container">
            <h1><?= $pageH1 ?></h1>
            <p><?= $pageDescription ?></p>
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
                    <?= markdownToHtml($pageContent) ?>
                    
                    <h3>Neden <?= e($district['name']) ?>'ta Bizi Tercih Etmelisiniz?</h3>
                    <ul>
                        <li><strong>Yerel Bilgi:</strong> <?= e($district['name']) ?>'ın özel koşullarını bilen deneyimli ekip</li>
                        <li><strong>Hızlı Hizmet:</strong> Bölgede sürekli ekibimiz ile hızlı randevu imkanı</li>
                        <li><strong>Kalite Garantisi:</strong> <?= e($districtData['specialty']) ?> konusunda uzman yaklaşım</li>
                        <li><strong>Rekabetçi Fiyat:</strong> Bölgesel avantajlarımızı müşterilerimize yansıtıyoruz</li>
                    </ul>

                    <h3>Çalışma Sürecimiz</h3>
                    <p><?= e($district['name']) ?>'ta projeleriniz için özel olarak tasarladığımız çekim süreci:</p>
                    <ol>
                        <li><strong>Ön Görüşme:</strong> Projenizin detaylarını konuşur, beklentilerinizi anlıyoruz</li>
                        <li><strong>Lokasyon Keşfi:</strong> <?= e($district['name']) ?>'ın özel koşullarına göre plan yapıyoruz</li>
                        <li><strong>Profesyonel Çekim:</strong> Uzman ekibimiz ve modern ekipmanlarla çekim gerçekleştiriyoruz</li>
                        <li><strong>Hızlı Teslimat:</strong> 3-5 iş günü içinde düzenlenmiş fotoğraflarınızı teslim ediyoruz</li>
                    </ol>
                </div>
                
                <div class="sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-card-image">
                            <img src="<?= e($districtData['image'] ?? '/assets/images/portfolio-1.jpg') ?>" alt="<?= e($district['name']) ?> Mekan Fotoğrafçısı" loading="lazy">
                        </div>
                        <div class="sidebar-card-content">
                            <h3><?= e($district['name']) ?> Bilgileri</h3>
                            <p><strong>İl:</strong> <?= e($districtData['province']) ?></p>
                            <p><strong>Uzmanlık:</strong> <?= e($districtData['specialty']) ?></p>
                            <p><strong>Hizmet Türü:</strong> Profesyonel Mekan Fotoğrafçılığı</p>
                            <p><strong>Çalışma Saatleri:</strong> 7/24 Randevu</p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <h3><?= e($district['name']) ?>'ta Çekim Planlayın</h3>
                        <p>Bu bölgedeki projeleriniz için hemen teklif alın.</p>
                        <a href="/#iletisim" class="btn btn-outline btn-block">İletişime Geçin</a>
                        <a href="tel:+905074677502" class="btn btn-outline btn-block">📞 +90 507 467 75 02</a>
                    </div>

                    <div class="sidebar-card">
                        <div class="sidebar-card-content">
                            <h3>Bu İlçede Sunduğumuz Hizmetler</h3>
                            <ul>
                                <?php foreach ($allServices as $service): ?>
                                    <li>
                                        <a href="/services/<?= e($service['slug']) ?>"><?= e($service['name']) ?></a>
                                        <?php 
                                        // Highlight services that match district specialty
                                        $specialtyLower = mb_strtolower($districtData['specialty']);
                                        $serviceNameLower = mb_strtolower($service['name']);
                                        if (strpos($specialtyLower, 'villa') !== false && strpos($serviceNameLower, 'villa') !== false) {
                                            echo ' <span style="color: var(--accent-color); font-size: 0.85em;">★</span>';
                                        } elseif (strpos($specialtyLower, 'otel') !== false && (strpos($serviceNameLower, 'otel') !== false || strpos($serviceNameLower, 'pansiyon') !== false)) {
                                            echo ' <span style="color: var(--accent-color); font-size: 0.85em;">★</span>';
                                        } elseif (strpos($specialtyLower, 'iş merkezi') !== false && strpos($serviceNameLower, 'iş merkezi') !== false) {
                                            echo ' <span style="color: var(--accent-color); font-size: 0.85em;">★</span>';
                                        } elseif (strpos($specialtyLower, 'ticari') !== false && strpos($serviceNameLower, 'ticari') !== false) {
                                            echo ' <span style="color: var(--accent-color); font-size: 0.85em;">★</span>';
                                        }
                                        ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <?php if (!empty($otherDistricts)): ?>
                    <div class="sidebar-card">
                        <div class="sidebar-card-content">
                            <h3><?= e($province['name']) ?>'daki Diğer İlçeler</h3>
                            <ul>
                                <?php foreach ($otherDistricts as $otherDistrict): ?>
                                <li><a href="/locations/<?= e($provinceSlug) ?>/<?= e($otherDistrict['slug']) ?>"><?= e($otherDistrict['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <?php if (!empty($districtData['faq'])): ?>
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title" style="text-align: center; margin-bottom: 48px;">
                <?= e($district['name']) ?> Hakkında Sıkça Sorulan Sorular
            </h2>
            <div class="faq-list">
                <?php foreach ($districtData['faq'] as $index => $faq): ?>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(<?= $index ?>)">
                        <span><?= e($faq['question']) ?></span>
                        <span class="faq-toggle" id="toggle-<?= $index ?>">+</span>
                    </div>
                    <div class="faq-answer" id="answer-<?= $index ?>">
                        <p><?= e($faq['answer']) ?></p>
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
            <h2><?= e($district['name']) ?>'ta Profesyonel Fotoğrafçılık Hizmeti</h2>
            <p><?= e($districtData['specialty']) ?> konusunda uzman ekibimizle projelerinizi hayata geçirin.</p>
            <div class="cta-buttons">
                <a href="tel:+905074677502" class="btn btn-outline">📞 +90 507 467 75 02</a>
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
            </div>
        </div>
    </section>
</main>

<script>
function toggleFAQ(index) {
    const answer = document.getElementById(`answer-${index}`);
    const toggle = document.getElementById(`toggle-${index}`);
    const item = answer.closest('.faq-item');
    
    // Toggle current item
    if (item.classList.contains('active')) {
        item.classList.remove('active');
        toggle.textContent = '+';
    } else {
        // Close all FAQs
        document.querySelectorAll('.faq-item').forEach(el => {
            el.classList.remove('active');
            const toggleEl = el.querySelector('.faq-toggle');
            if (toggleEl) toggleEl.textContent = '+';
        });
        
        // Open current FAQ
        item.classList.add('active');
        toggle.textContent = '×';
    }
}
</script>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
