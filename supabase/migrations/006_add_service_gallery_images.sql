-- Migration: Add gallery images field to services table
-- Stores Pexels URLs as JSON array for gallery display

ALTER TABLE services
ADD COLUMN IF NOT EXISTS gallery_images JSONB DEFAULT '[]'::jsonb;

-- Add comment for documentation
COMMENT ON COLUMN services.gallery_images IS 'Array of image URLs (e.g., Pexels links) for gallery display on service detail pages';


