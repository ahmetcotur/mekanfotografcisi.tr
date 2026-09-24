<?php
/**
 * Client self-service registration (/kayit/musteri)
 * Posts to api/register-client.php, then auto-logs in and redirects to /musteri.
 */
$pageTitle = 'Müşteri Hesabı Oluştur';
$pageDescription = 'Hesap oluşturun ve çekim taleplerinizi kolayca takip edin.';
$pageRobots = 'noindex, follow';
include __DIR__ . '/../page-header.php';
?>

<main id="main" class="grid flex-1 lg:grid-cols-2">
    <div class="flex items-start justify-center px-4 py-12 sm:px-6 md:py-20 lg:items-center">
        <div class="w-full max-w-sm">
            <p class="eyebrow">Mekan sahipleri</p>
            <h1 class="h-section mt-3">Müşteri hesabı oluştur</h1>
            <p class="mt-2 text-ink-muted">Taleplerini ve seninle çalışan fotoğrafçıyı tek yerden takip et.</p>

            <form id="register-form" class="mt-8 space-y-4" novalidate>
                <div>
                    <label for="rc-name" class="label">Ad soyad</label>
                    <input id="rc-name" name="name" required class="input" autocomplete="name">
                </div>
                <div>
                    <label for="rc-email" class="label">E-posta</label>
                    <input id="rc-email" name="email" type="email" required class="input" autocomplete="email">
                </div>
                <div>
                    <label for="rc-phone" class="label">Telefon <span class="font-normal text-ink-muted">(opsiyonel)</span></label>
                    <input id="rc-phone" name="phone" type="tel" class="input" autocomplete="tel">
                </div>
                <div>
                    <label for="rc-password" class="label">Şifre</label>
                    <input id="rc-password" name="password" type="password" required minlength="8" class="input" autocomplete="new-password">
                    <p class="hint">En az 8 karakter.</p>
                </div>
                <p id="register-message" class="notice notice-error" role="alert" hidden></p>
                <button type="submit" class="btn btn-primary w-full py-3">Hesap oluştur</button>
            </form>

            <p class="mt-8 border-t border-line pt-6 text-sm text-ink-muted">
                Zaten hesabın var mı? <a href="/giris" class="font-semibold text-brand-700 hover:underline">Giriş yap</a>
            </p>
        </div>
    </div>
    <?php
    $asideTitle = 'Mekanın için doğru fotoğrafçı, tek talepte.';
    $asidePoints = ['Talebin bölgendeki uygun fotoğrafçılara iletilir', 'Teklifleri ve durumlarını panelinden takip et', 'Çekimden sonra fotoğrafçını değerlendir'];
    include __DIR__ . '/../partials/auth-aside.php';
    ?>
</main>

<script>
    document.getElementById('register-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = e.target;
        const msgEl = document.getElementById('register-message');
        const btn = form.querySelector('button[type="submit"]');
        const fail = text => { msgEl.textContent = text; msgEl.hidden = false; };
        msgEl.hidden = true;

        const invalid = Array.from(form.querySelectorAll('input')).find(f => (f.required && !f.value.trim()) || !f.checkValidity());
        if (invalid) {
            fail(invalid.name === 'password' ? 'Şifre en az 8 karakter olmalı.' : 'Lütfen zorunlu alanları doğru doldur.');
            invalid.focus();
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Oluşturuluyor…';
        fetch('/api/register-client.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.fromEntries(new FormData(form).entries()))
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    fail(res.error || 'Kayıt başarısız.');
                    return;
                }
                localStorage.setItem('mf_token', res.token);
                localStorage.setItem('mf_role', 'client');
                window.location.href = '/musteri';
            })
            .catch(() => fail('Bağlantı hatası, lütfen tekrar dene.'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Hesap oluştur';
            });
    });
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
