<?php
/**
 * Service Detail Page
 * /services/{slug}
 */

require_once __DIR__ . '/../includes/supabase.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/markdown.php';

$serviceSlug = sanitizeSlug($_GET['slug'] ?? '');

// Get service from database
$serviceResults = $supabase->select('services', [
    'slug' => $serviceSlug,
    'is_active' => true
]);

$service = $serviceResults[0] ?? null;

if (!$service) {
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/../404.html';
    exit;
}

// Verify that the service slug matches (in case of mock data issues)
if (isset($service['slug']) && $service['slug'] !== $serviceSlug) {
    // If slug doesn't match, try to find the correct service from all services
    $allServicesCheck = $supabase->select('services', [
        'is_active' => 'eq.true',
        'select' => 'id,name,slug,short_intro'
    ]);
    
    foreach ($allServicesCheck as $s) {
        if ($s['slug'] === $serviceSlug) {
            $service = $s;
            break;
        }
    }
}

// Extended content for each service
$serviceContent = [
    'mimari-fotografcilik' => [
        'description' => 'Antalya ve Muğla bölgesinde binaların dış cephe, peyzaj ve çevre düzenlemelerini en etkileyici açılardan fotoğraflıyoruz.',
        'image' => '/assets/images/mimari-fotograf.jpg',
        'content' => '
## Profesyonel Mimari Fotoğrafçılık Hizmetleri

Antalya ve Muğla bölgesinde 10 yılı aşkın deneyimimizle, mimari yapıların en etkileyici yönlerini öne çıkaran profesyonel fotoğraflar üretiyoruz.

### Hizmet Kapsamımız

- **Dış Cephe Fotoğrafçılığı**: Binaların mimari detaylarını vurgulayan açılar
- **Peyzaj Entegrasyonu**: Yapının çevresiyle uyumunu gösteren kompozisyonlar  
- **Gece Çekimleri**: Aydınlatma sistemlerini öne çıkaran özel çekimler
- **Drone Çekimleri**: Havadan perspektif ile kapsamlı görünümler

### Çalışma Sürecimiz

1. **Keşif ve Planlama**: Yapıyı inceleyerek en iyi çekim zamanlarını belirliyoruz
2. **Teknik Hazırlık**: Işık koşullarına göre ekipman seçimi yapıyoruz
3. **Profesyonel Çekim**: Farklı açılardan detaylı fotoğraf çekimi
4. **Post-prodüksiyon**: Renk düzeltme ve kalite optimizasyonu

### Neden Bizi Tercih Etmelisiniz?

- Antalya ve Muğla\'nın ışık koşullarına hakim uzman ekip
- Son teknoloji kameralar ve objektifler
- Mimari detayları vurgulayan özel teknikler
- Hızlı teslimat ve revizyon imkanı
        '
    ],
    'ic-mekan-fotografciligi' => [
        'description' => 'Ev, villa, ofis ve ticari alanların iç mekan fotoğraflarını profesyonel ekipmanlarla çekiyoruz.',
        'image' => '/assets/images/ic-mekan.jpg',
        'content' => '
## İç Mekan Fotoğrafçılığında Uzman Ekip

Antalya ve Muğla bölgesinde iç mekanların atmosferini en iyi şekilde yansıtan profesyonel fotoğraflar üretiyoruz.

### Uzmanlık Alanlarımız

- **Konut Fotoğrafçılığı**: Villa, daire ve ev iç mekanları
- **Ticari Alan Çekimleri**: Ofis, mağaza ve showroom fotoğrafları
- **Otel İç Mekanları**: Oda, lobi ve ortak alan çekimleri
- **Restoran Fotoğrafçılığı**: Ambiyans ve dekorasyon vurgusu

### Teknik Özelliklerimiz

- **Geniş Açı Objektifler**: Mekanları ferah gösterme
- **Profesyonel Aydınlatma**: Doğal ve yapay ışık dengesi
- **HDR Teknikleri**: Detay kaybı olmayan görüntüler
- **Perspektif Düzeltme**: Mimari çizgilerin doğru görünümü

### Çekim Süreci

1. **Mekan Hazırlığı**: Düzenleme ve temizlik önerileri
2. **Işık Analizi**: En uygun çekim saatlerinin belirlenmesi
3. **Kompozisyon Planlama**: Mekanın en güzel açılarının seçimi
4. **Detay Çekimleri**: Özel dekorasyon ve tasarım öğeleri
        '
    ],
    'emlak-fotografciligi' => [
        'description' => 'Satılık veya kiralık mülklerinizi en çekici şekilde göstererek pazarlama sürecinize katkı sağlıyoruz.',
        'image' => '/assets/images/emlak-fotograf.jpg',
        'content' => '
## Emlak Pazarlamasında Fark Yaratan Fotoğraflar

Antalya ve Muğla bölgesinde emlak sektörüne özel, satış ve kiralama sürecinizi hızlandıran profesyonel fotoğraflar.

### Emlak Fotoğrafçılığı Hizmetlerimiz

- **Villa Fotoğrafçılığı**: Lüks konutların tüm detayları
- **Daire Çekimleri**: Kompakt mekanların ferah görünümü
- **Arsa ve Arazi**: Lokasyon avantajlarını vurgulama
- **Ticari Gayrimenkul**: İş yeri potansiyelini gösterme

### Pazarlama Odaklı Yaklaşım

- **Satış Hızlandırma**: Çekici görseller ile hızlı ilgi çekme
- **Değer Artırma**: Profesyonel sunum ile fiyat avantajı
- **Geniş Erişim**: Web ve sosyal medya için optimize edilmiş görseller
- **Rekabet Avantajı**: Sektörde öne çıkan görsel kalite

### Özel Tekniklerimiz

- **Sanal Tur Hazırlığı**: 360° fotoğraf çekimleri
- **Gece-Gündüz Çekimleri**: Farklı atmosferlerin yakalanması
- **Çevre Vurgusu**: Lokasyon avantajlarının gösterilmesi
- **Lifestyle Staging**: Yaşam tarzını yansıtan düzenlemeler

### Teslimat Paketleri

- **Hızlı Paket**: 24 saat içinde temel fotoğraflar
- **Standart Paket**: 3 gün içinde düzenlenmiş görsel seti
- **Premium Paket**: Sanal tur ve video içerikli kapsamlı sunum
        '
    ],
    'otel-restoran-fotografciligi' => [
        'description' => 'Otel odaları, restoranlar ve cafe mekanları için müşteri çekici fotoğraflar üretiyoruz.',
        'image' => '/assets/images/otel-restoran.jpg',
        'content' => '
## Turizm Sektörüne Özel Fotoğrafçılık

Antalya ve Muğla\'nın turizm merkezlerinde otel, restoran ve cafe işletmeleri için rezervasyon artırıcı fotoğraflar.

### Otel Fotoğrafçılığı

- **Oda Çekimleri**: Konfor ve lüksü yansıtan görüntüler
- **Ortak Alanlar**: Lobi, havuz ve bahçe fotoğrafları
- **Yiyecek-İçecek**: Restoran ve bar sunumları
- **Aktivite Alanları**: Spa, fitness ve eğlence mekanları

### Restoran ve Cafe Çekimleri

- **Ambiyans Fotoğrafları**: Atmosfer ve dekorasyon vurgusu
- **Yemek Sunumu**: Gastronomi fotoğrafçılığı
- **Mutfak Çekimleri**: Hijyen ve profesyonellik gösterimi
- **Dış Mekan**: Terras ve bahçe alanları

### Turizm Pazarlaması Odaklı

- **Booking Platformları**: Rezervasyon sitelerine uygun formatlar
- **Sosyal Medya**: Instagram ve Facebook için optimize edilmiş görseller
- **Web Sitesi**: Hızlı yüklenen, etkileyici ana sayfa görselleri
- **Broşür ve Katalog**: Basılı materyal için yüksek çözünürlük

### Sezonsal Çekim Hizmetleri

- **Yaz Sezonu**: Havuz, plaj ve dış mekan vurgusu
- **Kış Sezonu**: İç mekan sıcaklığı ve konfor
- **Özel Günler**: Düğün, organizasyon ve etkinlik çekimleri
- **Gece Çekimleri**: Romantik atmosfer ve aydınlatma
        '
    ],
    'butik-otel-fotografciligi' => [
        'description' => 'Küçük ölçekli, özel karakterli butik oteller için özel fotoğrafçılık hizmetleri.',
        'image' => '/assets/images/portfolio-1.jpg',
        'content' => '
## Butik Otel Fotoğrafçılığında Uzman Ekip

Antalya ve Muğla bölgesinde butik otellerin özel karakterini ve samimi atmosferini yansıtan profesyonel fotoğraflar üretiyoruz.

### Butik Otellerin Özel İhtiyaçları

Butik oteller, büyük ölçekli tesislerden farklı olarak özel bir yaklaşım gerektirir. Her butik otelin kendine özgü hikayesi, tasarımı ve atmosferi vardır.

### Hizmet Kapsamımız

- **Oda Fotoğrafçılığı**: Her odanın özel karakterini vurgulama
- **Ortak Alanlar**: Lobi, bahçe ve özel alanların çekimi
- **Detay Çekimleri**: Dekorasyon, sanat eserleri ve özel tasarım öğeleri
- **Dış Mekan**: Bina cephesi, bahçe ve çevre düzenlemesi
- **Ambiyans Fotoğrafları**: Otelin genel atmosferini yansıtan görüntüler

### Butik Otel Fotoğrafçılığının Özellikleri

- **Hikaye Anlatımı**: Otelin özel karakterini öne çıkarma
- **Samimi Atmosfer**: Misafirlerin kendini evinde hissetmesini sağlayan görüntüler
- **Tasarım Vurgusu**: Özel dekorasyon ve mimari detayların gösterimi
- **Yerel Kültür**: Bölgenin kültürel özelliklerini yansıtma
- **Doğal Işık**: Butik otellerin sıcak ve samimi atmosferini vurgulama

### Çalışma Sürecimiz

1. **Keşif Ziyareti**: Otelin özel karakterini anlama
2. **Hikaye Planlama**: Otelin hikayesini fotoğraflarla anlatma stratejisi
3. **Özel Çekim**: Her detayın özenle fotoğraflanması
4. **Post-Prodüksiyon**: Otelin atmosferini yansıtan renk düzenlemeleri

### Pazarlama Desteği

- **Booking Platformları**: Booking.com, Airbnb gibi platformlar için optimize edilmiş görseller
- **Sosyal Medya**: Instagram ve Facebook için hikaye anlatan görseller
- **Web Sitesi**: Otelin karakterini yansıtan ana sayfa ve galeri görselleri
- **Broşür ve Katalog**: Basılı materyal için yüksek kaliteli görseller
        '
    ],
    'yemek-fotografciligi' => [
        'description' => 'Restoran ve cafe menüleri için profesyonel yemek ve gastronomi fotoğrafçılığı.',
        'image' => '/assets/images/portfolio-2.jpg',
        'content' => '
## Profesyonel Yemek Fotoğrafçılığı

Antalya ve Muğla bölgesinde restoran, cafe ve gastronomi işletmeleri için iştah açıcı, profesyonel yemek fotoğrafları üretiyoruz.

### Yemek Fotoğrafçılığı Hizmetlerimiz

- **Menü Fotoğrafçılığı**: Tüm menü öğelerinin profesyonel çekimi
- **Yemek Sunumu**: Tabak düzenlemesi ve sunum teknikleri
- **Gastronomi Fotoğrafçılığı**: Yüksek kaliteli yemek görselleri
- **Mutfak Çekimleri**: Hazırlık süreçleri ve mutfak atmosferi
- **Restoran Ambiyansı**: Yemek ve mekan uyumunu gösteren çekimler

### Teknik Özelliklerimiz

- **Profesyonel Aydınlatma**: Yemeğin en iyi görünmesini sağlayan ışık teknikleri
- **Kompozisyon**: Görsel olarak çekici tabak düzenlemeleri
- **Renk Düzeltme**: Yemeğin doğal renklerini öne çıkarma
- **Makro Çekimler**: Detay ve doku vurgusu
- **Hızlı Çekim**: Yemeğin taze görünümünü yakalama

### Yemek Fotoğrafçılığında Önemli Noktalar

- **Tazelik**: Yemeğin en taze halini yakalama
- **Sunum**: Profesyonel tabak düzenlemesi
- **Işık**: Doğal ve yapay ışık dengesi
- **Açılar**: Yemeğin en çekici açıdan görünümü
- **Stil**: Restoranın konseptine uygun görsel stil

### Hizmet Alanlarımız

- **Fine Dining Restoranlar**: Yüksek kaliteli gastronomi fotoğrafları
- **Cafe ve Bistro**: Kahve, pasta ve hafif yemekler
- **Fast Food**: Hızlı servis için çekici görseller
- **Pastane ve Fırın**: Tatlı ve hamur işi fotoğrafları
- **Bar ve Pub**: İçecek ve atıştırmalık çekimleri

### Pazarlama Kullanımı

- **Menü Tasarımı**: Basılı ve dijital menüler için görseller
- **Sosyal Medya**: Instagram, Facebook için iştah açıcı görseller
- **Web Sitesi**: Online menü ve galeri için fotoğraflar
- **Reklam Materyalleri**: Broşür, poster ve reklam görselleri
- **Delivery Platformları**: Yemeksepeti, Getir gibi platformlar için görseller

### Çalışma Süreci

1. **Menü İnceleme**: Çekilecek yemeklerin belirlenmesi
2. **Hazırlık**: Gerekli ekipman ve dekorasyon malzemeleri
3. **Çekim**: Her yemeğin en iyi halini yakalama
4. **Düzenleme**: Renk düzeltme ve kalite optimizasyonu
5. **Teslimat**: Kullanıma hazır görseller
        '
    ],
    'lifestyle-fotografciligi' => [
        'description' => 'Yaşam tarzını yansıtan, hikaye anlatan profesyonel lifestyle fotoğrafçılığı.',
        'image' => '/assets/images/portfolio-3.jpg',
        'content' => '
## Lifestyle Fotoğrafçılığı - Hikaye Anlatan Görseller

Antalya ve Muğla bölgesinde mekanların yaşam tarzını yansıtan, duygusal bağ kuran profesyonel lifestyle fotoğrafları üretiyoruz.

### Lifestyle Fotoğrafçılığı Nedir?

Lifestyle fotoğrafçılığı, mekanların sadece görsel olarak değil, aynı zamanda yaşam tarzını ve atmosferini de yansıtan bir fotoğrafçılık türüdür. Bu fotoğraflar, potansiyel müşterilerin kendilerini o mekanda hayal etmelerini sağlar.

### Hizmet Kapsamımız

- **Villa Lifestyle**: Lüks yaşam tarzını yansıtan villa fotoğrafları
- **Otel Lifestyle**: Misafirlerin deneyimini öne çıkaran otel görselleri
- **Restoran Lifestyle**: Yemek ve sosyal deneyimi birleştiren görseller
- **Emlak Lifestyle**: Yaşam tarzını vurgulayan emlak fotoğrafları
- **Ticari Mekan Lifestyle**: İş yerlerinin atmosferini yansıtan görseller

### Lifestyle Fotoğrafçılığının Özellikleri

- **Hikaye Anlatımı**: Her fotoğraf bir hikaye anlatır
- **Doğal Görünüm**: Staged ama doğal görünen kompozisyonlar
- **Duygusal Bağ**: İzleyicide duygusal bir bağ oluşturma
- **Yaşam Tarzı Vurgusu**: Mekanın sunduğu yaşam tarzını gösterme
- **Atmosfer**: Mekanın genel atmosferini yakalama

### Çekim Teknikleri

- **Doğal Işık**: Gün ışığının doğal kullanımı
- **Kompozisyon**: Yaşam sahnelerini içeren kompozisyonlar
- **Renk Paleti**: Mekanın atmosferine uygun renk düzenlemeleri
- **Detay Vurgusu**: Yaşam tarzını yansıtan özel detaylar
- **Geniş Açı**: Mekanın bütününü gösteren çekimler

### Kullanım Alanları

- **Emlak Pazarlaması**: Satış ve kiralama için yaşam tarzı vurgusu
- **Otel Rezervasyonları**: Misafir deneyimini öne çıkarma
- **Restoran Pazarlaması**: Sosyal deneyimi vurgulama
- **Sosyal Medya**: Instagram ve Facebook için hikaye anlatan görseller
- **Web Sitesi**: Ana sayfa ve galeri için atmosferik görseller

### Lifestyle Çekim Süreci

1. **Konsept Geliştirme**: Mekanın yaşam tarzı konseptini belirleme
2. **Staging**: Doğal görünen ama düzenlenmiş sahneler
3. **Çekim**: Yaşam sahnelerini içeren profesyonel fotoğraflar
4. **Post-Prodüksiyon**: Atmosferi güçlendiren renk düzenlemeleri
5. **Hikaye Oluşturma**: Görsellerle bir hikaye anlatımı

### Özel Projeler

- **Sezonsal Çekimler**: Yaz ve kış sezonu için özel lifestyle görselleri
- **Etkinlik Çekimleri**: Özel günler ve organizasyonlar
- **Sosyal Medya İçerikleri**: Düzenli içerik üretimi
- **Kampanya Görselleri**: Özel pazarlama kampanyaları için görseller
        '
    ],
    'villa-fotografciligi' => [
        'description' => 'Lüks villaların tüm detaylarını profesyonel fotoğraflarla ölümsüzleştiriyoruz.',
        'image' => '/assets/images/portfolio-1.jpg',
        'content' => '
## Villa Fotoğrafçılığı - Lüks Yaşamın Görsel Tanıtımı

Antalya ve Muğla bölgesinde lüks villaların hem iç hem dış mekanlarını profesyonel fotoğraflarla belgeliyoruz. Villa sahipleri ve emlak danışmanları için satış ve kiralama sürecini destekleyen görseller üretiyoruz.

### Villa Fotoğrafçılığı Hizmetlerimiz

- **Dış Cephe Çekimleri**: Villa mimarisinin öne çıkan özelliklerini vurgulama
- **İç Mekan Fotoğrafçılığı**: Tüm odaların ferah ve lüks görünümü
- **Havuz ve Bahçe**: Dış mekan alanlarının çekimi
- **Deniz Manzarası**: Panoramik görünümler ve manzara vurgusu
- **Detay Çekimleri**: Özel tasarım öğeleri ve dekorasyon
- **Gece Çekimleri**: Aydınlatma sistemlerinin öne çıkarılması

### Villa Fotoğrafçılığının Özellikleri

- **Lüks Vurgusu**: Villanın prestijli karakterini yansıtma
- **Geniş Açı Çekimler**: Mekanları ferah ve büyük gösterme
- **Doğal Işık**: Gün ışığının en iyi kullanımı
- **Drone Çekimleri**: Havadan görünüm ve çevre entegrasyonu
- **Yaşam Tarzı**: Villa yaşamının atmosferini yakalama

### Çalışma Sürecimiz

1. **Keşif Ziyareti**: Villanın özelliklerini analiz etme
2. **Çekim Planlaması**: En iyi açılar ve zamanları belirleme
3. **Profesyonel Çekim**: İç ve dış mekan detaylı fotoğraflama
4. **Post-Prodüksiyon**: Kalite optimizasyonu ve renk düzeltme
5. **Teslimat**: Pazarlama için hazır görseller

### Pazarlama Desteği

- **Emlak Portalları**: Sahibinden, Emlakjet gibi platformlar için görseller
- **Sosyal Medya**: Instagram ve Facebook için çekici görseller
- **Web Sitesi**: Villa tanıtım sayfaları için fotoğraflar
- **Basılı Materyaller**: Broşür ve katalog için yüksek çözünürlük
        '
    ],
    'otel-fotografciligi' => [
        'description' => 'Otel, resort ve tatil köylerinin tüm alanlarını profesyonel fotoğraflarla belgeliyoruz.',
        'image' => '/assets/images/portfolio-2.jpg',
        'content' => '
## Otel Fotoğrafçılığı - Turizm Sektörüne Özel

Antalya ve Muğla\'nın turizm merkezlerinde otel, resort ve tatil köylerinin tüm alanlarını profesyonel fotoğraflarla belgeliyoruz. Rezervasyon artırıcı, müşteri çekici görseller üretiyoruz.

### Otel Fotoğrafçılığı Kapsamımız

- **Oda Çekimleri**: Tüm oda tiplerinin konfor ve lüks vurgusu
- **Ortak Alanlar**: Lobi, resepsiyon ve genel alanlar
- **Havuz ve Plaj**: Dış mekan aktivite alanları
- **Restoran ve Bar**: Yeme-içme mekanları
- **Spa ve Wellness**: Dinlenme ve sağlık tesisleri
- **Etkinlik Alanları**: Toplantı, konferans ve düğün salonları
- **Çocuk Kulüpleri**: Aile dostu alanlar

### Otel Fotoğrafçılığının Önemi

- **Rezervasyon Artırma**: Çekici görseller ile rezervasyon dönüşümü
- **Marka Değeri**: Profesyonel görseller ile marka konumlandırma
- **Rekabet Avantajı**: Sektörde öne çıkan görsel kalite
- **Online Presence**: Booking platformları ve web sitesi için görseller
- **Sezonsal Güncelleme**: Farklı sezonlar için görsel içerik

### Çalışma Sürecimiz

1. **Tesis İncelemesi**: Tüm alanların analizi
2. **Çekim Stratejisi**: Hangi alanların nasıl çekileceğinin planlanması
3. **Koordinasyon**: Misafir rahatsızlığı olmadan çekim zamanlaması
4. **Profesyonel Çekim**: Tüm alanların detaylı fotoğraflanması
5. **Post-Prodüksiyon**: Kalite optimizasyonu
6. **Formatlama**: Farklı platformlar için format dönüşümü

### Platform Desteği

- **Booking.com**: Oda ve tesis fotoğrafları
- **TripAdvisor**: Profil ve galeri görselleri
- **Web Sitesi**: Ana sayfa ve galeri
- **Sosyal Medya**: Instagram, Facebook içerikleri
- **Broşür ve Katalog**: Basılı materyaller
        '
    ],
    'yat-fotografciligi' => [
        'description' => 'Lüks yatların iç ve dış mekanlarını profesyonel fotoğraflarla çekiyoruz.',
        'image' => '/assets/images/portfolio-3.jpg',
        'content' => '
## Yat Fotoğrafçılığı - Denizcilik Lüksü

Antalya ve Muğla\'nın marinalarında lüks yatların iç ve dış mekanlarını profesyonel fotoğraflarla belgeliyoruz. Yat sahipleri ve kiralama şirketleri için pazarlama odaklı görseller üretiyoruz.

### Yat Fotoğrafçılığı Hizmetlerimiz

- **Dış Görünüm**: Yatın genel görünümü ve tasarım detayları
- **Kokpit Çekimleri**: Kaptan köşkü ve navigasyon alanları
- **İç Mekan**: Kabinler, salon ve yaşam alanları
- **Güverteler**: Ana güverte, güneşlenme alanları
- **Özel Alanlar**: Jakuzi, bar, yemek alanları
- **Teknik Detaylar**: Motor, ekipman ve özel özellikler
- **Marina Görünümü**: Yatın marina içindeki görünümü

### Yat Fotoğrafçılığının Zorlukları

- **Dar Mekanlar**: Kompakt alanların ferah gösterilmesi
- **Deniz Koşulları**: Su üzerinde çalışma ve denge
- **Işık Yönetimi**: Deniz yansımaları ve doğal ışık kullanımı
- **Güvenlik**: Denizcilik güvenlik kurallarına uyum
- **Özel Açılar**: Yat mimarisine özel çekim teknikleri

### Çalışma Sürecimiz

1. **Ön İnceleme**: Yatın özelliklerini ve çekim alanlarını belirleme
2. **Hava Koşulları**: En uygun hava ve deniz koşullarının belirlenmesi
3. **Koordinasyon**: Yat sahibi ve mürettebat ile çalışma planı
4. **Çekim**: Tüm alanların profesyonel fotoğraflanması
5. **Drone Çekimi**: Havadan yat ve çevre görünümü
6. **Post-Prodüksiyon**: Kalite optimizasyonu

### Kullanım Alanları

- **Yat Kiralama**: Charter şirketleri için pazarlama görselleri
- **Satış**: Brokerlar için yat tanıtım fotoğrafları
- **Marina Tanıtımı**: Marina tesislerinin tanıtımı
- **Sosyal Medya**: Yat sahipleri için kişisel görseller
- **Dergi ve Katalog**: Denizcilik yayınları için içerik
        '
    ],
    'konut-projeleri-fotografciligi' => [
        'description' => 'Konut kompleksleri ve rezidans projeleri için pazarlama odaklı profesyonel fotoğrafçılık.',
        'image' => '/assets/images/portfolio-4.jpg',
        'content' => '
## Konut Projeleri Fotoğrafçılığı - Rezidans ve Kompleksler

Antalya ve Muğla bölgesinde yeni konut projeleri, rezidanslar ve konut kompleksleri için pazarlama odaklı profesyonel fotoğrafçılık hizmetleri sunuyoruz.

### Konut Projeleri Fotoğrafçılığı Hizmetlerimiz

- **Dış Cephe**: Bina mimarisinin öne çıkan özellikleri
- **Örnek Daireler**: Showroom ve örnek daire çekimleri
- **Ortak Alanlar**: Lobi, bahçe, havuz ve sosyal tesisler
- **Peyzaj**: Çevre düzenlemesi ve bahçe alanları
- **Lokasyon**: Çevre avantajları ve manzara görünümleri
- **İnşaat Süreci**: İnşaat aşaması belgeleme çekimleri
- **Teslim Sonrası**: Tamamlanmış projenin çekimi

### Konut Projeleri İçin Özel Yaklaşım

- **Pazarlama Odaklı**: Satış ve kiralama için optimize edilmiş görseller
- **Yaşam Tarzı Vurgusu**: Projenin sunduğu yaşam kalitesini gösterme
- **Özellik Vurgusu**: Projenin öne çıkan özelliklerini belirginleştirme
- **Çevre Entegrasyonu**: Lokasyon avantajlarını vurgulama
- **Farklı Sezonlar**: Yaz ve kış sezonu görselleri

### Çalışma Sürecimiz

1. **Proje İncelemesi**: Proje özellikleri ve pazarlama hedefleri
2. **Çekim Planlaması**: Tüm alanların çekim stratejisi
3. **Koordinasyon**: Müteahhit ve pazarlama ekibi ile çalışma
4. **Profesyonel Çekim**: Tüm alanların detaylı fotoğraflanması
5. **Drone Çekimi**: Havadan proje ve çevre görünümü
6. **Post-Prodüksiyon**: Pazarlama için optimize edilmiş görseller
7. **Teslimat**: Farklı formatlarda görsel paketi

### Pazarlama Kullanımı

- **Satış Ofisleri**: Showroom ve satış noktaları
- **Web Sitesi**: Proje tanıtım sayfaları
- **Broşür ve Katalog**: Basılı pazarlama materyalleri
- **Sosyal Medya**: Proje tanıtım içerikleri
- **Emlak Portalları**: Online proje tanıtımları
- **Reklam Kampanyaları**: Reklam görselleri
        '
    ],
    'ofis-fotografciligi' => [
        'description' => 'Kurumsal ofislerin modern ve profesyonel görünümünü fotoğraflarla yansıtıyoruz.',
        'image' => '/assets/images/portfolio-5.jpg',
        'content' => '
## Ofis Fotoğrafçılığı - Kurumsal Görünüm

Antalya ve Muğla bölgesinde kurumsal ofislerin modern, profesyonel ve çalışma dostu atmosferini profesyonel fotoğraflarla yansıtıyoruz.

### Ofis Fotoğrafçılığı Hizmetlerimiz

- **Çalışma Alanları**: Açık ofis ve kapalı ofis alanları
- **Toplantı Salonları**: Konferans ve toplantı odaları
- **Ortak Alanlar**: Lobi, resepsiyon ve bekleme alanları
- **Özel Ofisler**: Yönetim ve VIP ofisler
- **Sosyal Alanlar**: Yemekhane, dinlenme ve rekreasyon alanları
- **Teknik Altyapı**: Teknoloji ve altyapı vurgusu
- **Peyzaj ve Dış Görünüm**: Bina dış cephesi ve çevre

### Ofis Fotoğrafçılığının Önemi

- **Kurumsal İmaj**: Profesyonel görünüm ile marka değeri
- **İnsan Kaynakları**: İş başvuruları için çekici görseller
- **Kurumsal İletişim**: Web sitesi ve tanıtım materyalleri
- **Yatırımcı İlişkileri**: Kurumsal sunumlar için görseller
- **Çalışan Morali**: Çalışma ortamının değerini gösterme

### Çalışma Sürecimiz

1. **Koordinasyon**: Çalışma saatlerine uygun çekim planlaması
2. **Alan Hazırlığı**: Çalışan rahatsızlığını minimize etme
3. **Profesyonel Çekim**: Tüm alanların detaylı fotoğraflanması
4. **Minimal İş Kesintisi**: Hızlı ve verimli çekim
5. **Post-Prodüksiyon**: Kurumsal standartlara uygun düzenleme
6. **Teslimat**: Farklı kullanım alanları için formatlar

### Kullanım Alanları

- **Kurumsal Web Sitesi**: Şirket tanıtım sayfaları
- **İnsan Kaynakları**: Kariyer sayfaları ve iş ilanları
- **Kurumsal Sunumlar**: Yatırımcı ve partner sunumları
- **Sosyal Medya**: LinkedIn ve kurumsal hesaplar
- **Broşür ve Katalog**: Kurumsal tanıtım materyalleri
- **Basın Bültenleri**: Medya için görsel içerik
        '
    ],
    'is-merkezi-fotografciligi' => [
        'description' => 'İş merkezleri ve ticari komplekslerin profesyonel görünümünü fotoğraflarla yansıtıyoruz.',
        'image' => '/assets/images/portfolio-6.jpg',
        'content' => '
## İş Merkezi Fotoğrafçılığı - Ticari Kompleksler

Antalya ve Muğla bölgesinde iş merkezleri, ticari kompleksler ve ofis binalarının profesyonel görünümünü fotoğraflarla yansıtıyoruz.

### İş Merkezi Fotoğrafçılığı Hizmetlerimiz

- **Bina Dış Cephe**: İş merkezinin mimari özellikleri
- **Lobi ve Giriş**: Karşılama ve resepsiyon alanları
- **Ortak Kullanım Alanları**: Toplantı salonları, yemekhane
- **Park Alanları**: Otopark ve çevre düzenlemesi
- **Ofis Alanları**: Kiralanabilir ofis alanları
- **Teknik Altyapı**: Asansör, güvenlik sistemleri
- **Peyzaj**: Çevre düzenlemesi ve bahçe alanları

### İş Merkezi Fotoğrafçılığının Önemi

- **Kiralama Pazarlaması**: Ofis kiralama için çekici görseller
- **Kurumsal İmaj**: İş merkezinin profesyonel görünümü
- **Yatırımcı Sunumları**: Yatırımcılar için tanıtım görselleri
- **Web Sitesi**: İş merkezi tanıtım sayfaları
- **Broşür ve Katalog**: Pazarlama materyalleri

### Çalışma Sürecimiz

1. **Tesis İncelemesi**: Tüm alanların analizi
2. **Çekim Planlaması**: İş saatlerine uygun zamanlama
3. **Koordinasyon**: Kiracı ve yönetim ile çalışma planı
4. **Profesyonel Çekim**: Tüm alanların detaylı fotoğraflanması
5. **Post-Prodüksiyon**: Kalite optimizasyonu
6. **Teslimat**: Pazarlama için hazır görseller
        '
    ],
    'ticari-alan-fotografciligi' => [
        'description' => 'Mağaza, showroom ve ticari işletmelerin çekici görsellerini profesyonelce üretiyoruz.',
        'image' => '/assets/images/portfolio-1.jpg',
        'content' => '
## Ticari Alan Fotoğrafçılığı - Perakende ve Showroom

Antalya ve Muğla bölgesinde mağaza, showroom, market ve ticari işletmelerin çekici görsellerini profesyonelce üretiyoruz.

### Ticari Alan Fotoğrafçılığı Hizmetlerimiz

- **Mağaza İç Mekan**: Ürün sunumu ve vitrin çekimleri
- **Showroom**: Ürün sergileme alanları
- **Market ve Süpermarket**: Perakende mekanları
- **Mağaza Dış Cephe**: Vitrin ve cephe görünümü
- **Ürün Fotoğrafçılığı**: Ürün tanıtım çekimleri
- **Vitrin Düzenleme**: Vitrin sunumu ve düzenleme
- **Sosyal Alanlar**: Müşteri bekleme ve dinlenme alanları

### Ticari Alan Fotoğrafçılığının Özellikleri

- **Satış Odaklı**: Ürünleri çekici gösterme
- **Marka Kimliği**: İşletmenin marka değerini yansıtma
- **Müşteri Çekici**: Potansiyel müşterileri cezbetme
- **Online Satış**: E-ticaret için ürün görselleri
- **Pazarlama**: Reklam ve tanıtım materyalleri

### Çalışma Sürecimiz

1. **İşletme İncelemesi**: Mağaza ve ürün yelpazesinin analizi
2. **Çekim Planlaması**: Müşteri trafiğine uygun zamanlama
3. **Ürün Düzenleme**: Ürünlerin en iyi şekilde görünmesi
4. **Profesyonel Çekim**: İç ve dış mekan detaylı fotoğraflama
5. **Post-Prodüksiyon**: Ürün renklerinin doğru yansıtılması
6. **Teslimat**: Farklı platformlar için formatlar

### Kullanım Alanları

- **E-ticaret**: Online mağaza ürün görselleri
- **Sosyal Medya**: Instagram, Facebook içerikleri
- **Web Sitesi**: Mağaza tanıtım sayfaları
- **Katalog**: Ürün katalogları
- **Reklam**: Broşür, poster ve reklam görselleri
- **Vitrin Tasarımı**: Vitrin düzenleme referansı
        '
    ],
    'pansiyon-fotografciligi' => [
        'description' => 'Pansiyon ve butik konaklama tesislerinin samimi atmosferini fotoğraflarla gösteriyoruz.',
        'image' => '/assets/images/portfolio-1.jpg',
        'content' => '
## Pansiyon Fotoğrafçılığı - Samimi Konaklama

Antalya ve Muğla bölgesinde pansiyon, butik otel ve küçük ölçekli konaklama tesislerinin samimi ve sıcak atmosferini profesyonel fotoğraflarla yansıtıyoruz.

### Pansiyon Fotoğrafçılığı Hizmetlerimiz

- **Oda Çekimleri**: Farklı oda tiplerinin samimi görünümü
- **Ortak Alanlar**: Kahvaltı salonu, oturma alanları
- **Bahçe ve Teras**: Dış mekan dinlenme alanları
- **Mutfak ve Yemek Alanı**: Kahvaltı ve yemek sunumu
- **Dış Görünüm**: Bina cephesi ve çevre düzenlemesi
- **Detay Çekimleri**: Özel dekorasyon ve karakteristik özellikler
- **Çevre Görünümü**: Lokasyon avantajları ve manzara

### Pansiyon Fotoğrafçılığının Özellikleri

- **Samimi Atmosfer**: Sıcak ve konuksever hava yaratma
- **Kişisel Dokunuş**: Pansiyonun özel karakterini vurgulama
- **Yerel Kültür**: Bölgenin kültürel özelliklerini yansıtma
- **Sade Görünüm**: Abartısız, doğal görseller
- **Misafir Odaklı**: Konukların rahatını vurgulama

### Çalışma Sürecimiz

1. **Keşif Ziyareti**: Pansiyonun özel karakterini anlama
2. **Hikaye Planlama**: Pansiyonun hikayesini fotoğraflarla anlatma
3. **Samimi Çekim**: Sıcak ve konuksever atmosfer yakalama
4. **Detay Vurgusu**: Özel tasarım ve dekorasyon öğeleri
5. **Post-Prodüksiyon**: Sıcak renk tonları ve atmosfer vurgusu

### Pazarlama Desteği

- **Booking.com**: Oda ve tesis fotoğrafları
- **Airbnb**: Profil ve galeri görselleri
- **Web Sitesi**: Pansiyon tanıtım sayfaları
- **Sosyal Medya**: Instagram ve Facebook içerikleri
- **Yerel Turizm**: Yerel turizm ofisleri için görseller
- **Broşür**: Basılı tanıtım materyalleri

### Özel İhtiyaçlar

- **Uygun Bütçe**: Küçük işletmeler için uygun fiyatlandırma
- **Hızlı Teslimat**: Sezon öncesi hızlı görsel ihtiyacı
- **Yerel Bilgi**: Bölgenin özelliklerine hakim ekip
- **Esnek Çalışma**: Pansiyon sahiplerinin programına uyum
        '
    ],
    'termal-tesis-fotografciligi' => [
        'description' => 'Termal oteller ve spa tesislerinin sağlık ve dinlenme alanlarını profesyonelce çekiyoruz.',
        'image' => '/assets/images/portfolio-3.jpg',
        'content' => '
## Termal Tesis Fotoğrafçılığı - Sağlık ve Dinlenme

Antalya ve Muğla bölgesinde termal oteller ve spa tesislerinin sağlık, dinlenme ve tedavi alanlarını profesyonel fotoğraflarla belgeliyoruz.

### Termal Tesis Fotoğrafçılığı Hizmetlerimiz

- **Termal Havuzlar**: Doğal termal su alanları
- **Spa Merkezleri**: Masaj, tedavi ve dinlenme odaları
- **Sauna ve Hamam**: Geleneksel ve modern sauna alanları
- **Tedavi Odaları**: Özel tedavi ve terapi alanları
- **Dinlenme Alanları**: Rahatlama ve meditasyon mekanları
- **Açık ve Kapalı Havuzlar**: Termal su havuzları
- **Peyzaj**: Doğal çevre ve bahçe alanları
- **Otel Odaları**: Termal otel konaklama alanları

### Termal Tesis Fotoğrafçılığının Özellikleri

- **Sakin Atmosfer**: Dinlenme ve rahatlama vurgusu
- **Doğal Işık**: Sakin ve huzurlu atmosfer yaratma
- **Su Vurgusu**: Termal suyun özelliklerini gösterme
- **Sağlık Odaklı**: Sağlık ve wellness vurgusu
- **Lüks ve Konfor**: Premium hizmet kalitesini yansıtma

### Çalışma Sürecimiz

1. **Tesis İncelemesi**: Tüm alanların analizi
2. **Çekim Planlaması**: Misafir rahatsızlığı olmadan zamanlama
3. **Özel İzinler**: Gizlilik ve güvenlik protokollerine uyum
4. **Profesyonel Çekim**: Tüm alanların özenle fotoğraflanması
5. **Atmosfer Yaratma**: Sakin ve huzurlu görsel ton
6. **Post-Prodüksiyon**: Renk düzeltme ve atmosfer vurgusu

### Pazarlama Desteği

- **Booking Platformları**: Rezervasyon siteleri için görseller
- **Web Sitesi**: Tesis tanıtım sayfaları
- **Sağlık Turizmi**: Medikal turizm pazarlaması
- **Sosyal Medya**: Instagram ve Facebook içerikleri
- **Broşür ve Katalog**: Basılı tanıtım materyalleri
- **Wellness Pazarlaması**: Sağlık ve wellness odaklı içerikler
        '
    ]
];

