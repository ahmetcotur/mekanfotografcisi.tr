<?php
/**
 * Freelancer self-service registration (/kayit/fotografci)
 * Posts to api/register-freelancer.php, then auto-logs in and redirects to /panel.
 */
$pageTitle = 'Kolektife Katılın';
$pageDescription = 'Bağımsız fotoğrafçı olarak kayıt olun, açık çekim taleplerini görün ve kendi işinizi büyütün.';
$pageRobots = 'noindex, follow';
include __DIR__ . '/../page-header.php';
?>

<main class="pt-40 pb-24 min-h-screen flex items-start justify-center">
    <div class="max-w-xl w-full mx-4 bg-white rounded-3xl border border-slate-100 shadow-xl p-8 md:p-10">
        <h1 class="text-2xl font-heading font-black text-slate-900 mb-2">Fotoğrafçı Olarak Katılın</h1>
        <p class="text-slate-400 text-sm mb-8">Kolektifimize katılın, açık çekim taleplerine erişin ve kendi profilinizi yönetin.</p>

        <form id="register-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input name="name" required placeholder="Ad Soyad" class="px-4 py-3 rounded-xl border border-slate-200">
                <input name="email" type="email" required placeholder="E-posta" class="px-4 py-3 rounded-xl border border-slate-200">
                <input name="password" type="password" required minlength="8" placeholder="Şifre (en az 8 karakter)" class="px-4 py-3 rounded-xl border border-slate-200">
                <input name="phone" required placeholder="Telefon" class="px-4 py-3 rounded-xl border border-slate-200">
                <input name="city" required placeholder="Şehir" class="px-4 py-3 rounded-xl border border-slate-200">
                <select name="experience" required class="px-4 py-3 rounded-xl border border-slate-200">
                    <option value="">Deneyim</option>
                    <option value="0-1">0-1 yıl</option>
                    <option value="1-3">1-3 yıl</option>
                    <option value="3-5">3-5 yıl</option>
                    <option value="5-10">5-10 yıl</option>
                    <option value="10+">10+ yıl</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-600 mb-2">Uzmanlık Alanları</label>
                <div class="flex flex-wrap gap-3">
                    <?php foreach (['mekan' => 'Mekan', 'dugun' => 'Düğün', 'mimari' => 'Mimari', 'otel' => 'Otel', 'emlak' => 'Emlak', 'yemek' => 'Yemek', 'drone' => 'Drone'] as $val => $label): ?>
                        <label class="flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 cursor-pointer text-sm">
                            <input type="checkbox" name="specialization" value="<?= e($val) ?>"> <?= e($label) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <input name="portfolio_url" placeholder="Portfolyo linki (opsiyonel)" class="w-full px-4 py-3 rounded-xl border border-slate-200">
            <textarea name="message" placeholder="Kendinizden kısaca bahsedin (opsiyonel)" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200"></textarea>

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
        const form = e.target;
        const msgEl = document.getElementById('register-message');

        const specialization = Array.from(form.querySelectorAll('input[name="specialization"]:checked')).map(c => c.value);
        if (!specialization.length) {
            msgEl.textContent = 'En az bir uzmanlık alanı seçin.';
            msgEl.className = 'text-sm font-medium text-red-600';
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

        fetch('/api/register-freelancer.php', {
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
                localStorage.setItem('mf_role', 'freelancer');
                window.location.href = '/panel';
            })
            .catch(() => {
                msgEl.textContent = 'Bir hata oluştu, lütfen tekrar deneyin.';
                msgEl.className = 'text-sm font-medium text-red-600';
            });
    });
</script>

<?php include __DIR__ . '/../page-footer.php'; ?>
