<?php
/**
 * Migration: Add Freelancer Working Regions and Quote Assignments
 * Date: 2026-01-16
 */

require_once __DIR__ . '/../includes/database.php';

try {
    $db = new DatabaseClient();
    $conn = $db->getConnection();

    echo "Starting migration: Add Freelancer Working Regions and Quote Assignments\n";

    // 1. Add working_regions column to freelancer_applications
    echo "Adding working_regions column to freelancer_applications...\n";
    $conn->exec("
        ALTER TABLE freelancer_applications 
        ADD COLUMN IF NOT EXISTS working_regions JSONB DEFAULT '[]'::jsonb
    ");
    echo "✓ Added working_regions column\n";

    // 2. Create quote_assignments table
    echo "Creating quote_assignments table...\n";
    $conn->exec("
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
        )
    ");
    echo "✓ Created quote_assignments table\n";

    // 3. Create index for faster lookups
    echo "Creating indexes...\n";
    $conn->exec("
        CREATE INDEX IF NOT EXISTS idx_quote_assignments_quote_id 
        ON quote_assignments(quote_id)
    ");
    $conn->exec("
        CREATE INDEX IF NOT EXISTS idx_quote_assignments_freelancer_id 
        ON quote_assignments(freelancer_id)
    ");
    $conn->exec("
        CREATE INDEX IF NOT EXISTS idx_quote_assignments_status 
        ON quote_assignments(status)
    ");
    echo "✓ Created indexes\n";

    // 4. Add comment to working_regions column
    $conn->exec("
        COMMENT ON COLUMN freelancer_applications.working_regions IS 
        'JSON array of working regions: [{\"province_id\": 34, \"province_name\": \"İstanbul\", \"district_ids\": [1,2,3]}]'
    ");

    echo "\n✅ Migration completed successfully!\n";

} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
