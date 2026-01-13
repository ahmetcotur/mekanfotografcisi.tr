<?php
/**
 * Fix Admin Password
 * Updates the admin user password with correct hash
 */

require_once __DIR__ . '/../includes/database.php';

$db = new DatabaseClient();
$newHash = password_hash('admin123', PASSWORD_DEFAULT);

echo "Updating admin password...\n";
echo "New hash: $newHash\n\n";

try {
    $result = $db->update('admin_users', [
        'password_hash' => $newHash
    ], [
        'email' => 'admin@mekanfotografcisi.tr'
    ]);
    
    echo "✓ Password updated successfully!\n";
    echo "You can now login with:\n";
    echo "  Email: admin@mekanfotografcisi.tr\n";
    echo "  Password: admin123\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}


