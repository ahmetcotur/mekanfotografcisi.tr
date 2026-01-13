-- Migration: Add content fields to services table
-- Adds description, content, and image fields for service detail pages

ALTER TABLE services
ADD COLUMN IF NOT EXISTS description TEXT,
ADD COLUMN IF NOT EXISTS content TEXT,
ADD COLUMN IF NOT EXISTS image VARCHAR(500);

-- Add comment for documentation
COMMENT ON COLUMN services.description IS 'Detailed description for service detail pages';
COMMENT ON COLUMN services.content IS 'Markdown content for service detail pages';
COMMENT ON COLUMN services.image IS 'Main image URL for service detail pages';