// Store service name before merging (to prevent any potential override)
// Get all services first to ensure we have the correct service name
$allServicesForName = $supabase->select('services', [
    'is_active' => 'eq.true',
    'select' => 'name,slug'
]);

// Find the correct service by slug
$currentServiceName = $service['name'] ?? '';
foreach ($allServicesForName as $s) {
    if ($s['slug'] === $serviceSlug) {
        $currentServiceName = $s['name'];
        $service['name'] = $s['name'];
        break;
    }
}

// Fallback: if still not found, use the service name from database
if (empty($currentServiceName) && isset($service['name'])) {
    $currentServiceName = $service['name'];
}

// Merge service data with extended content (use DB content first, fallback to hardcoded)
$fallbackContent = $serviceContent[$serviceSlug] ?? [];
$galleryImages = [];
if (!empty($service['gallery_images'])) {
    // Parse JSON if it's a string, otherwise use as is
    if (is_string($service['gallery_images'])) {
        $decoded = json_decode($service['gallery_images'], true);
        $galleryImages = is_array($decoded) ? $decoded : [];
    } elseif (is_array($service['gallery_images'])) {
        $galleryImages = $service['gallery_images'];
    }
}
// Build service data - prioritize DB content, but keep fallback if DB is empty
$dbContent = isset($service['content']) ? trim($service['content']) : (isset($service['content_md']) ? trim($service['content_md']) : '');
$finalContent = !empty($dbContent) ? $dbContent : ($fallbackContent['content'] ?? '<p>İçerik hazırlanıyor...</p>');

