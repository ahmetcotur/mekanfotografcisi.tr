-- Add featured_image column to posts table
ALTER TABLE posts ADD COLUMN IF NOT EXISTS featured_image VARCHAR(500);

-- Add comment
COMMENT ON COLUMN posts.featured_image IS 'URL of the featured/cover image for the post';
