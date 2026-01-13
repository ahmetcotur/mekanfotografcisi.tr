<?php
/**
 * Fix: Create and seed 'settings' table
 * Run this via URL: https://mekanfotografcisi.tr/scripts/fix-settings.php (if accessible)
 * or via terminal: php scripts/fix-settings.php
 */

require_once __DIR__ . '/../includes/database.php';

function log_msg($msg)
{
    if (php_sapi_name() === 'cli') {
        echo $msg . "\n";
    } else {
        error_log("[FixSettings] " . $msg);
        echo $msg . "<br>";
    }
}

try {
    $db = new DatabaseClient();

    log_msg("🛠️ Creating 'settings' table...");
    $db->query("CREATE TABLE IF NOT EXISTS settings (
        id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
        key VARCHAR(255) NOT NULL UNIQUE,
        value TEXT,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
        updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
    )");

    log_msg("✅ Settings table created (or already exists).");

    $defaultSettings = [
        ['key' => 'site_title', 'value' => 'Mekan Fotoğrafçısı'],
        ['key' => 'seo_default_desc', 'value' => 'Antalya ve Muğla bölgesinde profesyonel mimari, iç mekan ve otel fotoğrafçılığı hizmetleri.'],
        ['key' => 'phone', 'value' => '+905074677502'],
        ['key' => 'email', 'value' => 'info@mekanfotografcisi.tr'],
        ['key' => 'primary_color', 'value' => '#0ea5e9'],
        ['key' => 'secondary_color', 'value' => '#0284c7'],
        ['key' => 'logo_url', 'value' => '']
    ];

    log_msg("📥 Seeding default settings...");
    foreach ($defaultSettings as $s) {
        $exists = $db->query("SELECT id FROM settings WHERE key = ?", [$s['key']]);
        if (empty($exists)) {
            $db->insert('settings', [
                'key' => $s['key'],
                'value' => $s['value']
            ]);
            log_msg("   + Seeded setting: {$s['key']}");
        } else {
            log_msg("   . Skipped setting (exists): {$s['key']}");
        }
    }

    log_msg("🏠 Checking homepage...");
    $homeExists = $db->select('posts', ['slug' => 'homepage']);
    if (empty($homeExists)) {
        $db->insert('posts', [
            'id' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
            'title' => 'Profesyonel Mekan Fotoğrafçılığı | Antalya & Muğla',
            'slug' => 'homepage',
            'content' => '<h1>Profesyonel Mekan Fotoğrafçılığı Hizmetleri</h1><p>Mimari, iç mekan ve emlak fotoğrafçılığı alanında uzman ekibimizle mekanlarınızı en etkileyici açılardan fotoğraflıyoruz. Antalya ve Muğla bölgelerinde kaliteli görsel içerik üretiminde çözüm ortağınız olmaya hazırız.</p>',
            'post_type' => 'page',
            'post_status' => 'publish',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        log_msg("   + Seeded homepage.");
    }


    log_msg("✨ Fix operation completed successfully!");

} catch (Exception $e) {
    log_msg("❌ Error: " . $e->getMessage());
}