$dbDescription = isset($service['description']) ? trim($service['description']) : '';
$finalDescription = !empty($dbDescription) ? $dbDescription : ($fallbackContent['description'] ?? ($service['short_intro'] ?? ''));

$dbImage = isset($service['image']) ? trim($service['image']) : (isset($service['image_url']) ? trim($service['image_url']) : '');
$finalImage = !empty($dbImage) ? $dbImage : ($fallbackContent['image'] ?? '/assets/images/default-service.jpg');

$serviceData = [
    'name' => $currentServiceName,
    'slug' => $service['slug'] ?? $serviceSlug,
    'short_intro' => $service['short_intro'] ?? $fallbackContent['short_intro'] ?? '',
    'description' => $finalDescription,
    'content' => $finalContent,
    'image' => $finalImage,
    'gallery_images' => $galleryImages
];

// Ensure service name is preserved
$service['name'] = $currentServiceName;

// Get all services for sidebar
$allServices = $supabase->select('services', [
    'is_active' => 'eq.true',
    'select' => 'name,slug'
]);

// Get all active provinces for location links
$allProvinces = $supabase->select('locations_province', [
    'is_active' => 'eq.true',
    'select' => 'name,slug',
    'order' => 'name'
]);

$pageTitle = e($service['name']) . ' | Profesyonel Mekan Fotoğrafçılığı Hizmetleri';
$pageDescription = $serviceData['description'] ?? e($service['short_intro']);
$canonicalUrl = 'https://mekanfotografcisi.tr/services/' . $serviceSlug;
$pageH1 = e($service['name']);

