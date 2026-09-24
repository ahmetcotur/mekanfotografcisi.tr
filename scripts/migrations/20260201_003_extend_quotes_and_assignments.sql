-- Link quotes to logged-in clients and mark whether a request is open to the whole
-- marketplace or was placed directly with one photographer. Anonymous submissions
-- (no user_id) keep working exactly as before.

ALTER TABLE quotes ADD COLUMN IF NOT EXISTS user_id UUID REFERENCES users(id);

-- STATEMENT

ALTER TABLE quotes ADD COLUMN IF NOT EXISTS visibility VARCHAR(20) DEFAULT 'marketplace' CHECK (visibility IN ('marketplace', 'direct'));

-- STATEMENT

ALTER TABLE quotes ADD COLUMN IF NOT EXISTS preferred_freelancer_id INTEGER REFERENCES freelancer_applications(id);

-- STATEMENT

CREATE INDEX IF NOT EXISTS idx_quotes_user_id ON quotes(user_id);

-- STATEMENT

CREATE INDEX IF NOT EXISTS idx_quotes_visibility_status ON quotes(visibility, status);

-- STATEMENT

-- Tracks how an assignment came to be: manually by an admin (existing behavior),
-- self-claimed by a freelancer browsing open requests, or created directly from a
-- client's "contact this photographer" request on a public profile page.
ALTER TABLE quote_assignments ADD COLUMN IF NOT EXISTS source VARCHAR(20) DEFAULT 'admin' CHECK (source IN ('admin', 'self_claim', 'direct_request'));
