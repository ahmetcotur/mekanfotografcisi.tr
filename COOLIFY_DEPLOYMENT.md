# Coolify Deployment Guide - Mekan Fotoğrafçısı

Bu dokümantasyon, mekanfotografcisi.tr projesini Coolify üzerinde Supabase ile deploy etmek için gerekli adımları içerir.

## 📋 Önkoşullar

1. **Coolify Hesabı**: Coolify instance'ınız hazır olmalı
2. **Supabase Projesi**: Supabase projeniz oluşturulmuş ve migration'lar çalıştırılmış olmalı
3. **Domain**: Projeniz için bir domain hazır olmalı (opsiyonel ama önerilir)

## 🚀 Deployment Adımları

### 1. Supabase Migration'ları Çalıştırma

Supabase Dashboard > SQL Editor'da sırayla çalıştırın:

```sql
-- 1. İlk schema
-- supabase/migrations/001_initial_schema.sql

-- 2. Row Level Security
-- supabase/migrations/002_row_level_security.sql

-- 3. Service content fields
-- supabase/migrations/003_add_service_content_fields.sql

-- 4. Location content fields
-- supabase/migrations/004_add_location_content_fields.sql
```

### 2. Supabase Storage Bucket Oluşturma

Supabase Dashboard > Storage:

1. **Bucket Oluştur**: `media` adında public bucket oluşturun
2. **Policies Kontrol**: Storage policies migration'da otomatik oluşturulmuş olmalı

### 3. Coolify'da Yeni Uygulama Oluşturma

1. Coolify Dashboard'a giriş yapın
2. **New Resource** > **Application** seçin
3. **Source**: Git repository'nizi seçin
4. **Build Pack**: **Dockerfile** seçin (Zorunlu)
5. **Port**: **80** (Dockerfile içindeki internal port)

### 4. Environment Variables Ayarlama

Coolify'da uygulamanızın **Environment Variables** bölümüne şunları ekleyin:

```bash
# Supabase Configuration
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key-here
SUPABASE_SERVICE_KEY=your-service-role-key-here

# Application
NODE_ENV=production
APP_ENV=production

# Optional: Google Analytics
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
```

**Önemli**: `SUPABASE_SERVICE_KEY` sadece backend işlemleri için kullanılmalı, asla frontend'de expose edilmemeli.

### 5. Admin Panel Configuration

Admin panel için iki seçenek var:

#### Seçenek A: Environment Variables (Önerilen - Coolify için)

Coolify otomatik olarak environment variable'ları `window.ENV` objesi olarak inject eder. Admin panel bunu otomatik kullanacak.

#### Seçenek B: Config Dosyası (Local Development)

Local development için `admin/config.js` dosyası oluşturun:

```javascript
window.supabaseConfig = {
    SUPABASE_URL: 'https://your-project.supabase.co',
    SUPABASE_ANON_KEY: 'your-anon-key-here'
};
```

**Not**: Bu dosya `.gitignore`'a eklenmelidir.

### 6. PHP Configuration

Coolify'da PHP uygulaması için:

1. **PHP Version**: 8.1+ seçin
2. **Web Server**: Nginx veya Apache
3. **Document Root**: `/public` veya root directory

### 7. Build Script (Opsiyonel)

Eğer build script'i gerekiyorsa, `package.json`'a ekleyin:

```json
{
  "scripts": {
    "build": "echo 'No build step required for PHP'",
    "start": "php -S 0.0.0.0:8000 -t . router.php"
  }
}
```

### 8. Nginx Configuration (Zorunlu)

Coolify varsayılan olarak statik dosyaları arar. Projeyi lokaldeki gibi dinamik çalıştırmak için **Configuration > Custom Nginx Configuration** bölümüne şu ayarları eklemelisiniz:

```nginx
# Tüm istekleri index.php'ye yönlendir (Dinamik Routing)
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# PHP dosyalarını işle
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # Coolify versiyonuna göre değişebilir
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}

# Statik asset'leri doğrudan sun
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|webp)$ {
    expires max;
    log_not_found off;
}
```

