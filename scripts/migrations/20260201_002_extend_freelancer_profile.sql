-- Extend freelancer_applications into a full self-service freelancer profile.
-- The table keeps its original name/shape (application status, matching fields) and
-- gains login linkage + public-directory profile fields, rather than being replaced.

ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS user_id UUID REFERENCES users(id);

-- STATEMENT

ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS slug VARCHAR(160);

-- STATEMENT

CREATE UNIQUE INDEX IF NOT EXISTS idx_freelancer_applications_slug ON freelancer_applications(slug) WHERE slug IS NOT NULL;

-- STATEMENT

CREATE UNIQUE INDEX IF NOT EXISTS idx_freelancer_applications_user_id ON freelancer_applications(user_id) WHERE user_id IS NOT NULL;

-- STATEMENT

ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS bio TEXT;

-- STATEMENT

ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS avatar_media_id UUID REFERENCES media(id);

-- STATEMENT

ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS cover_media_id UUID REFERENCES media(id);

-- STATEMENT

ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS portfolio_media_ids JSONB DEFAULT '[]';

-- STATEMENT

-- Separate from status='approved': admin approves the application, but can still
-- pull a live profile from the public directory independently (e.g. on leave).
ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS is_public BOOLEAN DEFAULT false;

-- STATEMENT

ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS rating_avg NUMERIC(3,2) DEFAULT 0;

-- STATEMENT

ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS rating_count INTEGER DEFAULT 0;

-- STATEMENT

CREATE INDEX IF NOT EXISTS idx_freelancer_applications_public ON freelancer_applications(is_public) WHERE is_public = true;
