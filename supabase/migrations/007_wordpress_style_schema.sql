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
