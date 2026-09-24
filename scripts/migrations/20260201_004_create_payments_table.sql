-- Payments: one row per Stripe Checkout attempt for a booking deposit/commission
-- tied to a quote_assignment. Deliberately minimal - no subscriptions, no escrow,
-- no split payments. Payment status is tracked independently of the
-- quote_assignment's own status (fulfillment and payment are separate concerns).

CREATE TABLE IF NOT EXISTS payments (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    quote_assignment_id INTEGER REFERENCES quote_assignments(id),
    quote_id INTEGER REFERENCES quotes(id),
    freelancer_id INTEGER REFERENCES freelancer_applications(id),
    payer_user_id UUID REFERENCES users(id),
    type VARCHAR(30) NOT NULL DEFAULT 'booking_deposit' CHECK (type IN ('booking_deposit', 'commission', 'full_payment')),
    amount NUMERIC(10,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'TRY',
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'succeeded', 'failed', 'refunded')),
    provider VARCHAR(30) NOT NULL DEFAULT 'stripe',
    provider_session_id VARCHAR(255),
    provider_payment_intent_id VARCHAR(255),
    raw_response JSONB,
    failure_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- STATEMENT

CREATE INDEX IF NOT EXISTS idx_payments_assignment ON payments(quote_assignment_id);

-- STATEMENT

CREATE INDEX IF NOT EXISTS idx_payments_freelancer ON payments(freelancer_id);

-- STATEMENT

CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(status);

-- STATEMENT

CREATE UNIQUE INDEX IF NOT EXISTS idx_payments_session ON payments(provider_session_id) WHERE provider_session_id IS NOT NULL;
