-- Create freelancer_applications table
-- Stores applications from photographers who want to join as freelancers

CREATE TABLE IF NOT EXISTS freelancer_applications (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    city VARCHAR(100) NOT NULL,
    experience VARCHAR(50) NOT NULL,
    specialization JSONB NOT NULL,
    portfolio_url TEXT,
    message TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT
);

-- Create index on status for filtering
CREATE INDEX IF NOT EXISTS idx_freelancer_applications_status ON freelancer_applications(status);

-- Create index on created_at for sorting
CREATE INDEX IF NOT EXISTS idx_freelancer_applications_created_at ON freelancer_applications(created_at DESC);

-- Add comment
COMMENT ON TABLE freelancer_applications IS 'Stores freelancer photographer applications';
