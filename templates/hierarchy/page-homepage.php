<?php
/**
 * Homepage.
 *
 * Built entirely from this template (the old version string-patched the
 * `homepage` post's stored HTML with ~15 regexes, which broke whenever that
 * HTML changed). $post is still used for the SEO title/description fallback.
 */
$pageTitle = 'Mekan Fotoğrafçısı Bul veya Fotoğrafçı Olarak Katıl';
$pageDescription = 'Mekanını çektirmek isteyenler için bölgesine ve kategorisine uygun fotoğrafçı bulma, fotoğrafçılar için ise açık çekim taleplerine erişme platformu.';
include __DIR__ . '/../page-header.php';

global $db;
if (!$db) {
    $db = new DatabaseClient();
}

$services = published_services($db);

// Guarded: is_public/rating_avg only exist once the marketplace migrations
// (scripts/migrations/20260201_*) have been applied. Skip the section rather
// than take the homepage down.
$featuredPhotographers = [];
try {
    $featuredPhotographers = $db->select('freelancer_applications', [
        'status' => 'approved',
        'is_public' => true,
        'order' => 'rating_avg DESC, created_at DESC',
        'limit' => 4,
    ]);
} catch (Exception $e) {
    error_log('Featured photographers fetch failed (migrations pending?): ' . $e->getMessage());
}

$activeProvinces = [];
try {
    $activeProvinces = $db->select('locations_province', ['is_active' => true, 'order' => 'name ASC', 'limit' => 100]);
} catch (Exception $e) {
    error_log('Homepage provinces fetch failed: ' . $e->getMessage());
}

// Photos: curated Pexels set from the admin, local files as a fallback.
$photos = array_values(array_filter(array_map('photo_src', get_random_pexels_photos(12))));
$localPhotos = ['/assets/images/hero-bg.jpg', '/assets/images/portfolio-1.jpg', '/assets/images/portfolio-2.jpg', '/assets/images/portfolio-3.jpg', '/assets/images/portfolio-4.jpg', '/assets/images/portfolio-6.jpg'];
$photoAt = function ($i) use ($photos, $localPhotos) {
    return $photos[$i] ?? $localPhotos[$i % count($localPhotos)];
};

$serviceIcons = ['otel' => 'hotel', 'pansiyon' => 'hotel', 'termal' => 'hotel', 'yemek' => 'utensils', 'restoran' => 'utensils', 'emlak' => 'home', 'villa' => 'home', 'konut' => 'home', 'yat' => 'sparkles', 'ofis' => 'briefcase', 'is-merkezi' => 'briefcase', 'ticari' => 'briefcase', 'mimari' => 'building', 'lifestyle' => 'sparkles'];
$serviceIcon = function ($slug) use ($serviceIcons) {
    foreach ($serviceIcons as $needle => $icon) {
        if (strpos($slug, $needle) !== false) {
            return $icon;
        }
    }
    return 'camera';
};
$serviceHref = function ($slug) {
    return '/hizmetlerimiz/' . preg_replace('#^hizmetlerimiz/#', '', $slug);
};

$faqs = [
    ['Teklif almak ücretli mi?', 'Hayır. Talep oluşturmak ve teklif almak ücretsizdir; gelen tekliflerden birini kabul etmek zorunda değilsin.'],
    ['Fotoğrafçılar nasıl seçiliyor?', 'Kolektife katılan her fotoğrafçının başvurusu ve portfolyosu incelenir. Onaylanan fotoğrafçılar profillerinde uzmanlık alanlarını, çalıştıkları bölgeleri ve müşteri değerlendirmelerini gösterir.'],
    ['Talebim kimlere iletiliyor?', 'Talebin, çekim yerine ve istediğin hizmete göre eşleşen fotoğrafçılara iletilir. İletişim bilgilerin yalnızca bu fotoğrafçılarla paylaşılır.'],
    ['Fotoğrafları ne zaman teslim alırım?', 'Çoğu mekan çekimi 2-4 iş günü içinde düzenlenmiş olarak teslim edilir. Kesin süreyi çekim öncesinde fotoğrafçınla netleştirirsin.'],
];
?>

