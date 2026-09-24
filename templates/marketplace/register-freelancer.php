<?php
/**
 * Freelancer self-service registration (/kayit/fotografci)
 * Posts to api/register-freelancer.php, then auto-logs in and redirects to /panel.
 */
$pageTitle = 'Fotoğrafçı Olarak Katıl';
$pageDescription = 'Bağımsız fotoğrafçı olarak kayıt olun, açık çekim taleplerini görün ve kendi işinizi büyütün.';
$pageRobots = 'noindex, follow';
include __DIR__ . '/../page-header.php';
?>

<main id="main" class="grid flex-1 lg:grid-cols-2">
    <div class="flex items-start justify-center px-4 py-12 sm:px-6 md:py-16">
        <div class="w-full max-w-lg">
            <p class="eyebrow">Fotoğrafçılar</p>
            <h1 class="h-section mt-3">Kolektife katıl</h1>
            <p class="mt-2 text-ink-muted">Kayıt ücretsiz. Başvurun incelendikten sonra profilin dizinde yayınlanır ve bölgendeki talepleri görmeye başlarsın.</p>

            <form id="register-form" class="mt-8 space-y-8" novalidate>
                <fieldset class="space-y-4">
                    <legend class="mb-4 text-sm font-semibold uppercase tracking-wider text-ink-muted">Hesap</legend>
                    <div>
                        <label for="rf-name" class="label">Ad soyad</label>
                        <input id="rf-name" name="name" required class="input" autocomplete="name">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="rf-email" class="label">E-posta</label>
                            <input id="rf-email" name="email" type="email" required class="input" autocomplete="email">
                        </div>
                        <div>
                            <label for="rf-phone" class="label">Telefon</label>
                            <input id="rf-phone" name="phone" type="tel" required class="input" autocomplete="tel">
                        </div>
                    </div>
                    <div>
                        <label for="rf-password" class="label">Şifre</label>
                        <input id="rf-password" name="password" type="password" required minlength="8" class="input" autocomplete="new-password">
                        <p class="hint">En az 8 karakter.</p>
                    </div>
                </fieldset>

                <fieldset class="space-y-4">
                    <legend class="mb-4 text-sm font-semibold uppercase tracking-wider text-ink-muted">Profesyonel bilgiler</legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="rf-city" class="label">Şehir</label>
                            <input id="rf-city" name="city" required class="input" autocomplete="address-level1" placeholder="Örn: Antalya">
                        </div>
                        <div>
                            <label for="rf-experience" class="label">Deneyim</label>
                            <select id="rf-experience" name="experience" required class="input">
                                <option value="">Seç</option>
                                <option value="0-1">0-1 yıl</option>
                                <option value="1-3">1-3 yıl</option>
                                <option value="3-5">3-5 yıl</option>
                                <option value="5-10">5-10 yıl</option>
                                <option value="10+">10+ yıl</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <span class="label">Uzmanlık alanların</span>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <?php foreach (photographer_specialties() as $val => $label): ?>
                                <label class="choice py-2.5">
                                    <input type="checkbox" name="specialization" value="<?= e($val) ?>" class="h-4 w-4 rounded border-stone-300 accent-brand-600">
                                    <?= e($label) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <label for="rf-portfolio" class="label">Portfolyo / Instagram <span class="font-normal text-ink-muted">(opsiyonel)</span></label>
                        <input id="rf-portfolio" name="portfolio_url" type="url" class="input" placeholder="https://">
                    </div>
                    <div>
                        <label for="rf-message" class="label">Kendinden bahset <span class="font-normal text-ink-muted">(opsiyonel)</span></label>
                        <textarea id="rf-message" name="message" rows="3" class="input" placeholder="Hangi mekanları çekiyorsun, nerelerde çalışıyorsun?"></textarea>
                    </div>
                </fieldset>

                <div>
                    <p id="register-message" class="notice notice-error mb-4" role="alert" hidden></p>
                    <button type="submit" class="btn btn-primary w-full py-3">Kayıt ol</button>
                    <p class="mt-4 text-sm text-ink-muted">Zaten hesabın var mı? <a href="/giris" class="font-semibold text-brand-700 hover:underline">Giriş yap</a></p>
                </div>
            </form>
        </div>
    </div>
    <?php
    $asideTitle = 'Müşteri aramak yerine çekime odaklan.';
    $asidePoints = ['Bölgene ve uzmanlığına uyan açık talepler', 'İstediğin işi üstlen, aidat yok', 'Portfolyo ve yorumlarla herkese açık profil', 'Kapora ve ödemeler platform üzerinden'];
    include __DIR__ . '/../partials/auth-aside.php';
    ?>
</main>

<script>
    document.getElementById('register-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = e.target;
        const msgEl = document.getElementById('register-message');
        const btn = form.querySelector('button[type="submit"]');
        const fail = (text, field) => { msgEl.textContent = text; msgEl.hidden = false; if (field) field.focus(); };
        msgEl.hidden = true;

        const invalid = Array.from(form.querySelectorAll('input:not([type=checkbox]), select')).find(f => (f.required && !f.value.trim()) || !f.checkValidity());
        if (invalid) {
            const messages = { password: 'Şifre en az 8 karakter olmalı.', email: 'Geçerli bir e-posta adresi gir.', portfolio_url: 'Portfolyo linki https:// ile başlamalı.' };
            fail(messages[invalid.name] || 'Lütfen zorunlu alanları doldur.', invalid);
            return;
        }

        const specialization = Array.from(form.querySelectorAll('input[name="specialization"]:checked')).map(c => c.value);
        if (!specialization.length) {
            fail('En az bir uzmanlık alanı seç.', form.querySelector('input[name="specialization"]'));
            return;
        }

        const data = {
            name: form.name.value,
            email: form.email.value,
            password: form.password.value,
            phone: form.phone.value,
            city: form.city.value,
            experience: form.experience.value,
            specialization,
            portfolio_url: form.portfolio_url.value,
            message: form.message.value,
        };

        btn.disabled = true;
        btn.textContent = 'Kaydediliyor…';
        fetch('/api/register-freelancer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    fail(res.error || 'Kayıt başarısız.');
                    return;
                }
                localStorage.setItem('mf_token', res.token);
                localStorage.setItem('mf_role', 'freelancer');
                window.location.href = '/panel';
            })
            .catch(() => fail('Bağlantı hatası, lütfen tekrar dene.'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Kayıt ol';
            });
    });
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
