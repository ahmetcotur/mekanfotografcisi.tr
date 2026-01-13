# Supabase Kurulum Kılavuzu (Walkthrough)

Bu kılavuz, mekanfotografcisi.tr projesini Supabase ile nasıl çalıştıracağınızı adım adım açıklar.

## 1. Supabase Projesi Oluşturma
1. [Supabase Dashboard](https://app.supabase.com/)'a giren ve yeni bir proje oluşturun.
2. Proje adını ve güvenli bir veritabanı şifresi belirleyin.
3. Bölge (Region) olarak Türkiye'ye en yakın olanı (örn: Frankfurt veya Amsterdam) seçin.

## 2. Veritabanı Şemasını Yükleme
1. Supabase panelinde sol menüden **SQL Editor**'e gidin.
2. **New query** butonuna basın.
3. Proje kök dizinindeki `supabase_schema.sql` dosyasının içeriğini kopyalayıp buraya yapıştırın.
4. **Run** butonuna basarak tüm tabloların, indekslerin ve fonksiyonların oluşturulmasını sağlayın.

## 3. Ortam Değişkenlerini Ayarlama (.env)
1. Projenizdeki `.env.example` dosyasını kopyalayıp `.env` olarak adlandırın.
2. Supabase panelinde **Project Settings > API** sekmesine gidin.
3. Buradaki bilgileri `.env` dosyanıza işleyin:
   - `SUPABASE_URL`: Project URL
   - `SUPABASE_ANON_KEY`: anon public key
   - `SUPABASE_SERVICE_KEY`: service_role key (sadece admin işlemleri için)
4. Veritabanı bağlantısı için **Project Settings > Database** sekmesine gidin ve "Connection string (URI)" kısmından PostgreSQL bilgilerini alın:
   - `DB_HOST`: db.xxxxxx.supabase.co
   - `DB_PORT`: 5432
   - `DB_NAME`: postgres
   - `DB_USER`: postgres
   - `DB_PASSWORD`: (Proje oluştururken belirlediğiniz şifre)

## 4. Uygulamayı Başlatma
1. `.env` dosyanıza ayrıca şu değişkenleri de ekleyin (bağlantı için gereklidir):
   - `DB_HOST`: Supabase veritabanı host adresi
   - `DB_PORT`: 5432
   - `DB_NAME`: postgres
   - `DB_USER`: postgres
   - `DB_PASSWORD`: (Veritabanı şifreniz)

2. `.env` dosyasını kaydettikten sonra PHP sunucunuzu başlatın veya yenileyin.
3. Uygulama otomatik olarak PostgreSQL veritabanına bağlanacak ve verileri oradan çekecektir.

## 5. Admin Paneline Giriş
1. Uygulama kurulduğunda varsayılan bir admin kullanıcısı oluşturulur:
   - **Email:** `admin@mekanfotografcisi.tr`
   - **Şifre:** `admin123`
2. Güvenliğiniz için giriş yaptıktan sonra şifrenizi mutlaka değiştirin.

---
**Not:** Proje hem doğrudan PostgreSQL (PDO) hem de Supabase Client benzeri bir arayüz ile ( `includes/supabase.php` ) çalışmaktadır. Bu sayede veritabanı işlemleri performanslı ve uyumlu bir şekilde yürütülür.
