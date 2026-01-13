<?php
/**
 * PHP Built-in Server Router (WordPress-style Refactor)
 */

// Set working directory to project root
chdir(__DIR__);

// Autoload Core classes (simple PSR-4 like autoloader)
spl_autoload_register(function ($class) {
    $prefix = 'Core\\';
    $base_dir = __DIR__ . '/includes/Core/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0)
        return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file))
        include $file;
});

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

use Core\Post;
use Core\TemplateLoader;

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';

// Force cleanup of common prefixes that cause 404s
$requestPath = str_replace('/index.php', '', $requestPath);
$requestPath = trim($requestPath, '/');

// CRITICAL: Static files MUST be checked BEFORE session starts
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|webp|ico|woff|woff2|ttf|eot|pdf|xml|txt)$/i', $requestPath)) {
    return false;
}

// Global Session (Ensures consistency across /login, /admin, and site)
if (session_status() === PHP_SESSION_NONE) {
    // Production-safe session configuration
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_lifetime', '86400'); // 24 hours

    session_name('MF_SESSION');
    session_start();

    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Login Route
if ($requestPath === 'login') {
    if (isset($_SESSION['admin_user_id'])) {
        header('Location: /admin/');
        exit;
    }
    require_once __DIR__ . '/login.php';
    exit;
}

// 1. Admin Panel Route
if ($requestPath === 'admin' || strpos($requestPath, 'admin/') === 0) {
    // Serve the admin controller
    require_once __DIR__ . '/admin/index.php';
    exit;
}

// API endpoints
if (strpos($requestPath, 'api') === 0 || strpos($requestPath, '/api') === 0) {
    $file = __DIR__ . '/' . ltrim($requestPath, '/');
    if (file_exists($file) && !is_dir($file))
        return false;
}

// Specific root-level PHP files (auto_login.php, etc.)
if (preg_match('/\.php$/', $requestPath)) {
    $file = __DIR__ . '/' . ltrim($requestPath, '/');
    if (file_exists($file))
        return false;
}

// Global DB instance
$db = new DatabaseClient();

// Auto-seed for local environment if database is empty
if (env('APP_ENV') === 'local' || env('APP_ENV') === 'development') {
    try {
        $check = $db->select('locations_province', ['limit' => 1]);
        if (empty($check)) {
            require_once __DIR__ . '/scripts/seed-locations.php';
        }
    } catch (Exception $e) {
        error_log("Auto-seeding check failed: " . $e->getMessage());
    }
}

// Dynamic Content Lookup
$slug = trim($requestPath, '/');
if ($slug === '') {
    $post = Post::findBySlug('homepage', $db);
} else {
    // 1. Try to find existing post
    $post = Post::findBySlug($slug, $db);

    // 2. If not found, try to discover/generate it
    if (!$post) {
        $discoverer = new Core\ContentDiscoverer($db);
        $post = $discoverer->discover($slug);
    }
}

// 3. Status VERIFICATION (Passive Dependency Check)
if ($post && $post->post_type === 'seo_page') {
    // A. Check Province Status
    $province_id = $post->getMeta('province_id');
    $province = null;

    if ($province_id) {
        // Method 1: ID lookup
        $prov = $db->select('locations_province', ['id' => $province_id]);
        $province = $prov[0] ?? null;
    } else {
        // Method 2: Fallback to Slug derivation (e.g. locations/adana)
        if (preg_match('/^locations\/([a-z0-9-]+)$/', $post->slug, $matches)) {
            $provSlug = $matches[1];
            $prov = $db->select('locations_province', ['slug' => $provSlug]);
            $province = $prov[0] ?? null;
        }

        // Method 3: Fallback to location_name meta
        if (!$province) {
            $locName = $post->getMeta('location_name'); // Returns string if single=true
            if ($locName) {
                $prov = $db->select('locations_province', ['name' => $locName]);
                $province = $prov[0] ?? null;
            }
        }
    }

    // Perform Province Active Check
    if ($province) {
        // cast to string to be safe against bool or string types in DB
        $isActive = (string) $province['is_active'];
        if ($isActive === 'false' || $isActive === '' || $isActive === '0') {
            $post = null; // Force 404
        }
    }

    // B. Check District Status (if applicable)
    // Legacy pages might not have district_id, need similar fallback if we had district pages.
    // For now, districts usually have province_id set if generated recently.
    if ($post) {
        $district_id = $post->getMeta('district_id');
        if ($district_id) {
            $dist = $db->select('locations_district', ['id' => $district_id]);
            if (!empty($dist)) {
                $isActive = (string) $dist[0]['is_active'];
                if ($isActive === 'false' || $isActive === '' || $isActive === '0') {
                    $post = null;
                }
            }
        }
    }

    // C. Check Service Status (if applicable)
    if ($post) {
        $service_id = $post->getMeta('service_id');
        if ($service_id) {
            $serv = $db->select('services', ['id' => $service_id]);
            if (!empty($serv)) {
                $isActive = (string) $serv[0]['is_active'];
                if ($isActive === 'false' || $isActive === '' || $isActive === '0') {
                    $post = null;
                }
            }
        }
    }
}

// Load Template
$templateLoader = new TemplateLoader(__DIR__ . '/templates/hierarchy', $post);
$template = $templateLoader->getTemplate();

if ($template) {
    $templateLoader->render($template);
    return true;
}

// Final fallback - 404
http_response_code(404);
$templateLoader = new TemplateLoader(__DIR__ . '/templates/hierarchy', null);
$templateLoader->render($templateLoader->getTemplate());
return true;
