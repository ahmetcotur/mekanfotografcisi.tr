# Production Deployment Guide

## Önkoşullar

1. PostgreSQL veritabanı hazır ve `database_schema.sql` içe aktarılmış
2. Domain ve hosting hazır
3. SSL sertifikası aktif
4. PHP 8.1+ yüklü (pdo_pgsql eklentisi ile)

## Deployment Adımları

### 1. Environment Variables Yapılandırması

#### Root dizinde `.env` dosyası oluşturun:

```bash
cp .env.example .env
```

#### `.env` dosyasını düzenleyin:

```bash
DB_HOST=your-postgres-host
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASSWORD=your-secret-password
NODE_ENV=production
GA4_MEASUREMENT_ID=G-XXXXXXXXXX  # Optional
```

### 2. HTTPS Redirect Aktif Etme

`.htaccess` dosyasında (satır 4-5) yorumu kaldırın:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Dosya İzinleri

```bash
# PHP dosyaları için uygun izinler
chmod 644 *.php
chmod 644 routes/*.php
chmod 644 includes/*.php
chmod 644 api/*.php

# .env dosyası için güvenli izinler
chmod 600 .env

# Yazılabilir dizinler (Uploads için)
chmod 755 uploads/media
```

### 4. Database Setup

Veritabanınıza `database_schema.sql` dosyasını import edin.

### 5. Location Data Seeding

```bash
php scripts/seed-locations.php
```

### 6. Admin User Oluşturma

`admin_users` tablosuna manuel olarak bir admin kullanıcısı ekleyin. `password_hash` için PHP `password_hash()` fonksiyonunu kullanın.

### 7. Google Analytics (Opsiyonel)

`.env` dosyasına `GA4_MEASUREMENT_ID` ekleyin.

Google Search Console:
1. Domain doğrulaması yapın
2. Sitemap ekleyin: `https://mekanfotografcisi.tr/sitemap.xml`

### 8. Test Checklist

- [ ] Ana sayfa yükleniyor (`/`)
- [ ] Tüm route'lar çalışıyor (services, locations, portfolio)
- [ ] Contact form çalışıyor
- [ ] Admin panel login yapılabiliyor
- [ ] SEO sayfaları generate edilebiliyor
- [ ] Sitemap erişilebilir (`/sitemap.xml`)
- [ ] HTTPS yönlendirme çalışıyor
- [ ] .env dosyası erişilemiyor (403)

### 9. Performance Kontrolü

- [ ] PageSpeed Insights test edin
- [ ] Core Web Vitals kontrol edin
- [ ] Image optimization kontrol edin
- [ ] Browser caching çalışıyor

### 10. Security Kontrolü

- [ ] .env dosyası .getignore'da
- [ ] Admin panel authentication çalışıyor
- [ ] XSS protection aktif
- [ ] SQL injection koruması (PDO prepared statements)

## Sorun Giderme

### Veritabanı bağlantı hataları
- `.env` dosyasının doğru yolda olduğundan emin olun
- `pdo_pgsql` eklentisinin aktif olduğunu kontrol edin
- Host ve şifre bilgilerini doğrulayın

### Route'lar çalışmıyor
- `.htaccess` dosyasınınaktif olduğundan emin olun
- Apache mod_rewrite modülünün aktif olduğunu kontrol edin
- `router.php` dosyasının root dizinde olduğunu doğrulayın

## Rollback Planı

1. Database backup'ını restore edin
2. Önceki kod versiyonuna geri dönün
3. `.env` dosyasını önceki değerlerle güncelleyin
