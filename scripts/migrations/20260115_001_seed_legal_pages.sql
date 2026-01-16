-- Checking if pages exist before inserting to avoid duplicates
INSERT INTO posts (title, slug, content, post_type, post_status, created_at, updated_at)
SELECT * FROM (SELECT 
    'Gizlilik Politikası', 
    'gizlilik-politikasi', 
    '<div class="prose prose-slate max-w-none prose-lg">
    <h1>Gizlilik Politikası</h1>
    <p class="lead">Gizliliğiniz bizim için önemlidir.</p>
    <p>Bu gizlilik politikası, web sitemizi kullandığınızda topladığımız, kullandığımız ve koruduğumuz bilgiler hakkında sizi bilgilendirmeyi amaçlamaktadır.</p>
    
    <h3>1. Toplanan Bilgiler</h3>
    <p>Sitemizi ziyaret ettiğinizde, IP adresiniz, tarayıcı türünüz ve ziyaret ettiğiniz sayfalar gibi bazı teknik bilgileri otomatik olarak toplayabiliriz. Ayrıca, iletişim formları veya bülten kayıtları aracılığıyla bize gönüllü olarak sağladığınız ad, e-posta adresi gibi kişisel bilgileri de toplarız.</p>
    
    <h3>2. Bilgilerin Kullanımı</h3>
    <p>Topladığımız bilgileri şu amaçlarla kullanabiliriz:</p>
    <ul>
        <li>Hizmetlerimizi sunmak ve iyileştirmek</li>
        <li>Sizinle iletişim kurmak</li>
        <li>Talep ettiğiniz hizmetleri sağlamak</li>
        <li>Yasal yükümlülüklerimizi yerine getirmek</li>
    </ul>

    <h3>3. Bilgi Güvenliği</h3>
    <p>Kişisel bilgilerinizin güvenliğini sağlamak için uygun teknik ve idari tedbirleri almaktayız. Ancak, internet üzerinden yapılan hiçbir veri iletiminin %100 güvenli olmadığını unutmayınız.</p>
</div>', 
    'page', 
    'publish', 
    NOW(), 
    NOW()
) AS tmp
WHERE NOT EXISTS (
    SELECT slug FROM posts WHERE slug = 'gizlilik-politikasi'
) LIMIT 1;

-- STATEMENT

INSERT INTO posts (title, slug, content, post_type, post_status, created_at, updated_at)
SELECT * FROM (SELECT 
    'Kullanım Şartları', 
    'kullanim-sartlari', 
    '<div class="prose prose-slate max-w-none prose-lg">
    <h1>Kullanım Şartları</h1>
    <p class="lead">Lütfen sitemizi kullanmadan önce bu kullanım şartlarını dikkatlice okuyunuz.</p>
    
    <h3>1. Kabul</h3>
    <p>Bu web sitesine erişerek ve kullanarak, bu kullanım şartlarını kabul etmiş sayılırsınız. Eğer bu şartları kabul etmiyorsanız, lütfen sitemizi kullanmayınız.</p>
    
    <h3>2. Fikri Mülkiyet</h3>
    <p>Sitemizde yer alan tüm içerik (metinler, görseller, logolar vb.) telif hakları ve diğer fikri mülkiyet yasaları ile korunmaktadır. İzinsiz kullanımı yasaktır.</p>
    
    <h3>3. Sorumluluk Reddi</h3>
    <p>Web sitemizdeki bilgiler "olduğu gibi" sunulmaktadır. Bilgilerin doğruluğu, güncelliği veya eksiksizliği konusunda herhangi bir garanti vermemekteyiz.</p>
</div>', 
    'page', 
    'publish', 
    NOW(), 
    NOW()
) AS tmp
WHERE NOT EXISTS (
    SELECT slug FROM posts WHERE slug = 'kullanim-sartlari'
) LIMIT 1;

-- STATEMENT

INSERT INTO posts (title, slug, content, post_type, post_status, created_at, updated_at)
SELECT * FROM (SELECT 
    'Çerez Politikası', 
    'cerez-politikasi', 
    '<div class="prose prose-slate max-w-none prose-lg">
    <h1>Çerez Politikası</h1>
    <p class="lead">Web sitemizde kullanıcı deneyimini iyileştirmek için çerezler kullanmaktayız.</p>
    
    <h3>1. Çerez Nedir?</h3>
    <p>Çerezler, web sitelerini ziyaret ettiğinizde tarayıcınız aracılığıyla cihazınıza kaydedilen küçük metin dosyalarıdır.</p>
    
    <h3>2. Kullandığımız Çerez Türleri</h3>
    <p>Web sitemizde çeşitli amaçlarla çerezler kullanılabilir.</p>
    <ul>
        <li><strong>Zorunlu Çerezler:</strong> Web sitesinin düzgün çalışması için gereklidir.</li>
        <li><strong>Analitik Çerezler:</strong> Ziyaretçilerin siteyi nasıl kullandığını anlamamıza yardımcı olur.</li>
        <li><strong>Pazarlama Çerezleri:</strong> İlgi alanlarınıza göre reklamlar sunmak için kullanılabilir.</li>
    </ul>

    <h3>3. Çerezleri Yönetme</h3>
    <p>Tarayıcı ayarlarınızı değiştirerek çerezleri reddedebilir veya silebilirsiniz. Ancak, bu durumda web sitemizin bazı özellikleri düzgün çalışmayabilir.</p>
</div>', 
    'page', 
    'publish', 
    NOW(), 
    NOW()
) AS tmp
WHERE NOT EXISTS (
    SELECT slug FROM posts WHERE slug = 'cerez-politikasi'
) LIMIT 1;
