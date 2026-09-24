-- Create users table
-- Shared account table for the two self-service marketplace roles: freelancer and client.
-- Admin accounts remain in the separate admin_users table (not merged here) to keep
-- role-escalation risk contained to a table nothing public-facing writes to.

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;

-- STATEMENT

CREATE TABLE IF NOT EXISTS users (
    id UUID DEFAULT uuid_generate_v4() PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('freelancer', 'client')),
    name VARCHAR(150),
    phone VARCHAR(50),
    is_active BOOLEAN DEFAULT true,
    email_verified_at TIMESTAMP,
    last_login_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- STATEMENT

CREATE UNIQUE INDEX IF NOT EXISTS idx_users_email ON users(LOWER(email));

-- STATEMENT

CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- STATEMENT

COMMENT ON TABLE users IS 'Freelancer and client marketplace accounts (role column discriminates); admin accounts stay in admin_users';
