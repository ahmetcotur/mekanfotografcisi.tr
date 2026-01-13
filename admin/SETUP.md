# Admin Panel Setup Guide

## Supabase Configuration

Admin panel JavaScript dosyası (`admin.js`) içinde Supabase credentials'ları yapılandırmanız gerekmektedir.

### Adım 1: Supabase Projenizi Oluşturun

1. [Supabase](https://supabase.com) hesabı oluşturun
2. Yeni bir proje oluşturun
3. Project Settings > API bölümünden aşağıdaki bilgileri alın:
   - Project URL
   - `anon` public key

### Adım 2: admin.js Dosyasını Güncelleyin

`admin/admin.js` dosyasının başındaki (satır 6-7) şu satırları bulun:

```javascript
const SUPABASE_URL = 'YOUR_SUPABASE_URL';
const SUPABASE_ANON_KEY = 'YOUR_SUPABASE_ANON_KEY';
```

Bu değerleri kendi Supabase bilgilerinizle değiştirin:

```javascript
const SUPABASE_URL = 'https://your-project-id.supabase.co';
const SUPABASE_ANON_KEY = 'your-anon-key-here';
```

### Adım 3: Admin User Oluşturun

Supabase Dashboard > Authentication bölümünden:

1. Bir kullanıcı oluşturun (email/password)
2. User'ın metadata'sına şunu ekleyin:
   ```json
   {
     "role": "admin"
   }
   ```

### Adım 4: Database Migration'ları Çalıştırın

1. Supabase Dashboard > SQL Editor'a gidin
2. `supabase/migrations/001_initial_schema.sql` dosyasını çalıştırın
3. `supabase/migrations/002_row_level_security.sql` dosyasını çalıştırın

### Adım 5: Location Data'yı Seed Edin

Terminal'de:

```bash
# .env dosyasını oluşturun (root dizinde)
cp .env.example .env

# .env dosyasını düzenleyin ve SUPABASE_URL, SUPABASE_SERVICE_KEY ekleyin
# Service key: Supabase Dashboard > Settings > API > service_role key

# Seed script'i çalıştırın
npm install
npm run seed
```

### Güvenlik Notu

⚠️ **ÖNEMLİ**: `admin.js` dosyası client-side JavaScript olduğu için, içindeki `SUPABASE_ANON_KEY` public olarak görünecektir. Bu normaldir ve güvenlidir çünkü:
- `anon` key Row Level Security (RLS) politikaları ile korunur
- Sadece admin user'lar admin panel işlemlerini yapabilir
- Public API sadece published içerikleri döner

Service key'i asla client-side kodda kullanmayın!



