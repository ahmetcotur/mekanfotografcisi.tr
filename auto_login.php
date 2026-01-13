<?php
/**
 * Auto-login Helper
 * Sets session variables to bypass admin login for testing.
 */
session_start();

require_once __DIR__ . '/includes/database.php';
$db = new DatabaseClient();

$users = $db->select('admin_users', [
    'email' => 'info@mekanfotografsici.tr',
    'is_active' => true
]);

if (!empty($users)) {
    $user = $users[0];
    $_SESSION['admin_user_id'] = $user['id'];
    $_SESSION['admin_user_email'] = $user['email'];
    $_SESSION['admin_user_name'] = $user['name'];

    // For compatibility
    $_SESSION['user_id'] = $user['id'];

    header('Location: /admin/');
    exit;
} else {
    echo "Admin user not found. Lütfen email kontrol ediniz: info@mekanfotografsici.tr";
}