include __DIR__ . '/../templates/page-header.php';
?>

<main class="seo-page">
    <!-- Breadcrumbs -->
    <section class="breadcrumbs">
        <div class="container">
            <a href="/">Ana Sayfa</a>
            <span>›</span>
            <a href="/services">Hizmetler</a>
            <span>›</span>
            <strong><?= e($service['name']) ?></strong>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="seo-hero">
        <div class="container">
            <h1><?= $pageH1 ?></h1>
            <p><?= e($serviceData['description'] ?? $service['short_intro']) ?></p>
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
                    <?= markdownToHtml($serviceData['content'] ?? '<p>İçerik hazırlanıyor...</p>') ?>
                    
                    <?php if (!empty($serviceData['gallery_images']) && is_array($serviceData['gallery_images'])): ?>
                    <!-- Gallery Section -->
                    <div class="service-gallery" style="margin-top: 40px;">
                        <h2>Galeri</h2>
                        <div class="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                            <?php foreach ($serviceData['gallery_images'] as $index => $imageUrl): ?>
                                <a href="<?= e($imageUrl) ?>" class="glightbox" data-gallery="service-gallery" data-title="<?= e($service['name']) ?> - Görsel <?= $index + 1 ?>">
                                    <img src="<?= e($imageUrl) ?>" alt="<?= e($service['name']) ?> - Görsel <?= $index + 1 ?>" 
                                         style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; cursor: pointer; transition: transform 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                                         onmouseover="this.style.transform='scale(1.05)'" 
                                         onmouseout="this.style.transform='scale(1)'"
                                         loading="lazy">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-card-image">
                            <img src="<?= e($serviceData['image'] ?? '/assets/images/portfolio-1.jpg') ?>" alt="<?= e($service['name']) ?>" loading="lazy">
                        </div>
                        <div class="sidebar-card-content">
                            <h3>Bu Hizmet Hakkında</h3>
                            <p><?= e($serviceData['description'] ?? $service['short_intro']) ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-card">
                        <h3>Hemen Teklif Alın</h3>
                        <p>Bu hizmet için özel fiyat teklifi almak ister misiniz? Uzman ekibimizle iletişime geçin.</p>
                        <a href="/#iletisim" class="btn btn-outline btn-block">İletişime Geçin</a>
                        <a href="tel:+905074677502" class="btn btn-outline btn-block">📞 +90 507 467 75 02</a>
                    </div>

                    <div class="sidebar-card">
                        <div class="sidebar-card-content">
                            <h3>Bu Hizmeti Sunduğumuz Bölgeler</h3>
                            <ul>
                                <?php 
                                $provinceCount = 0;
                                foreach ($allProvinces as $province): 
                                    $provinceCount++;
                                    // Vary anchor text for SEO - mix different formats
                                    if ($provinceCount <= 5) {
                                        // First 5: Full format
                                        $linkText = e($province['name']) . '\'da ' . e($currentServiceName);
                                    } elseif ($provinceCount <= 10) {
                                        // Next 5: Just province name
                                        $linkText = e($province['name']);
                                    } else {
                                        // Rest: Simple format
                                        $linkText = e($province['name']) . ' bölgesi';
                                    }
                                ?>
                                    <li><a href="/locations/<?= e($province['slug']) ?>"><?= $linkText ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <div class="sidebar-card-content">
                            <h3>Diğer Hizmetlerimiz</h3>
                            <ul>
                                <?php foreach ($allServices as $otherService): ?>
                                    <?php if ($otherService['slug'] !== $serviceSlug): ?>
                                    <li><a href="/services/<?= e($otherService['slug']) ?>"><?= e($otherService['name']) ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
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
            <h2><?= e($service['name']) ?> İçin Hemen İletişime Geçin!</h2>
            <p>Bu hizmet konusunda uzman ekibimizle projelerinizi hayata geçirin.</p>
            <div class="cta-buttons">
                <a href="tel:+905074677502" class="btn btn-outline">📞 +90 507 467 75 02</a>
                <a href="/#iletisim" class="btn btn-primary">Ücretsiz Teklif Al</a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../templates/page-footer.php'; ?>
