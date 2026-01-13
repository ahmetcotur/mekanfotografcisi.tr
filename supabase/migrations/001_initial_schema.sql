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
CREATE TRIGGER update_seo_variation_blocks_updated_at BEFORE UPDATE ON seo_variation_blocks FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();