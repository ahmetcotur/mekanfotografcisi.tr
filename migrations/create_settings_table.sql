-- Settings table for site configuration
CREATE TABLE IF NOT EXISTS settings (
    id SERIAL PRIMARY KEY,
    "key" VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    "group" VARCHAR(100) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_settings_key ON settings("key");
CREATE INDEX IF NOT EXISTS idx_settings_group ON settings("group");

-- Insert default settings
INSERT INTO settings ("key", value, "group") VALUES
('site_title', 'Mekan Fotoğrafçısı', 'General'),
('site_tagline', 'Antalya ve Muğla Bölgesinde Profesyonel Fotoğrafçılık', 'General'),
('phone', '+90 507 467 75 02', 'Contact'),
('email', 'info@mekanfotografcisi.tr', 'Contact'),
('address', 'Kalkan Mah. Şehitler Cad. no 7 Kaş / Antalya', 'Contact'),
('social_instagram', 'https://instagram.com/mekanfotografcisi', 'Social'),
('primary_color', '#0ea5e9', 'Design'),
('secondary_color', '#0284c7', 'Design'),
('seo_default_desc', 'Antalya ve Muğla bölgesinde profesyonel mimari, iç mekan ve otel fotoğrafçılığı hizmetleri.', 'SEO')
ON CONFLICT ("key") DO NOTHING;
