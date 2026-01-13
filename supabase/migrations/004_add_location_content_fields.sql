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


