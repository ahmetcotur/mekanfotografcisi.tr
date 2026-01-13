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
