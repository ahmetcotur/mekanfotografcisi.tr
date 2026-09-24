<?php
/**
 * Unified freelancer/client login (/giris)
 * Single form posting to api/auth.php?action=login, which already resolves
 * admin/freelancer/client by email. Stores the JWT + role in localStorage and
 * redirects to the right dashboard.
 */
$pageTitle = 'Giriş Yap';
$pageDescription = 'Fotoğrafçı veya müşteri hesabınızla giriş yapın.';
include __DIR__ . '/../page-header.php';
?>

<main class="pt-40 pb-24 min-h-screen flex items-start justify-center">
    <div class="max-w-md w-full mx-4 bg-white rounded-3xl border border-slate-100 shadow-xl p-8 md:p-10">
        <h1 class="text-2xl font-heading font-black text-slate-900 mb-2">Giriş Yap</h1>
        <p class="text-slate-400 text-sm mb-8">Fotoğrafçı veya müşteri hesabınızla devam edin.</p>

        <form id="login-form" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-600 mb-1">E-posta</label>
                <input name="email" type="email" required class="w-full px-4 py-3 rounded-xl border border-slate-200">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 mb-1">Şifre</label>
                <input name="password" type="password" required class="w-full px-4 py-3 rounded-xl border border-slate-200">
            </div>
            <button type="submit" class="w-full py-4 bg-brand-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brand-700 transition-all">
                Giriş Yap
            </button>
            <p id="login-message" class="text-sm font-medium"></p>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-sm text-slate-400 space-y-2">
            <p>Fotoğrafçı mısınız? <a href="/kayit/fotografci" class="text-brand-600 font-bold">Kolektife katılın</a></p>
        </div>
    </div>
</main>

<script>
    document.getElementById('login-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target).entries());
        const msgEl = document.getElementById('login-message');

        fetch('/api/auth.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    msgEl.textContent = res.error || 'Giriş başarısız';
                    msgEl.className = 'text-sm font-medium text-red-600';
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
            .catch(() => {
                msgEl.textContent = 'Bir hata oluştu, lütfen tekrar deneyin.';
                msgEl.className = 'text-sm font-medium text-red-600';
            });
    });
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
