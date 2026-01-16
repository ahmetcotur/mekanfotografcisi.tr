-- Freelancer Assignment System Migration
-- Creates tables and columns needed for the freelancer job assignment feature

-- 1. Add working_regions column to freelancer_applications if it doesn't exist
ALTER TABLE freelancer_applications ADD COLUMN IF NOT EXISTS working_regions JSONB DEFAULT '[]';

-- 2. Create quote_assignments table if it doesn't exist
CREATE TABLE IF NOT EXISTS quote_assignments (
    id SERIAL PRIMARY KEY,
    quote_id INTEGER NOT NULL REFERENCES quotes(id) ON DELETE CASCADE,
    freelancer_id INTEGER NOT NULL REFERENCES freelancer_applications(id) ON DELETE CASCADE,
    status VARCHAR(50) DEFAULT 'pending',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INTEGER,
    freelancer_note TEXT,
    admin_note TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(quote_id, freelancer_id)
);

-- 3. Create indexes for better query performance
CREATE INDEX IF NOT EXISTS idx_quote_assignments_quote_id ON quote_assignments(quote_id);
CREATE INDEX IF NOT EXISTS idx_quote_assignments_freelancer_id ON quote_assignments(freelancer_id);
CREATE INDEX IF NOT EXISTS idx_quote_assignments_status ON quote_assignments(status);
CREATE INDEX IF NOT EXISTS idx_quote_assignments_assigned_at ON quote_assignments(assigned_at DESC);
