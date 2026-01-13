<?php
/**
 * Auto-login Helper
 * Sets session variables to bypass admin login for testing.
 */
session_start();

require_once __DIR__ . '/includes/database.php';
$db = new DatabaseClient();

$users = $db->select('admin_users', [
    'email' => 'admin@mekanfotografcisi.tr',
    'is_active' => true
]);

if (!empty($users)) {
    $user = $users[0];
    $_SESSION['admin_user_id'] = $user['id'];
    $_SESSION['admin_user_email'] = $user['email'];
    $_SESSION['admin_user_name'] = $user['name'];
    header('Location: /admin/index.html');
    exit;
} else {
    echo "Admin user not found.";
}
