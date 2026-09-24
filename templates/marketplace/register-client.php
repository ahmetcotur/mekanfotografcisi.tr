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

<main class="pt-40 pb-24 min-h-screen flex items-start justify-center">
    <div class="max-w-md w-full mx-4 bg-white rounded-3xl border border-slate-100 shadow-xl p-8 md:p-10">
        <h1 class="text-2xl font-heading font-black text-slate-900 mb-2">Müşteri Hesabı Oluştur</h1>
        <p class="text-slate-400 text-sm mb-8">Taleplerinizi takip etmek ve fotoğrafçınızla iletişimde kalmak için hesap oluşturun.</p>

        <form id="register-form" class="space-y-4">
            <input name="name" required placeholder="Ad Soyad" class="w-full px-4 py-3 rounded-xl border border-slate-200">
            <input name="email" type="email" required placeholder="E-posta" class="w-full px-4 py-3 rounded-xl border border-slate-200">
            <input name="phone" placeholder="Telefon (opsiyonel)" class="w-full px-4 py-3 rounded-xl border border-slate-200">
            <input name="password" type="password" required minlength="8" placeholder="Şifre (en az 8 karakter)" class="w-full px-4 py-3 rounded-xl border border-slate-200">

            <button type="submit" class="w-full py-4 bg-brand-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brand-700 transition-all">
                Kayıt Ol
            </button>
            <p id="register-message" class="text-sm font-medium"></p>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-sm text-slate-400">
            Zaten hesabınız var mı? <a href="/giris" class="text-brand-600 font-bold">Giriş yapın</a>
        </div>
    </div>
</main>

<script>
    document.getElementById('register-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.target).entries());
        const msgEl = document.getElementById('register-message');

        fetch('/api/register-client.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    msgEl.textContent = res.error || 'Kayıt başarısız';
                    msgEl.className = 'text-sm font-medium text-red-600';
                    return;
                }
                localStorage.setItem('mf_token', res.token);
                localStorage.setItem('mf_role', 'client');
                window.location.href = '/musteri';
            })
            .catch(() => {
                msgEl.textContent = 'Bir hata oluştu, lütfen tekrar deneyin.';
                msgEl.className = 'text-sm font-medium text-red-600';
            });
    });
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
