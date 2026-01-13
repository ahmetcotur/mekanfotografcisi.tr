# Production Deployment Guide

## Önkoşullar

1. Supabase projesi oluşturulmuş ve migration'lar çalıştırılmış
2. Domain ve hosting hazır
3. SSL sertifikası aktif
4. PHP 7.4+ yüklü

## Deployment Adımları

### 1. Environment Variables Yapılandırması

#### Root dizinde `.env` dosyası oluşturun:

```bash
cp .env.example .env
```

#### `.env` dosyasını düzenleyin:

```bash
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key-here
SUPABASE_SERVICE_KEY=your-service-role-key-here
NODE_ENV=production
GA4_MEASUREMENT_ID=G-XXXXXXXXXX  # Optional
```

### 2. Admin Panel Yapılandırması

`admin/admin.js` dosyasını düzenleyin (satır 6-7):

```javascript
const SUPABASE_URL = 'https://your-project.supabase.co';
const SUPABASE_ANON_KEY = 'your-anon-key-here';
```

### 3. HTTPS Redirect Aktif Etme

`.htaccess` dosyasında (satır 4-5) yorumu kaldırın:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 4. Dosya İzinleri

```bash
# PHP dosyaları için uygun izinler
chmod 644 *.php
chmod 644 routes/*.php
chmod 644 includes/*.php
chmod 644 api/*.php

# .env dosyası için güvenli izinler
chmod 600 .env

# Yazılabilir dizinler (form data için)
chmod 755 .
```

### 5. Database Migration

Supabase Dashboard > SQL Editor'da sırayla çalıştırın:

1. `supabase/migrations/001_initial_schema.sql`
2. `supabase/migrations/002_row_level_security.sql`

### 6. Location Data Seeding

```bash
npm install
npm run seed
```

### 7. Admin User Oluşturma

Supabase Dashboard > Authentication:
1. Kullanıcı oluşturun
2. User metadata'ya ekleyin: `{"role": "admin"}`

### 8. Google Analytics (Opsiyonel)

`.env` dosyasına `GA4_MEASUREMENT_ID` ekleyin.

Google Search Console:
1. Domain doğrulaması yapın
2. Sitemap ekleyin: `https://mekanfotografcisi.tr/sitemap.xml`

### 9. Test Checklist

- [ ] Ana sayfa yükleniyor (`/`)
- [ ] Tüm route'lar çalışıyor (services, locations, portfolio)
- [ ] Contact form çalışıyor
- [ ] Admin panel login yapılabiliyor
- [ ] SEO sayfaları generate edilebiliyor
- [ ] Sitemap erişilebilir (`/sitemap.xml`)
- [ ] HTTPS yönlendirme çalışıyor
- [ ] .env dosyası erişilemiyor (403)

### 10. Performance Kontrolü

- [ ] PageSpeed Insights test edin
- [ ] Core Web Vitals kontrol edin
- [ ] Image optimization kontrol edin
- [ ] Browser caching çalışıyor

### 11. Security Kontrolü

- [ ] .env dosyası .gitignore'da
- [ ] Admin panel authentication çalışıyor
- [ ] XSS protection aktif
- [ ] SQL injection koruması (Supabase RLS)
- [ ] CSRF protection (admin panel için)

## Sorun Giderme

### Supabase bağlantı hataları
- `.env` dosyasının doğru yolda olduğundan emin olun
- Environment variable'ların server'da yüklü olduğundan emin olun
- Supabase URL ve key'lerin doğru olduğunu kontrol edin

### Route'lar çalışmıyor
- `.htaccess` dosyasının aktif olduğundan emin olun
- Apache mod_rewrite modülünün aktif olduğunu kontrol edin
- Error log'ları kontrol edin

### Admin panel login olmuyor
- Supabase credentials'ın doğru olduğunu kontrol edin
- User metadata'da `role: 'admin'` olduğundan emin olun
- Browser console'da hataları kontrol edin

## Rollback Planı

1. Database backup'ını restore edin
2. Önceki kod versiyonuna geri dönün
3. `.env` dosyasını önceki değerlerle güncelleyin
4. Cache'leri temizleyin



