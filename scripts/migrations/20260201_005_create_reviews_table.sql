-- Reviews: a small trust signal for the public directory. One review per
-- completed assignment (UNIQUE on quote_assignment_id), left by the client.
-- freelancer_applications.rating_avg/rating_count are denormalized copies kept
-- in sync from application code (includes/Core/ReviewService.php), matching how
-- this codebase already avoids DB triggers in favor of PHP-side updates.

CREATE TABLE IF NOT EXISTS reviews (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    freelancer_id INTEGER NOT NULL REFERENCES freelancer_applications(id) ON DELETE CASCADE,
    quote_assignment_id INTEGER REFERENCES quote_assignments(id) UNIQUE,
    client_user_id UUID REFERENCES users(id),
    rating SMALLINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    is_published BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- STATEMENT

CREATE INDEX IF NOT EXISTS idx_reviews_freelancer ON reviews(freelancer_id);
