<?php
// Admin Panel Entry Point

// Ensure user is authenticated (using existing logic or improved one)
require_once __DIR__ . '/../includes/database.php';
$db = new DatabaseClient();

// Simple session check (placeholder - we'll integrate better auth later)
/*
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
*/

// Get the current page from query string or URL rewrite
$page = $_GET['page'] ?? 'dashboard';

// Allowed pages
$allowed_pages = [
    'dashboard',
    'services',
    'locations',
    'seo-pages',
    'media',
    'settings',
    'quotes',
    'editor',
    'editor-legacy'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Page Titles
$page_titles = [
    'dashboard' => 'Dashboard',
    'services' => 'Hizmet Yönetimi',
    'locations' => 'Lokasyon Yönetimi',
    'seo-pages' => 'SEO Sayfaları',
    'media' => 'Medya Kütüphanesi',
    'settings' => 'Ayarlar',
    'quotes' => 'Teklifler & Talepler',
    'editor' => 'İçerik Düzenle',
    'editor-legacy' => 'Lokasyon Düzenle'
];

$page_title = $page_titles[$page];

// Extract View
$view_file = __DIR__ . '/views/' . $page . '.php';

// If view doesn't exist, show 404
if (!file_exists($view_file)) {
    // $view_file = __DIR__ . '/views/404.php'; // or fallback
    die("View not found: $page");
}

// Include Layout
include __DIR__ . '/includes/header.php';
include $view_file;
include __DIR__ . '/includes/footer.php';
