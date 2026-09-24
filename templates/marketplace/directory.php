<?php
/**
 * Public Photographer Directory (/fotografcilar)
 *
 * Server-rendered with GET filters (?bolge=Antalya&uzmanlik=otel) so results
 * are indexable and filter links can be shared. Filtering mirrors
 * api/directory/freelancers.php (city or working region, specialization).
 */
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

$filterRegion = trim((string) ($_GET['bolge'] ?? ''));
$filterSpecialty = (string) ($_GET['uzmanlik'] ?? '');
if (!isset(photographer_specialties()[$filterSpecialty])) {
    $filterSpecialty = '';
}

$db = new DatabaseClient();
$photographers = [];
try {
    $photographers = $db->select('freelancer_applications', [
        'status' => 'approved',
        'is_public' => true,
        'order' => 'rating_avg DESC, created_at DESC',
        'limit' => 500,
    ]);
} catch (Exception $e) {
    error_log('Directory fetch failed (migrations pending?): ' . $e->getMessage());
}

if ($filterRegion !== '') {
    $photographers = array_values(array_filter($photographers, function ($f) use ($filterRegion) {
        if (mb_stripos($f['city'] ?? '', $filterRegion) !== false) {
            return true;
        }
        foreach (json_decode($f['working_regions'] ?? '[]', true) ?: [] as $region) {
            if (isset($region['province_name']) && mb_stripos($region['province_name'], $filterRegion) !== false) {
                return true;
            }
        }
        return false;
    }));
}
if ($filterSpecialty !== '') {
    $photographers = array_values(array_filter($photographers, function ($f) use ($filterSpecialty) {
        return in_array($filterSpecialty, json_decode($f['specialization'] ?? '[]', true) ?: [], true);
    }));
}

$provinces = [];
try {
    $provinces = $db->select('locations_province', ['is_active' => true, 'order' => 'name ASC', 'limit' => 100]);
} catch (Exception $e) {
    error_log('Directory provinces fetch failed: ' . $e->getMessage());
}

$hasFilter = $filterRegion !== '' || $filterSpecialty !== '';
$pageTitle = $filterRegion !== '' ? $filterRegion . ' Mekan Fotoğrafçıları' : 'Fotoğrafçılar';
$pageDescription = 'Kolektifimizdeki onaylı, bağımsız mekan fotoğrafçılarını keşfedin; bölge ve uzmanlığa göre filtreleyip doğrudan iletişime geçin.';
if ($hasFilter) {
    $pageRobots = 'noindex, follow';
    $appPage = false;
}
include __DIR__ . '/../page-header.php';

$heroEyebrow = 'Kolektif';
$heroTitle = $filterRegion !== '' ? $filterRegion . ' fotoğrafçıları' : 'Fotoğrafçıları keşfet';
$heroLead = 'Başvurusu incelenip onaylanan bağımsız mekan fotoğrafçıları. Profillerine bak, doğrudan iletişime geç ya da tek talep ile hepsinden teklif al.';
$heroCrumbs = [['href' => '/fotografcilar', 'label' => 'Fotoğrafçılar']];
?>

<main id="main">
    <?php include __DIR__ . '/../partials/page-hero.php'; ?>

    <section class="section pt-8 md:pt-10">
        <div class="container-page">
            <form method="get" action="/fotografcilar" id="directory-filters" class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="filter-region" class="label px-1 text-xs text-ink-muted">Bölge</label>
                    <input id="filter-region" name="bolge" type="text" list="province-options" value="<?= e($filterRegion) ?>" placeholder="Şehir veya bölge" class="input">
                    <datalist id="province-options">
                        <?php foreach ($provinces as $province): ?>
                            <option value="<?= e($province['name']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="flex-1">
                    <label for="filter-specialty" class="label px-1 text-xs text-ink-muted">Uzmanlık</label>
                    <select id="filter-specialty" name="uzmanlik" class="input" onchange="this.form.submit()">
                        <option value="">Tüm uzmanlıklar</option>
                        <?php foreach (photographer_specialties() as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $filterSpecialty === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-dark py-3"><?= icon('search', 'h-4 w-4') ?> Ara</button>
                <?php if ($hasFilter): ?>
                    <a href="/fotografcilar" class="btn btn-ghost py-3">Temizle</a>
                <?php endif; ?>
            </form>

            <p class="mt-6 text-sm text-ink-muted" aria-live="polite">
                <?= count($photographers) ?> fotoğrafçı<?= $filterSpecialty ? ' · ' . e(specialty_label($filterSpecialty)) : '' ?><?= $filterRegion !== '' ? ' · ' . e($filterRegion) : '' ?>
            </p>

            <?php if ($photographers): ?>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($photographers as $photographer): ?>
                        <?= photographer_card($photographer) ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card mt-4 p-8 text-center md:p-12">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-ink-muted"><?= icon('users') ?></span>
                    <h2 class="h-card mt-4">Bu kriterlere uyan fotoğrafçı henüz yok</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm text-ink-soft">Talebini yine de ilet; bölgene hizmet verebilecek fotoğrafçıları senin için bulalım.</p>
                    <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                        <button type="button" onclick='openQuoteWizard(<?= json_encode($filterSpecialty ?: null) ?>, <?= json_encode($filterRegion ?: null) ?>)' class="btn btn-primary">Teklif iste</button>
                        <?php if ($hasFilter): ?>
                            <a href="/fotografcilar" class="btn btn-outline">Tüm fotoğrafçılar</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-12 flex flex-col items-start justify-between gap-4 rounded-2xl bg-brand-50 p-6 ring-1 ring-inset ring-brand-100 md:flex-row md:items-center">
                <div>
                    <h2 class="font-semibold">Tek tek yazmakla uğraşma</h2>
                    <p class="mt-1 text-sm text-ink-soft">Tek bir talep oluştur, uygun fotoğrafçılar sana teklif göndersin.</p>
                </div>
                <button type="button" onclick='openQuoteWizard(<?= json_encode($filterSpecialty ?: null) ?>, <?= json_encode($filterRegion ?: null) ?>)' class="btn btn-primary shrink-0">Ücretsiz teklif al</button>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../page-footer.php'; ?>