<main id="main">
    <!-- Hero -->
    <section class="relative overflow-hidden">
        <div class="container-page grid items-center gap-12 pb-16 pt-10 md:pt-16 lg:grid-cols-12 lg:gap-8 lg:pb-24">
            <div class="lg:col-span-6">
                <p class="eyebrow"><?= icon('camera', 'h-4 w-4') ?> Mekan fotoğrafçılığı platformu</p>
                <h1 class="h-display mt-5">Mekanını, bölgendeki <span class="text-brand-600">doğru fotoğrafçıyla</span> buluştur.</h1>
                <p class="lead mt-6 max-w-xl">
                    Otel, villa, restoran ya da ofis — ne çektireceğini ve nerede olduğunu söyle; onaylı fotoğrafçılar
                    teklifleriyle sana dönsün.
                </p>

                <form class="mt-8 rounded-2xl border border-line bg-white p-2 shadow-soft sm:flex sm:items-center sm:gap-2" onsubmit="event.preventDefault(); openQuoteWizard(this.service.value, this.location.value.trim());">
                    <label class="flex flex-1 items-center gap-3 rounded-xl px-3 py-2 sm:py-1">
                        <span class="text-ink-muted"><?= icon('camera', 'h-5 w-5') ?></span>
                        <span class="flex-1">
                            <span class="block text-xs font-medium text-ink-muted">Ne çektireceksin?</span>
                            <select name="service" class="-ml-1 w-full appearance-none bg-transparent pl-1 text-sm font-semibold focus:outline-none">
                                <option value="mimari">Villa, konut, ofis</option>
                                <option value="otel">Otel & turizm tesisi</option>
                                <option value="yemek">Restoran & yemek</option>
                                <option value="diger">Drone / diğer</option>
                            </select>
                        </span>
                    </label>
                    <span class="mx-3 block h-px bg-line sm:mx-0 sm:h-10 sm:w-px"></span>
                    <label class="flex flex-1 items-center gap-3 rounded-xl px-3 py-2 sm:py-1">
                        <span class="text-ink-muted"><?= icon('map-pin', 'h-5 w-5') ?></span>
                        <span class="flex-1">
                            <span class="block text-xs font-medium text-ink-muted">Nerede?</span>
                            <input name="location" type="text" placeholder="Örn: Kaş, Antalya" class="w-full bg-transparent text-sm font-semibold placeholder:font-normal placeholder:text-stone-400 focus:outline-none">
                        </span>
                    </label>
                    <button type="submit" class="btn btn-primary mt-2 w-full py-3 sm:mt-0 sm:w-auto">
                        Teklif al <?= icon('arrow-right', 'h-4 w-4') ?>
                    </button>
                </form>

                <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-ink-soft">
                    <li class="flex items-center gap-2"><?= icon('check', 'h-4 w-4 text-brand-600') ?> Ücretsiz ve bağlayıcı değil</li>
                    <li class="flex items-center gap-2"><?= icon('check', 'h-4 w-4 text-brand-600') ?> Onaylı fotoğrafçılar</li>
                    <li class="flex items-center gap-2"><?= icon('check', 'h-4 w-4 text-brand-600') ?> <?= count($activeProvinces) ?: 'Birçok' ?> ilde hizmet</li>
                </ul>
            </div>

            <div class="relative lg:col-span-6" aria-hidden="true">
                <div class="grid grid-cols-6 grid-rows-6 gap-3 sm:gap-4" style="aspect-ratio: 6 / 5;">
                    <div class="photo-placeholder col-span-4 row-span-4 overflow-hidden rounded-3xl">
                        <img src="<?= e($photoAt(0)) ?>" alt="" class="h-full w-full object-cover" fetchpriority="high">
                    </div>
                    <div class="photo-placeholder col-span-2 row-span-3 overflow-hidden rounded-3xl">
                        <img src="<?= e($photoAt(1)) ?>" alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="photo-placeholder col-span-2 row-span-3 overflow-hidden rounded-3xl">
                        <img src="<?= e($photoAt(2)) ?>" alt="" class="h-full w-full object-cover">
                    </div>
                    <div class="photo-placeholder col-span-4 row-span-2 overflow-hidden rounded-3xl">
                        <img src="<?= e($photoAt(3)) ?>" alt="" class="h-full w-full object-cover">
                    </div>
                </div>
                <?php if (!empty($featuredPhotographers)): $top = $featuredPhotographers[0]; ?>
                    <div class="absolute -bottom-4 left-4 hidden items-center gap-3 rounded-2xl border border-line bg-white py-3 pl-3 pr-5 shadow-lift sm:flex">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 font-display font-semibold text-brand-800"><?= e(mb_strtoupper(mb_substr($top['name'], 0, 1))) ?></span>
                        <span class="text-sm">
                            <span class="block font-semibold"><?= e($top['name']) ?></span>
                            <span class="text-ink-muted"><?= e($top['city']) ?> · fotoğrafçı</span>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Two sides of the marketplace -->
    <section class="border-y border-line bg-white">
        <div class="container-page grid divide-y divide-line md:grid-cols-2 md:divide-x md:divide-y-0">
            <div class="py-10 md:py-12 md:pr-12">
                <p class="eyebrow">Mekan sahipleri için</p>
                <h2 class="h-card mt-3 text-2xl">Mekanını çektirmek mi istiyorsun?</h2>
                <p class="mt-3 text-ink-soft">Tek bir talep oluştur, bölgende çalışan uygun fotoğrafçılardan teklif al, portfolyolarını karşılaştır ve seç.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="button" onclick="openQuoteWizard()" class="btn btn-primary">Çekim talebi oluştur</button>
                    <a href="/fotografcilar" class="btn btn-outline">Fotoğrafçıları incele</a>
                </div>
            </div>
            <div class="py-10 md:py-12 md:pl-12">
                <p class="eyebrow">Fotoğrafçılar için</p>
                <h2 class="h-card mt-3 text-2xl">Fotoğrafçı mısın? Freelance mi çalışıyorsun?</h2>
                <p class="mt-3 text-ink-soft">Kolektife ücretsiz katıl, uzmanlığına ve bölgene uyan açık çekim taleplerini gör, dilediğini üstlen.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="/kayit/fotografci" class="btn btn-dark">Fotoğrafçı olarak katıl</a>
                    <a href="/nasil-calisir#fotografcilar" class="btn btn-ghost">Nasıl çalışır? <?= icon('arrow-right', 'h-4 w-4') ?></a>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="section">
        <div class="container-page">
            <div class="max-w-2xl">
                <p class="eyebrow">Nasıl çalışır?</p>
                <h2 class="h-section mt-3">Üç adımda doğru fotoğrafçı</h2>
            </div>
            <ol class="mt-10 grid gap-6 md:grid-cols-3">
                <?php foreach ([
                    ['message', 'Talebini oluştur', 'Mekanını, konumunu ve ne zaman çekim istediğini birkaç soruda anlat. 2 dakika sürer.'],
                    ['users', 'Teklifleri karşılaştır', 'Bölgende çalışan ve uzmanlığı uyan fotoğrafçılar sana ulaşır. Portfolyolarına ve yorumlara bak.'],
                    ['camera', 'Çekimi planla', 'Seçtiğin fotoğrafçıyla tarihi netleştir; düzenlenmiş fotoğrafların birkaç gün içinde elinde.'],
                ] as $i => [$ic, $title, $text]): ?>
                    <li class="card p-6">
                        <div class="flex items-center justify-between">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700"><?= icon($ic) ?></span>
                            <span class="font-display text-3xl font-semibold text-stone-200"><?= sprintf('%02d', $i + 1) ?></span>
                        </div>
                        <h3 class="mt-5 font-semibold"><?= e($title) ?></h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink-soft"><?= e($text) ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- Categories -->
    <?php if (!empty($services)): ?>
        <section class="section border-t border-line bg-white">
            <div class="container-page">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <div class="max-w-2xl">
                        <p class="eyebrow">Kategoriler</p>
                        <h2 class="h-section mt-3">Hangi mekanı çektireceksin?</h2>
                    </div>
                    <a href="/hizmetlerimiz" class="btn btn-outline self-start md:self-auto">Tüm hizmetler <?= icon('arrow-right', 'h-4 w-4') ?></a>
                </div>
                <div class="mt-10 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                    <?php foreach (array_slice($services, 0, 8) as $i => $service): ?>
                        <a href="<?= e($serviceHref($service['slug'])) ?>" class="card-link group overflow-hidden">
                            <div class="photo-placeholder aspect-[4/3] overflow-hidden">
                                <img src="<?= e($photoAt($i + 4)) ?>" alt="<?= e($service['title']) ?>" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            </div>
                            <div class="flex items-center gap-3 p-3 sm:p-4">
                                <span class="hidden text-brand-700 sm:block"><?= icon($serviceIcon($service['slug'])) ?></span>
                                <span class="text-sm font-semibold leading-snug sm:text-base"><?= e($service['title']) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Featured photographers -->
    <section class="section border-t border-line">
        <div class="container-page">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div class="max-w-2xl">
                    <p class="eyebrow">Kolektif</p>
                    <h2 class="h-section mt-3">Kolektiften fotoğrafçılar</h2>
                    <p class="mt-3 text-ink-soft">Başvurusu incelenip onaylanan, bağımsız çalışan mekan fotoğrafçıları.</p>
                </div>
                <?php if (!empty($featuredPhotographers)): ?>
                    <a href="/fotografcilar" class="btn btn-outline self-start md:self-auto">Tümünü gör <?= icon('arrow-right', 'h-4 w-4') ?></a>
                <?php endif; ?>
            </div>
            <?php if (!empty($featuredPhotographers)): ?>
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <?php foreach ($featuredPhotographers as $photographer): ?>
                        <?= photographer_card($photographer) ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card mt-10 flex flex-col items-start justify-between gap-4 p-6 md:flex-row md:items-center">
                    <p class="text-ink-soft">Kolektif yeni büyüyor. Bölgendeki ilk fotoğrafçılardan biri ol, talepleri ilk sen gör.</p>
                    <a href="/kayit/fotografci" class="btn btn-dark">Fotoğrafçı olarak katıl</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- For photographers -->
    <section class="section bg-ink text-white">
        <div class="container-page grid items-center gap-12 lg:grid-cols-2">
            <div>
                <p class="eyebrow-light">Fotoğrafçılar için</p>
                <h2 class="h-section mt-3 text-white">Portfolyonu büyüt, işini kendin yönet.</h2>
                <p class="mt-4 max-w-lg text-stone-300">Mekan fotoğrafçılığı yapıyorsan, müşteri aramak yerine çekime odaklan. Bölgendeki talepler panelinde seni bekliyor.</p>
                <a href="/kayit/fotografci" class="btn btn-primary btn-lg mt-8">Ücretsiz katıl <?= icon('arrow-right', 'h-4 w-4') ?></a>
            </div>
            <ul class="grid gap-4 sm:grid-cols-2">
                <?php foreach ([
                    ['map-pin', 'Bölgene uygun işler', 'Uzmanlığına ve çalıştığın illere göre eşleşen talepleri gör.'],
                    ['briefcase', 'Seçim senin', 'İstediğin talebi üstlen, istemediğini geç. Aidat yok.'],
                    ['user', 'Herkese açık profil', 'Portfolyon ve müşteri yorumlarınla dizinde yer al.'],
                    ['wallet', 'Güvenli ödeme', 'Kapora ve ödemeler platform üzerinden takip edilir.'],
                ] as [$ic, $title, $text]): ?>
                    <li class="rounded-2xl border border-white/10 bg-white/5 p-5">
                        <span class="text-brand-300"><?= icon($ic) ?></span>
                        <h3 class="mt-3 font-semibold"><?= e($title) ?></h3>
                        <p class="mt-1 text-sm text-stone-400"><?= e($text) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- Regions -->
    <?php if (!empty($activeProvinces)): ?>
        <section class="section">
            <div class="container-page">
                <div class="max-w-2xl">
                    <p class="eyebrow">Bölgeler</p>
                    <h2 class="h-section mt-3">Hizmet verdiğimiz iller</h2>
                    <p class="mt-3 text-ink-soft">İlçe bazında fotoğrafçı ve hizmet bilgisi için bir il seç.</p>
                </div>
                <div class="mt-8 flex flex-wrap gap-2">
                    <?php foreach ($activeProvinces as $province): ?>
                        <a href="/hizmet-bolgeleri/<?= e($province['slug']) ?>" class="inline-flex items-center gap-2 rounded-full border border-line bg-white px-4 py-2 text-sm font-medium transition hover:border-stone-300 hover:shadow-soft">
                            <?= icon('map-pin', 'h-4 w-4 text-brand-600') ?> <?= e($province['name']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="/hizmet-bolgeleri" class="inline-flex items-center gap-1 rounded-full px-4 py-2 text-sm font-medium text-brand-700 hover:underline">Tüm bölgeler <?= icon('arrow-right', 'h-4 w-4') ?></a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- FAQ -->
    <section class="section border-t border-line bg-white">
        <div class="container-page grid gap-10 lg:grid-cols-3">
            <div>
                <p class="eyebrow">SSS</p>
                <h2 class="h-section mt-3">Merak edilenler</h2>
                <p class="mt-3 text-ink-soft">Başka bir sorun mu var? <a href="/nasil-calisir" class="font-medium text-brand-700 underline underline-offset-2">Nasıl çalışır</a> sayfasına göz at.</p>
            </div>
            <div class="divide-y divide-line border-y border-line lg:col-span-2">
                <?php foreach ($faqs as [$q, $a]): ?>
                    <details class="group py-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold [&::-webkit-details-marker]:hidden">
                            <?= e($q) ?>
                            <span class="text-ink-muted transition group-open:rotate-180"><?= icon('chevron-down') ?></span>
                        </summary>
                        <p class="mt-3 max-w-2xl text-ink-soft"><?= e($a) ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../partials/cta-band.php'; ?>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
