<?php
/**
 * Migration Script: Create pexels_images table
 */
require_once __DIR__ . '/../includes/database.php';

try {
    $db = new DatabaseClient();

    echo "Creating pexels_images table...\n";

    $sql = "
    CREATE TABLE IF NOT EXISTS pexels_images (
        id SERIAL PRIMARY KEY,
        image_url TEXT NOT NULL,
        photographer VARCHAR(255),
        is_visible BOOLEAN DEFAULT TRUE,
        display_order INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE INDEX IF NOT EXISTS idx_pexels_visible ON pexels_images(is_visible);
    CREATE INDEX IF NOT EXISTS idx_pexels_order ON pexels_images(display_order);
    ";

    // Use exec() directly on the PDO connection for DDL statements
    $db->getConnection()->exec($sql);

    echo "✅ Table created successfully.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