**Not**: Eğer hala "Welcome to nginx" sayfasını görüyorsanız, Coolify panelinde **"Is it a static site?"** seçeneğinin kapalı olduğundan ve **"Base Directory"** ayarının doğru olduğundan emin olun.

### 9. Admin User Oluşturma

Supabase Dashboard > Authentication:

1. **New User** oluşturun
2. **User Metadata**'ya ekleyin:
   ```json
   {
     "role": "admin"
   }
   ```
3. Kullanıcıya email ile şifre reset linki gönderin

### 10. İlk İçerik Yükleme

Admin panel'e giriş yaptıktan sonra:

1. **Medya** tab'ından görseller yükleyin
2. **Hizmetler** tab'ından hizmet içeriklerini düzenleyin
3. **İller** ve **İlçeler** tab'ından lokasyon içeriklerini düzenleyin
4. **SEO Sayfaları** tab'ından sayfaları oluşturun ve yayınlayın

## 🔒 Güvenlik Notları

1. **Service Key**: Asla frontend'de kullanmayın, sadece backend API'lerde
2. **Admin Panel**: `/admin` route'u production'da IP whitelist ile korunabilir
3. **CORS**: Supabase'de CORS ayarlarını production domain'inize göre yapılandırın
4. **Rate Limiting**: Supabase'de rate limiting ayarlarını kontrol edin

## 📊 Monitoring

### Supabase Dashboard

- **Database**: Query performance ve connection pool
- **Storage**: Bucket usage ve bandwidth
- **Auth**: User activity ve login attempts
- **Logs**: Real-time error logs

### Coolify Dashboard

- **Application Logs**: PHP error logs
- **Resource Usage**: CPU, Memory, Disk
- **Deployment History**: Rollback için

## 🔄 Güncelleme Süreci

1. Git repository'ye push yapın
2. Coolify otomatik olarak yeni deployment başlatır
3. Build tamamlandıktan sonra uygulama otomatik restart olur
4. Migration'lar varsa Supabase Dashboard'dan manuel çalıştırın

## 🐛 Troubleshooting

### Admin Panel'de Supabase Bağlantı Hatası

1. Environment variable'ları kontrol edin
2. Supabase URL ve key'lerin doğru olduğundan emin olun
3. Browser console'da hata mesajlarını kontrol edin
4. CORS ayarlarını kontrol edin

### Görsel Yükleme Hatası

1. Supabase Storage bucket'ının `media` adında olduğundan emin olun
2. Storage policies'in doğru olduğunu kontrol edin
3. File size limit'lerini kontrol edin (max 10MB)
4. MIME type'ların doğru olduğunu kontrol edin

### İçerik Düzenleme Kaydedilmiyor

1. Database migration'larının çalıştırıldığından emin olun
2. Row Level Security policies'lerin doğru olduğunu kontrol edin
3. User'ın admin role'üne sahip olduğunu kontrol edin

## 📝 Environment Variables Özeti

| Variable | Açıklama | Gerekli |
|----------|----------|---------|
| `SUPABASE_URL` | Supabase project URL | ✅ |
| `SUPABASE_ANON_KEY` | Supabase anonymous key | ✅ |
| `SUPABASE_SERVICE_KEY` | Supabase service role key | ✅ (Backend) |
| `NODE_ENV` | Environment (production) | ✅ |
| `DESTINATION_PORT` | Uygulama Portu (80) | ✅ |
| `GA4_MEASUREMENT_ID` | Google Analytics ID | ❌ |

## 🎯 Sonraki Adımlar

1. **SSL Certificate**: Coolify otomatik Let's Encrypt SSL sağlar
2. **Domain Configuration**: Domain'i Coolify'a bağlayın
3. **Backup Strategy**: Supabase'de otomatik backup'ları aktif edin
4. **Monitoring**: Uptime monitoring servisi ekleyin
5. **CDN**: Statik dosyalar için CDN kullanmayı düşünün

## 📞 Destek

Sorun yaşarsanız:
- Coolify Documentation: https://coolify.io/docs
- Supabase Documentation: https://supabase.com/docs
- Project Issues: GitHub repository'de issue açın


