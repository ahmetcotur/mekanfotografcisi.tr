-- =============================================
-- Supabase Database Schema for SEO Extension
-- mekanfotografcisi.tr
-- =============================================

-- Enable necessary extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- =============================================
-- 1. LOCATIONS TABLES
-- =============================================

-- Provinces table (81 provinces in Turkey)
CREATE TABLE locations_province (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    region_name VARCHAR(50), -- 7 geographical regions
    plate_code INTEGER UNIQUE, -- Turkish license plate codes (1-81)
    is_active BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Districts table (973 districts in Turkey)
CREATE TABLE locations_district (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    province_id UUID NOT NULL REFERENCES locations_province(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT false,
    local_notes TEXT, -- Human-written local differentiators
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(province_id, slug)
);

-- =============================================
-- 2. SERVICES TABLE
-- =============================================

CREATE TABLE services (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    short_intro TEXT,
    is_active BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =============================================
-- 3. MEDIA TABLE
-- =============================================

CREATE TABLE media (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    storage_path VARCHAR(500) NOT NULL, -- Supabase Storage path
    public_url VARCHAR(500) NOT NULL, -- Public accessible URL
    alt VARCHAR(200),
    width INTEGER,
    height INTEGER,
    file_size INTEGER, -- in bytes
    mime_type VARCHAR(100),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =============================================
-- 4. PORTFOLIO PROJECTS TABLE
-- =============================================

CREATE TABLE portfolio_projects (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    province_id UUID REFERENCES locations_province(id),
    district_id UUID REFERENCES locations_district(id),
    cover_media_id UUID REFERENCES media(id),
    gallery_media_ids UUID[] DEFAULT '{}', -- Array of media IDs
    description TEXT,
    year INTEGER,
    is_published BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =============================================
-- 5. SEO PAGES TABLE
-- =============================================

CREATE TABLE seo_pages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    type VARCHAR(20) NOT NULL CHECK (type IN ('province', 'district', 'service', 'portfolio')),
    province_id UUID REFERENCES locations_province(id),
    district_id UUID REFERENCES locations_district(id),
    service_id UUID REFERENCES services(id),
    portfolio_id UUID REFERENCES portfolio_projects(id),
    slug VARCHAR(300) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    meta_description VARCHAR(320) NOT NULL,
    h1 VARCHAR(200) NOT NULL,
    content_md TEXT NOT NULL, -- Markdown content
    faq_json JSONB, -- FAQ section as JSON
    published BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =============================================
-- 6. SEO TEMPLATES TABLE
-- =============================================

CREATE TABLE seo_templates (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    type VARCHAR(20) NOT NULL CHECK (type IN ('province', 'district', 'service', 'portfolio')),
    base_template_md TEXT NOT NULL, -- Base markdown template with placeholders
    rules_json JSONB, -- Template rules and variable definitions
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =============================================
-- 7. SEO VARIATION BLOCKS TABLE
-- =============================================

CREATE TABLE seo_variation_blocks (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    block_type VARCHAR(20) NOT NULL CHECK (block_type IN ('intro', 'process', 'benefits', 'faq', 'cta')),
    variant_md TEXT NOT NULL, -- Markdown content for this variation
    weight INTEGER DEFAULT 1, -- For weighted random selection
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- =============================================
-- INDEXES FOR PERFORMANCE
-- =============================================

-- Location indexes
CREATE INDEX idx_locations_province_slug ON locations_province(slug);
CREATE INDEX idx_locations_province_active ON locations_province(is_active);
CREATE INDEX idx_locations_district_slug ON locations_district(slug);
CREATE INDEX idx_locations_district_active ON locations_district(is_active);
CREATE INDEX idx_locations_district_province ON locations_district(province_id);

-- SEO pages indexes
CREATE INDEX idx_seo_pages_type ON seo_pages(type);
CREATE INDEX idx_seo_pages_published ON seo_pages(published);
CREATE INDEX idx_seo_pages_slug ON seo_pages(slug);
CREATE INDEX idx_seo_pages_province ON seo_pages(province_id);
CREATE INDEX idx_seo_pages_district ON seo_pages(district_id);

-- Services indexes
CREATE INDEX idx_services_slug ON services(slug);
CREATE INDEX idx_services_active ON services(is_active);

-- Portfolio indexes
CREATE INDEX idx_portfolio_slug ON portfolio_projects(slug);
CREATE INDEX idx_portfolio_published ON portfolio_projects(is_published);

-- Variation blocks indexes
CREATE INDEX idx_variation_blocks_type ON seo_variation_blocks(block_type);

-- =============================================
-- TRIGGERS FOR UPDATED_AT
-- =============================================

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply triggers to all tables with updated_at
CREATE TRIGGER update_locations_province_updated_at BEFORE UPDATE ON locations_province FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_locations_district_updated_at BEFORE UPDATE ON locations_district FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_services_updated_at BEFORE UPDATE ON services FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_media_updated_at BEFORE UPDATE ON media FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_portfolio_projects_updated_at BEFORE UPDATE ON portfolio_projects FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_seo_pages_updated_at BEFORE UPDATE ON seo_pages FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_seo_templates_updated_at BEFORE UPDATE ON seo_templates FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_seo_variation_blocks_updated_at BEFORE UPDATE ON seo_variation_blocks FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();-- =============================================
-- Row Level Security (RLS) Policies
-- mekanfotografcisi.tr
-- =============================================

-- Enable RLS on all tables
ALTER TABLE locations_province ENABLE ROW LEVEL SECURITY;
ALTER TABLE locations_district ENABLE ROW LEVEL SECURITY;
ALTER TABLE services ENABLE ROW LEVEL SECURITY;
ALTER TABLE media ENABLE ROW LEVEL SECURITY;
ALTER TABLE portfolio_projects ENABLE ROW LEVEL SECURITY;
ALTER TABLE seo_pages ENABLE ROW LEVEL SECURITY;
ALTER TABLE seo_templates ENABLE ROW LEVEL SECURITY;
ALTER TABLE seo_variation_blocks ENABLE ROW LEVEL SECURITY;

-- =============================================
-- PUBLIC (ANONYMOUS) POLICIES - READ ONLY
-- =============================================

-- Locations Province - Public can read active provinces
CREATE POLICY "Public can read active provinces" ON locations_province
    FOR SELECT USING (is_active = true);

-- Locations District - Public can read active districts
CREATE POLICY "Public can read active districts" ON locations_district
    FOR SELECT USING (is_active = true);

-- Services - Public can read active services
CREATE POLICY "Public can read active services" ON services
    FOR SELECT USING (is_active = true);

-- Media - Public can read all media (for published content)
CREATE POLICY "Public can read media" ON media
    FOR SELECT USING (true);

-- Portfolio Projects - Public can read published projects
CREATE POLICY "Public can read published portfolio" ON portfolio_projects
    FOR SELECT USING (is_published = true);

-- SEO Pages - Public can read published pages
CREATE POLICY "Public can read published seo pages" ON seo_pages
    FOR SELECT USING (published = true);

-- SEO Templates - Public can read templates (for dynamic generation)
CREATE POLICY "Public can read seo templates" ON seo_templates
    FOR SELECT USING (true);

-- SEO Variation Blocks - Public can read variation blocks (for dynamic generation)
CREATE POLICY "Public can read variation blocks" ON seo_variation_blocks
    FOR SELECT USING (true);

-- =============================================
-- ADMIN POLICIES - FULL CRUD ACCESS
-- =============================================

-- Helper function to check if user is admin
CREATE OR REPLACE FUNCTION is_admin()
RETURNS BOOLEAN AS $$
BEGIN
    RETURN (
        auth.role() = 'authenticated' AND 
        auth.jwt() ->> 'role' = 'admin'
    );
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Locations Province - Admin full access
CREATE POLICY "Admin full access to provinces" ON locations_province
    FOR ALL USING (is_admin());

-- Locations District - Admin full access
CREATE POLICY "Admin full access to districts" ON locations_district
    FOR ALL USING (is_admin());

-- Services - Admin full access
CREATE POLICY "Admin full access to services" ON services
    FOR ALL USING (is_admin());

-- Media - Admin full access
CREATE POLICY "Admin full access to media" ON media
    FOR ALL USING (is_admin());

-- Portfolio Projects - Admin full access
CREATE POLICY "Admin full access to portfolio" ON portfolio_projects
    FOR ALL USING (is_admin());

-- SEO Pages - Admin full access
CREATE POLICY "Admin full access to seo pages" ON seo_pages
    FOR ALL USING (is_admin());

-- SEO Templates - Admin full access
CREATE POLICY "Admin full access to seo templates" ON seo_templates
    FOR ALL USING (is_admin());

-- SEO Variation Blocks - Admin full access
CREATE POLICY "Admin full access to variation blocks" ON seo_variation_blocks
    FOR ALL USING (is_admin());

-- =============================================
-- STORAGE POLICIES
-- =============================================

-- Note: Storage policies are for Supabase Storage
-- For PostgreSQL-only setup, media files are stored in filesystem or S3
-- Storage bucket creation is skipped for direct PostgreSQL setup

-- =============================================
-- FUNCTIONS FOR SEO PAGE GENERATION
-- =============================================

-- Function to generate SEO page slug
CREATE OR REPLACE FUNCTION generate_seo_slug(
    page_type TEXT,
    province_slug TEXT DEFAULT NULL,
    district_slug TEXT DEFAULT NULL,
    service_slug TEXT DEFAULT NULL,
    portfolio_slug TEXT DEFAULT NULL
)
RETURNS TEXT AS $$
BEGIN
    CASE page_type
        WHEN 'province' THEN
            RETURN 'locations/' || province_slug;
        WHEN 'district' THEN
            RETURN 'locations/' || province_slug || '/' || district_slug;
        WHEN 'service' THEN
            RETURN 'services/' || service_slug;
        WHEN 'portfolio' THEN
            RETURN 'portfolio/' || portfolio_slug;
        ELSE
            RAISE EXCEPTION 'Invalid page type: %', page_type;
    END CASE;
END;
$$ LANGUAGE plpgsql;

-- Function to get variation block by hash (deterministic selection)
CREATE OR REPLACE FUNCTION get_variation_block_by_hash(
    block_type_param TEXT,
    hash_input TEXT
)
RETURNS TEXT AS $$
DECLARE
    block_count INTEGER;
    selected_index INTEGER;
    result_text TEXT;
BEGIN
    -- Get total count of blocks for this type
    SELECT COUNT(*) INTO block_count
    FROM seo_variation_blocks
    WHERE block_type = block_type_param;
    
    IF block_count = 0 THEN
        RETURN '';
    END IF;
    
    -- Generate deterministic index based on hash
    selected_index := (hashtext(hash_input) % block_count);
    
    -- Get the selected variation block
    SELECT variant_md INTO result_text
    FROM seo_variation_blocks
    WHERE block_type = block_type_param
    ORDER BY id
    LIMIT 1 OFFSET selected_index;
    
    RETURN COALESCE(result_text, '');
END;
$$ LANGUAGE plpgsql;

-- Function to upsert SEO page
CREATE OR REPLACE FUNCTION upsert_seo_page(
    page_type TEXT,
    province_id_param UUID DEFAULT NULL,
    district_id_param UUID DEFAULT NULL,
    service_id_param UUID DEFAULT NULL,
    portfolio_id_param UUID DEFAULT NULL
)
RETURNS UUID AS $$
DECLARE
    page_id UUID;
    page_slug TEXT;
    page_title TEXT;
    page_meta_description TEXT;
    page_h1 TEXT;
    page_content TEXT;
    page_faq JSONB;
    province_name TEXT;
    district_name TEXT;
    service_name TEXT;
    portfolio_title TEXT;
    hash_input TEXT;
BEGIN
    -- Get related entity names
    IF province_id_param IS NOT NULL THEN
        SELECT name INTO province_name FROM locations_province WHERE id = province_id_param;
    END IF;
    
    IF district_id_param IS NOT NULL THEN
        SELECT name INTO district_name FROM locations_district WHERE id = district_id_param;
    END IF;
    
    IF service_id_param IS NOT NULL THEN
        SELECT name INTO service_name FROM services WHERE id = service_id_param;
    END IF;
    
    IF portfolio_id_param IS NOT NULL THEN
        SELECT title INTO portfolio_title FROM portfolio_projects WHERE id = portfolio_id_param;
    END IF;
    
    -- Generate slug
    page_slug := generate_seo_slug(
        page_type,
        (SELECT slug FROM locations_province WHERE id = province_id_param),
        (SELECT slug FROM locations_district WHERE id = district_id_param),
        (SELECT slug FROM services WHERE id = service_id_param),
        (SELECT slug FROM portfolio_projects WHERE id = portfolio_id_param)
    );
    
    -- Create hash input for deterministic variation selection
    hash_input := COALESCE(province_name, '') || '_' || COALESCE(district_name, '') || '_' || COALESCE(service_name, '') || '_' || COALESCE(portfolio_title, '');
    
    -- Generate content based on page type
    CASE page_type
        WHEN 'province' THEN
            page_title := province_name || ' Mekan Fotoğrafçısı | Profesyonel Mimari ve İç Mekan Fotoğrafçılığı';
            page_meta_description := province_name || '''da profesyonel mekan fotoğrafçılığı hizmetleri. Mimari, iç mekan, emlak ve otel fotoğrafçılığı için uzman ekibimizle iletişime geçin.';
            page_h1 := province_name || ' Mekan Fotoğrafçısı';
            
        WHEN 'district' THEN
            page_title := district_name || ', ' || province_name || ' Mekan Fotoğrafçısı | Profesyonel Fotoğrafçılık Hizmetleri';
            page_meta_description := district_name || ', ' || province_name || '''da mekan fotoğrafçılığı. Mimari, iç mekan, emlak ve otel fotoğrafları için profesyonel hizmet.';
            page_h1 := district_name || ' Mekan Fotoğrafçısı';
            
        WHEN 'service' THEN
            page_title := service_name || ' | Antalya ve Muğla''da Profesyonel Hizmet';
            page_meta_description := 'Antalya ve Muğla bölgesinde ' || service_name || ' hizmetleri. Profesyonel ekipman ve deneyimli ekibimizle kaliteli sonuçlar.';
            page_h1 := service_name;
            
        WHEN 'portfolio' THEN
            page_title := portfolio_title || ' | Mekan Fotoğrafçısı Portfolio';
            page_meta_description := portfolio_title || ' projesi detayları. Profesyonel mekan fotoğrafçılığı örnekleri ve çalışma sürecimiz.';
            page_h1 := portfolio_title;
    END CASE;
    
    -- Generate content using variation blocks
    page_content := get_variation_block_by_hash('intro', hash_input || '_intro') || E'\n\n' ||
                   get_variation_block_by_hash('process', hash_input || '_process') || E'\n\n' ||
                   get_variation_block_by_hash('benefits', hash_input || '_benefits') || E'\n\n' ||
                   get_variation_block_by_hash('cta', hash_input || '_cta');
    
    -- Generate FAQ JSON
    page_faq := jsonb_build_object(
        'questions', jsonb_build_array(
            jsonb_build_object(
                'question', 'Hangi bölgelerde hizmet veriyorsunuz?',
                'answer', 'Antalya ve Muğla bölgesinin tamamında, özellikle ' || COALESCE(district_name, province_name) || ' ve çevresinde profesyonel mekan fotoğrafçılığı hizmetleri sunuyoruz.'
            ),
            jsonb_build_object(
                'question', 'Fotoğraf çekimi ne kadar sürer?',
                'answer', 'Mekanın büyüklüğüne ve çekim türüne göre değişmekle birlikte, ortalama 2-4 saat sürmektedir.'
            ),
            jsonb_build_object(
                'question', 'Fotoğraflar ne zaman teslim edilir?',
                'answer', 'Çekim sonrası 3-5 iş günü içinde düzenlenmiş fotoğraflarınızı teslim ediyoruz.'
            )
        )
    );
    
    -- Upsert the SEO page
    INSERT INTO seo_pages (
        type, province_id, district_id, service_id, portfolio_id,
        slug, title, meta_description, h1, content_md, faq_json, published
    ) VALUES (
        page_type, province_id_param, district_id_param, service_id_param, portfolio_id_param,
        page_slug, page_title, page_meta_description, page_h1, page_content, page_faq, false
    )
    ON CONFLICT (slug) DO UPDATE SET
        title = EXCLUDED.title,
        meta_description = EXCLUDED.meta_description,
        h1 = EXCLUDED.h1,
        content_md = EXCLUDED.content_md,
        faq_json = EXCLUDED.faq_json,
        updated_at = NOW()
    RETURNING id INTO page_id;
    
    RETURN page_id;
END;
$$ LANGUAGE plpgsql;-- Migration: Add content fields to services table
-- Adds description, content, and image fields for service detail pages

ALTER TABLE services
ADD COLUMN IF NOT EXISTS description TEXT,
ADD COLUMN IF NOT EXISTS content TEXT,
ADD COLUMN IF NOT EXISTS image VARCHAR(500);

-- Add comment for documentation
COMMENT ON COLUMN services.description IS 'Detailed description for service detail pages';
COMMENT ON COLUMN services.content IS 'Markdown content for service detail pages';
COMMENT ON COLUMN services.image IS 'Main image URL for service detail pages';


-- Migration: Add content fields to location tables
-- Adds description, content, and image fields for province and district detail pages

-- Add content fields to locations_province
ALTER TABLE locations_province
ADD COLUMN IF NOT EXISTS description TEXT,
ADD COLUMN IF NOT EXISTS content TEXT,
ADD COLUMN IF NOT EXISTS image VARCHAR(500);

-- Add content fields to locations_district
ALTER TABLE locations_district
ADD COLUMN IF NOT EXISTS description TEXT,
ADD COLUMN IF NOT EXISTS content TEXT,
ADD COLUMN IF NOT EXISTS image VARCHAR(500);

-- Add comments for documentation
COMMENT ON COLUMN locations_province.description IS 'Detailed description for province detail pages';
COMMENT ON COLUMN locations_province.content IS 'Markdown content for province detail pages';
COMMENT ON COLUMN locations_province.image IS 'Main image URL for province detail pages';

COMMENT ON COLUMN locations_district.description IS 'Detailed description for district detail pages';
COMMENT ON COLUMN locations_district.content IS 'Markdown content for district detail pages';
COMMENT ON COLUMN locations_district.image IS 'Main image URL for district detail pages';


-- Migration: Create admin users table
-- Simple authentication system for admin panel

CREATE TABLE IF NOT EXISTS admin_users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    last_login_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create index for email lookups
CREATE INDEX IF NOT EXISTS idx_admin_users_email ON admin_users(email);

-- Create trigger for updated_at
CREATE TRIGGER update_admin_users_updated_at BEFORE UPDATE ON admin_users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Insert default admin user (password: admin123)
-- Password hash for 'admin123' using PHP password_hash()
INSERT INTO admin_users (email, password_hash, name, is_active)
VALUES (
    'admin@mekanfotografcisi.tr',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin User',
    true
)
ON CONFLICT (email) DO NOTHING;


-- Migration: Add gallery images field to services table
-- Stores Pexels URLs as JSON array for gallery display

ALTER TABLE services
ADD COLUMN IF NOT EXISTS gallery_images JSONB DEFAULT '[]'::jsonb;

-- Add comment for documentation
COMMENT ON COLUMN services.gallery_images IS 'Array of image URLs (e.g., Pexels links) for gallery display on service detail pages';


-- =============================================
-- WordPress-like Architecture for mekanfotografcisi.tr
-- =============================================

-- 1. Posts Table
-- This will store all primary content: services, locations, portfolio projects, and regular pages.
CREATE TABLE IF NOT EXISTS posts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT, -- Primary markdown or HTML content
    excerpt TEXT, -- Brief summary
    post_type VARCHAR(50) NOT NULL DEFAULT 'page', -- 'page', 'service', 'location', 'portfolio', 'seo_page'
    post_status VARCHAR(20) NOT NULL DEFAULT 'draft', -- 'publish', 'draft', 'trash'
    parent_id UUID REFERENCES posts(id) ON DELETE SET NULL,
    menu_order INTEGER DEFAULT 0,
    author_id UUID, -- For future multi-user support
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. Post Meta Table
-- Stores arbitrary key-value pairs for posts (e.g., SEO metadata, gallery IDs, custom fields).
CREATE TABLE IF NOT EXISTS post_meta (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    post_id UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    meta_key VARCHAR(255) NOT NULL,
    meta_value JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(post_id, meta_key)
);

-- 3. Taxonomies Table
-- For categories, tags, etc.
CREATE TABLE IF NOT EXISTS taxonomies (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 4. Terms Table
-- Individual items within a taxonomy (e.g., "Architecture" category)
CREATE TABLE IF NOT EXISTS terms (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    taxonomy_id UUID NOT NULL REFERENCES taxonomies(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    parent_id UUID REFERENCES terms(id) ON DELETE SET NULL,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(taxonomy_id, slug)
);

-- 5. Term Relationships Table
-- Links posts to terms
CREATE TABLE IF NOT EXISTS term_relationships (
    post_id UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    term_id UUID NOT NULL REFERENCES terms(id) ON DELETE CASCADE,
    PRIMARY KEY (post_id, term_id)
);

-- Indexes for performance
CREATE INDEX idx_posts_slug ON posts(slug);
CREATE INDEX idx_posts_type_status ON posts(post_type, post_status);
CREATE INDEX idx_post_meta_key ON post_meta(meta_key);
CREATE INDEX idx_post_meta_lookup ON post_meta(post_id, meta_key);

-- Triggers for updated_at
CREATE TRIGGER update_posts_updated_at BEFORE UPDATE ON posts FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_post_meta_updated_at BEFORE UPDATE ON post_meta FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TABLE IF NOT EXISTS locations_town (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    district_id UUID NOT NULL REFERENCES locations_district(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL,
    UNIQUE(district_id, slug)
);

CREATE INDEX IF NOT EXISTS idx_locations_town_district_id ON locations_town(district_id);
CREATE INDEX IF NOT EXISTS idx_locations_town_slug ON locations_town(slug);
