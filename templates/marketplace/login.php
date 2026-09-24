<?php
/**
 * Unified freelancer/client login (/giris)
 * Single form posting to api/auth.php?action=login, which already resolves
 * admin/freelancer/client by email. Stores the JWT + role in localStorage and
 * redirects to the right dashboard.
 */
$pageTitle = 'Giriş Yap';
$pageDescription = 'Fotoğrafçı veya müşteri hesabınızla giriş yapın.';
$pageRobots = 'noindex, follow';
include __DIR__ . '/../page-header.php';
?>

<main id="main" class="grid flex-1 lg:grid-cols-2">
    <div class="flex items-start justify-center px-4 py-12 sm:px-6 md:py-20 lg:items-center">
        <div class="w-full max-w-sm">
            <h1 class="h-section">Giriş yap</h1>
            <p class="mt-2 text-ink-muted">Fotoğrafçı ya da müşteri hesabınla giriş yap.</p>

            <form id="login-form" class="mt-8 space-y-4" novalidate>
                <div>
                    <label for="login-email" class="label">E-posta</label>
                    <input id="login-email" name="email" type="email" required class="input" autocomplete="email" autofocus>
                </div>
                <div>
                    <label for="login-password" class="label">Şifre</label>
                    <input id="login-password" name="password" type="password" required class="input" autocomplete="current-password">
                </div>
                <p id="login-message" class="notice notice-error" role="alert" hidden></p>
                <button type="submit" class="btn btn-primary w-full py-3">Giriş yap</button>
            </form>

            <div class="mt-8 space-y-2 border-t border-line pt-6 text-sm text-ink-muted">
                <p>Fotoğrafçı mısın? <a href="/kayit/fotografci" class="font-semibold text-brand-700 hover:underline">Kolektife katıl</a></p>
                <p>Hesabın yok mu? <a href="/kayit/musteri" class="font-semibold text-brand-700 hover:underline">Müşteri hesabı oluştur</a></p>
            </div>
        </div>
    </div>
    <?php
    $asideTitle = 'Mekan sahiplerini ve fotoğrafçıları buluşturuyoruz.';
    $asidePoints = ['Taleplerini ve tekliflerini tek yerden takip et', 'Açık çekim taleplerine panelinden eriş', 'Profilini ve portfolyonu yönet'];
    include __DIR__ . '/../partials/auth-aside.php';
    ?>
</main>

<script>
    document.getElementById('login-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = e.target;
        const msgEl = document.getElementById('login-message');
        const btn = form.querySelector('button[type="submit"]');
        const fail = text => { msgEl.textContent = text; msgEl.hidden = false; };
        msgEl.hidden = true;

        if (!form.email.value.trim() || !form.email.checkValidity() || !form.password.value) {
            fail('E-posta ve şifreni kontrol et.');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Giriş yapılıyor…';
        fetch('/api/auth.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.fromEntries(new FormData(form).entries()))
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    fail(res.error || 'Giriş başarısız.');
                    return;
                }
                localStorage.setItem('mf_token', res.token);
                localStorage.setItem('mf_role', res.user.role);
                if (res.user.role === 'freelancer') {
                    window.location.href = '/panel';
                } else if (res.user.role === 'client') {
                    window.location.href = '/musteri';
                } else {
                    window.location.href = '/admin/';
                }
            })
            .catch(() => fail('Bağlantı hatası, lütfen tekrar dene.'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Giriş yap';
            });
    });
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
