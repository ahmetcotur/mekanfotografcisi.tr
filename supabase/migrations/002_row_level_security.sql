-- =============================================
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
$$ LANGUAGE plpgsql;