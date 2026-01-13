#!/usr/bin/env node

/**
 * Turkey Locations Seed Script
 * Seeds all 81 provinces and 973 districts into Supabase
 * Usage: node scripts/seed-locations.js
 */

const { createClient } = require('@supabase/supabase-js');
const fs = require('fs');
const path = require('path');

// Supabase configuration
const SUPABASE_URL = process.env.SUPABASE_URL || process.env.NEXT_PUBLIC_SUPABASE_URL;
const SUPABASE_SERVICE_KEY = process.env.SUPABASE_SERVICE_KEY || process.env.NEXT_PUBLIC_SUPABASE_SERVICE_KEY; // Service role key for admin operations

if (!SUPABASE_URL || !SUPABASE_SERVICE_KEY) {
    console.error('❌ Missing required environment variables:');
    console.error('   SUPABASE_URL');
    console.error('   SUPABASE_SERVICE_KEY');
    console.error('\nPlease set these in your .env file or environment.');
    process.exit(1);
}

// Initialize Supabase client with service role
const supabase = createClient(SUPABASE_URL, SUPABASE_SERVICE_KEY);

// Helper function to create URL-friendly slugs
function createSlug(text) {
    return text
        .toLowerCase()
        .replace(/ğ/g, 'g')
        .replace(/ü/g, 'u')
        .replace(/ş/g, 's')
        .replace(/ı/g, 'i')
        .replace(/ö/g, 'o')
        .replace(/ç/g, 'c')
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

async function seedProvinces(locationData) {
    console.log('🏛️  Seeding provinces...');

    const provinces = [];
    const allProvinces = new Set();

    // Collect all unique provinces from regions
    locationData.regions.forEach(region => {
        region.provinces.forEach(province => {
            if (!allProvinces.has(province.name)) {
                allProvinces.add(province.name);
                provinces.push({
                    name: province.name,
                    slug: createSlug(province.name),
                    region_name: region.name,
                    plate_code: province.plate_code,
                    is_active: false // Start as inactive, admin will activate as needed
                });
            }
        });
    });

    console.log(`   Found ${provinces.length} unique provinces`);

    // Insert provinces in batches
    const batchSize = 50;
    let insertedCount = 0;

    for (let i = 0; i < provinces.length; i += batchSize) {
        const batch = provinces.slice(i, i + batchSize);

        const { data, error } = await supabase
            .from('locations_province')
            .upsert(batch, {
                onConflict: 'slug',
                ignoreDuplicates: false
            })
            .select('id, name');

        if (error) {
            console.error(`❌ Error inserting province batch ${Math.floor(i / batchSize) + 1}:`, error);
            continue;
        }

        insertedCount += data.length;
        console.log(`   ✅ Inserted batch ${Math.floor(i / batchSize) + 1}: ${data.length} provinces`);
    }

    console.log(`✅ Successfully seeded ${insertedCount} provinces\n`);
    return provinces;
}

async function seedDistricts(locationData) {
    console.log('🏘️  Seeding districts...');

    // First, get all province IDs
    const { data: provinces, error: provinceError } = await supabase
        .from('locations_province')
        .select('id, name, slug');

    if (provinceError) {
        console.error('❌ Error fetching provinces:', provinceError);
        return;
    }

    const provinceMap = {};
    provinces.forEach(province => {
        provinceMap[province.name] = province.id;
    });

    const districts = [];

    // Process districts from JSON data
    Object.entries(locationData.districts).forEach(([provinceName, districtList]) => {
        const provinceId = provinceMap[provinceName];

        if (!provinceId) {
            console.warn(`⚠️  Province not found: ${provinceName}`);
            return;
        }

        districtList.forEach(district => {
            districts.push({
                province_id: provinceId,
                name: district.name,
                slug: district.slug,
                is_active: false, // Start as inactive
                local_notes: null // Will be filled by admin later
            });
        });
    });

    console.log(`   Found ${districts.length} districts across ${Object.keys(locationData.districts).length} provinces`);

    // Insert districts in batches
    const batchSize = 100;
    let insertedCount = 0;

    for (let i = 0; i < districts.length; i += batchSize) {
        const batch = districts.slice(i, i + batchSize);

        const { data, error } = await supabase
            .from('locations_district')
            .upsert(batch, {
                onConflict: 'province_id,slug',
                ignoreDuplicates: false
            })
            .select('id, name');

        if (error) {
            console.error(`❌ Error inserting district batch ${Math.floor(i / batchSize) + 1}:`, error);
            continue;
        }

        insertedCount += data.length;
        console.log(`   ✅ Inserted batch ${Math.floor(i / batchSize) + 1}: ${data.length} districts`);
    }

    console.log(`✅ Successfully seeded ${insertedCount} districts\n`);
    return districts;
}

async function seedServices() {
    console.log('🛠️  Seeding default services...');

    const services = [
        {
            name: 'Mimari Fotoğrafçılık',
            slug: 'mimari-fotografcilik',
            short_intro: 'Binaların dış cephe, peyzaj ve çevre düzenlemelerini en etkileyici açılardan fotoğraflıyoruz.',
            is_active: true
        },
        {
            name: 'İç Mekan Fotoğrafçılığı',
            slug: 'ic-mekan-fotografciligi',
            short_intro: 'Ev, villa, ofis ve ticari alanların iç mekan fotoğraflarını profesyonel ekipmanlarla çekiyoruz.',
            is_active: true
        },
        {
            name: 'Emlak Fotoğrafçılığı',
            slug: 'emlak-fotografciligi',
            short_intro: 'Satılık veya kiralık mülklerinizi en çekici şekilde göstererek pazarlama sürecinize katkı sağlıyoruz.',
            is_active: true
        },
        {
            name: 'Otel ve Restoran Fotoğrafçılığı',
            slug: 'otel-restoran-fotografciligi',
            short_intro: 'Otel odaları, restoranlar ve cafe mekanları için müşteri çekici fotoğraflar üretiyoruz.',
            is_active: true
        }
    ];

    const { data, error } = await supabase
        .from('services')
        .upsert(services, {
            onConflict: 'slug',
            ignoreDuplicates: false
        })
        .select('id, name');

    if (error) {
        console.error('❌ Error inserting services:', error);
        return;
    }

    console.log(`✅ Successfully seeded ${data.length} services\n`);
    return data;
}

async function seedVariationBlocks() {
    console.log('📝 Seeding SEO variation blocks...');

    const variationBlocks = [
        // Intro variations
        {
            block_type: 'intro',
            variant_md: `## Profesyonel Mekan Fotoğrafçılığı Hizmetleri

Antalya ve Muğla bölgesinde 10 yılı aşkın deneyimimizle, mekanlarınızın en etkileyici yönlerini öne çıkaran profesyonel fotoğraflar üretiyoruz. Modern ekipmanlarımız ve uzman ekibimizle, her projeye özel yaklaşım sergiliyoruz.`,
            weight: 1
        },
        {
            block_type: 'intro',
            variant_md: `## Uzman Ekibimizle Kaliteli Fotoğrafçılık

Akdeniz ve Ege bölgesinin eşsiz güzelliklerini mekanlarınızla harmanlayarak, görsel hikayeler yaratıyoruz. Profesyonel fotoğrafçılık deneyimimiz ve son teknoloji ekipmanlarımızla, beklentilerinizi aşan sonuçlar elde ediyoruz.`,
            weight: 1
        },
        {
            block_type: 'intro',
            variant_md: `## Mekanlarınızın Gerçek Potansiyelini Keşfedin

Her mekanın kendine özgü bir hikayesi vardır. Biz bu hikayeleri, profesyonel fotoğrafçılık teknikleri ve sanatsal bakış açımızla görselleştiriyoruz. Antalya ve Muğla'nın doğal ışığından faydalanarak, mekanlarınızı en iyi şekilde yansıtıyoruz.`,
            weight: 1
        },

        // Process variations
        {
            block_type: 'process',
            variant_md: `## Çalışma Sürecimiz

### 1. Ön Görüşme ve Planlama
Projenizin detaylarını konuşur, çekim planını birlikte belirleriz.

### 2. Profesyonel Çekim
Uzman ekibimiz ve profesyonel ekipmanlarla mekanınızı fotoğraflıyoruz.

### 3. Düzenleme ve Teslim
Fotoğraflarınızı profesyonel yazılımlarla düzenleyip, 3-5 iş günü içinde teslim ediyoruz.`,
            weight: 1
        },
        {
            block_type: 'process',
            variant_md: `## Nasıl Çalışıyoruz?

### Keşif ve Analiz
Mekanınızı inceleyerek en iyi çekim açılarını belirliyoruz.

### Teknik Hazırlık
Işık koşullarını analiz ederek ekipmanlarımızı optimize ediyoruz.

### Çekim Süreci
Detaylı çekim planımıza göre, mekanınızın her köşesini profesyonelce fotoğraflıyoruz.

### Son İşlemler
Renk düzeltme, kontrast ayarları ve kalite kontrolü ile fotoğraflarınızı teslime hazırlıyoruz.`,
            weight: 1
        },

        // Benefits variations
        {
            block_type: 'benefits',
            variant_md: `## Neden Bizi Tercih Etmelisiniz?

- **Deneyimli Ekip**: 10+ yıllık profesyonel fotoğrafçılık deneyimi
- **Modern Ekipman**: Son teknoloji kameralar ve aydınlatma sistemleri
- **Hızlı Teslimat**: 3-5 iş günü içinde düzenlenmiş fotoğraflar
- **Bölgesel Uzmanlık**: Antalya ve Muğla'nın ışık koşullarına hakim
- **Esnek Çalışma**: Size uygun zaman dilimlerinde çekim imkanı`,
            weight: 1
        },
        {
            block_type: 'benefits',
            variant_md: `## Avantajlarımız

✓ **Kalite Garantisi**: Her projede mükemmellik standardı
✓ **Geniş Portföy**: Mimari, iç mekan, emlak ve otel fotoğrafçılığı
✓ **Rekabetçi Fiyatlar**: Kaliteli hizmet, uygun fiyat
✓ **Müşteri Memnuniyeti**: %100 müşteri memnuniyet oranı
✓ **Teknik Destek**: Çekim sonrası danışmanlık hizmeti`,
            weight: 1
        },

        // FAQ variations
        {
            block_type: 'faq',
            variant_md: `## Sıkça Sorulan Sorular

**Çekim öncesi hazırlık gerekir mi?**
Mekanınızın temiz ve düzenli olması yeterlidir. Gerekli tüm ekipmanları biz getiriyoruz.

**Kötü hava koşullarında çekim yapılır mı?**
İç mekan çekimleri hava koşullarından etkilenmez. Dış çekimler için uygun gün planlaması yaparız.

**Fotoğrafların telif hakkı kime aittir?**
Çekim bedeli ödendikten sonra tüm fotoğrafların kullanım hakkı size aittir.`,
            weight: 1
        },

        // CTA variations
        {
            block_type: 'cta',
            variant_md: `## Hemen İletişime Geçin!

Mekanınızın profesyonel fotoğrafları için bugün bizimle iletişime geçin. Ücretsiz keşif görüşmesi ve detaylı teklif için [iletişim sayfamızı](/iletisim) ziyaret edin.

**Telefon**: +90 507 467 75 02  
**E-posta**: info@mekanfotografcisi.tr`,
            weight: 1
        },
        {
            block_type: 'cta',
            variant_md: `## Projenizi Başlatalım

Hayalinizdeki fotoğraflar için hemen harekete geçin! Deneyimli ekibimiz ve profesyonel yaklaşımımızla, mekanınızı en iyi şekilde yansıtan fotoğraflar üretiyoruz.

[Teklif almak için tıklayın](/iletisim) veya **+90 507 467 75 02** numaralı telefonu arayın.`,
            weight: 1
        }
    ];

    const { data, error } = await supabase
        .from('seo_variation_blocks')
        .upsert(variationBlocks, {
            onConflict: 'id',
            ignoreDuplicates: false
        })
        .select('id, block_type');

    if (error) {
        console.error('❌ Error inserting variation blocks:', error);
        return;
    }

    console.log(`✅ Successfully seeded ${data.length} variation blocks\n`);
    return data;
}

async function generateVerificationReport() {
    console.log('📊 Generating verification report...');

    // Count provinces
    const { count: provinceCount, error: provinceError } = await supabase
        .from('locations_province')
        .select('*', { count: 'exact', head: true });

    if (provinceError) {
        console.error('❌ Error counting provinces:', provinceError);
        return;
    }

    // Count districts
    const { count: districtCount, error: districtError } = await supabase
        .from('locations_district')
        .select('*', { count: 'exact', head: true });

    if (districtError) {
        console.error('❌ Error counting districts:', districtError);
        return;
    }

    // Count services
    const { count: serviceCount, error: serviceError } = await supabase
        .from('services')
        .select('*', { count: 'exact', head: true });

    if (serviceError) {
        console.error('❌ Error counting services:', serviceError);
        return;
    }

    // Count variation blocks
    const { count: blockCount, error: blockError } = await supabase
        .from('seo_variation_blocks')
        .select('*', { count: 'exact', head: true });

    if (blockError) {
        console.error('❌ Error counting variation blocks:', blockError);
        return;
    }

    // Get sample data
    const { data: sampleProvinces } = await supabase
        .from('locations_province')
        .select('name, slug, region_name, plate_code')
        .limit(5);

    const { data: sampleDistricts } = await supabase
        .from('locations_district')
        .select('name, slug, locations_province(name)')
        .limit(5);

    console.log('\n📋 VERIFICATION REPORT');
    console.log('='.repeat(50));
    console.log(`Provinces seeded: ${provinceCount}/81`);
    console.log(`Districts seeded: ${districtCount}`);
    console.log(`Services seeded: ${serviceCount}/4`);
    console.log(`Variation blocks seeded: ${blockCount}`);
    console.log('\n📍 Sample Provinces:');
    sampleProvinces?.forEach(p => {
        console.log(`   ${p.name} (${p.slug}) - ${p.region_name} - Plate: ${p.plate_code}`);
    });

    console.log('\n🏘️  Sample Districts:');
    sampleDistricts?.forEach(d => {
        console.log(`   ${d.name} (${d.slug}) - ${d.locations_province?.name}`);
    });

    console.log('\n✅ Seed operation completed successfully!');
    console.log('\n🔧 Next Steps:');
    console.log('1. Access admin panel to activate provinces/districts');
    console.log('2. Generate SEO pages for activated locations');
    console.log('3. Customize local_notes for districts');
    console.log('4. Review and publish generated content');
}

async function main() {
    try {
        console.log('🚀 Starting Turkey locations seed process...\n');

        // Load location data
        const dataPath = path.join(__dirname, '..', 'data', 'turkey-locations.json');
        const locationData = JSON.parse(fs.readFileSync(dataPath, 'utf8'));

        // Seed data in order
        await seedProvinces(locationData);
        await seedDistricts(locationData);
        await seedServices();
        await seedVariationBlocks();

        // Generate verification report
        await generateVerificationReport();

    } catch (error) {
        console.error('❌ Fatal error during seed process:', error);
        process.exit(1);
    }
}

// Run the seed script
if (require.main === module) {
    main();
}

module.exports = { main, seedProvinces, seedDistricts, seedServices, seedVariationBlocks };