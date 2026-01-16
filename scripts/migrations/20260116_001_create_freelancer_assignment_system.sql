-- Freelancer Assignment System Migration
-- Creates tables and columns needed for the freelancer job assignment feature

-- 1. Add working_regions column to freelancer_applications if it doesn't exist
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'freelancer_applications' 
        AND column_name = 'working_regions'
    ) THEN
        ALTER TABLE freelancer_applications 
        ADD COLUMN working_regions JSONB DEFAULT '[]';
        
        COMMENT ON COLUMN freelancer_applications.working_regions IS 
        'Stores regions where freelancer can work. Format: [{"province_id": 34, "province_name": "İstanbul", "districts": [1, 2, 3]}]';
    END IF;
END $$;

-- 2. Create quote_assignments table if it doesn't exist
CREATE TABLE IF NOT EXISTS quote_assignments (
    id SERIAL PRIMARY KEY,
    quote_id INTEGER NOT NULL REFERENCES quotes(id) ON DELETE CASCADE,
    freelancer_id INTEGER NOT NULL REFERENCES freelancer_applications(id) ON DELETE CASCADE,
    status VARCHAR(50) DEFAULT 'pending', -- pending, accepted, rejected, completed
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INTEGER, -- admin user id
    freelancer_note TEXT,
    admin_note TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(quote_id, freelancer_id)
);

-- 3. Create indexes for better query performance
CREATE INDEX IF NOT EXISTS idx_quote_assignments_quote_id 
    ON quote_assignments(quote_id);

CREATE INDEX IF NOT EXISTS idx_quote_assignments_freelancer_id 
    ON quote_assignments(freelancer_id);

CREATE INDEX IF NOT EXISTS idx_quote_assignments_status 
    ON quote_assignments(status);

CREATE INDEX IF NOT EXISTS idx_quote_assignments_assigned_at 
    ON quote_assignments(assigned_at DESC);

-- 4. Add comments for documentation
COMMENT ON TABLE quote_assignments IS 
'Stores assignments between quote requests and freelancers';

COMMENT ON COLUMN quote_assignments.status IS 
'Assignment status: pending (waiting for freelancer response), accepted (freelancer accepted), rejected (freelancer declined), completed (job finished)';

COMMENT ON COLUMN quote_assignments.assigned_by IS 
'ID of the admin user who made the assignment';

-- 5. Create a function to update the updated_at timestamp
CREATE OR REPLACE FUNCTION update_quote_assignment_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 6. Create trigger to automatically update updated_at
DROP TRIGGER IF EXISTS trigger_update_quote_assignment_timestamp ON quote_assignments;
CREATE TRIGGER trigger_update_quote_assignment_timestamp
    BEFORE UPDATE ON quote_assignments
    FOR EACH ROW
    EXECUTE FUNCTION update_quote_assignment_timestamp();
