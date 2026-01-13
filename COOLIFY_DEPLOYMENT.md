# Coolify Deployment Guide - Mekan Fotoğrafçısı

Bu dokümantasyon, mekanfotografcisi.tr projesini Coolify üzerinde doğrudan PostgreSQL ile deploy etmek için gerekli adımları içerir.

## 📋 Önkoşullar

1. **Coolify Hesabı**: Coolify instance'ınız hazır olmalı
2. **PostgreSQL**: Coolify içinde bir "Database" resource'u olarak veya harici (Supabase vb.) PostgreSQL hazır olmalı
3. **Domain**: Projeniz için bir domain hazır olmalı

## 🚀 Deployment Adımları

### 1. Database Setup

PostgreSQL veritabanınıza `database_schema.sql` dosyasını import edin.

### 2. Coolify'da Yeni Uygulama Oluşturma

1. Coolify Dashboard'a giriş yapın
2. **New Resource** > **Application** seçin
3. **Source**: Git repository'nizi seçin
4. **Build Pack**: **Dockerfile** seçin (Zorunlu)
5. **Port**: **80** (Dockerfile içindeki internal port)

### 3. Environment Variables Ayarlama

Coolify'da uygulamanızın **Environment Variables** bölümüne şunları ekleyin:

```bash
# Direct Database Connection
DB_HOST=your-postgres-host
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASSWORD=your-database-password

# Application
NODE_ENV=production
APP_ENV=production
DESTINATION_PORT=80
```

### 4. Admin Panel Yapılandırması

Uygulama zaten yerel session ve doğrudan Postgres kullandığı için admin panel otomatik olarak çalışacaktır. Herhangi bir JS configurasyonu gerekmez.

### 5. Nginx Configuration (Zorunlu)

Coolify varsayılan olarak statik dosyaları arar. Projeyi lokaldeki gibi dinamik çalıştırmak için **Configuration > Custom Nginx Configuration** bölümüne şu ayarları eklemelisiniz:

```nginx
# Tüm istekleri index.php'ye yönlendir (Dinamik Routing)
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# PHP dosyalarını işle
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
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

### 6. İlk İçerik ve Seeding

Deployment tamamlandıktan sonra Coolify terminali üzerinden veya scripti bir kez çalıştırarak verileri doldurabilirsiniz:

```bash
php scripts/seed-locations.php
```

## 📊 Monitoring

### Coolify Dashboard

- **Application Logs**: PHP error logs
- **Resource Usage**: CPU, Memory, Disk
- **Deployment History**: Rollback için

## 🔄 Güncelleme Süreci

1. Git repository'ye push yapın
2. Coolify otomatik olarak yeni deployment başlatır
3. Build tamamlandıktan sonra uygulama otomatik restart olur

## 🚨 KRİTİK: "Welcome to nginx" Hatası Alıyorsanız

Eğer hala varsayılan Nginx sayfasını görüyorsanız:

1. **Build Pack**: Uygulama ayarlarında "Build Pack" kısmının **Dockerfile** olduğundan emin olun.
2. **Port**: Değerin **80** olduğundan emin olun.
3. **Is it a static site?**: Bu seçeneğin **KAPALI** (No) olması gerekir.

---

**Built with ❤️ for Turkish photography professionals**
