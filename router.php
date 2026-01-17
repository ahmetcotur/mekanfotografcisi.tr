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
require_once __DIR__ . '/scripts/run_migrations.php';

// Sync Database Migrations (Low overhead check)
// run_migrations(); // Disabled for production stability

use Core\Post;
use Core\TemplateLoader;

$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';

// Force cleanup of common prefixes that cause 404s
$requestPath = str_replace('/index.php', '', $requestPath);
$requestPath = trim($requestPath, '/');

// Debug logging
file_put_contents(__DIR__ . '/debug_log.txt', "[" . date('Y-m-d H:i:s') . "] Path: " . $requestPath . "\n", FILE_APPEND);

// 1. Explicit Routing for SEO files
if ($requestPath === 'sitemap.xml') {
    require_once __DIR__ . '/sitemap.php';
    exit;
}

if ($requestPath === 'robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    readfile(__DIR__ . '/robots.txt');
    exit;
}

// CRITICAL: Static files MUST be handled BEFORE anything else
if (preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|webp|ico|woff|woff2|ttf|eot|pdf|xml|txt)$/i', $requestPath)) {
    return false;
}

// 301 Redirects for Legacy URLs
$redirects = [
    'locations' => 'hizmet-bolgeleri',
    'services' => 'hizmetlerimiz'
];

if (isset($redirects[$requestPath])) {
    header("Location: /" . $redirects[$requestPath], true, 301);
    exit;
}

// Redirect services/* to hizmetlerimiz/*
if (strpos($requestPath, 'services/') === 0) {
    $newPath = str_replace('services/', 'hizmetlerimiz/', $requestPath);
    header("Location: /" . $newPath, true, 301);
    exit;
}

// Redirect locations/* to hizmet-bolgeleri/*
if (strpos($requestPath, 'locations/') === 0) {
    $newPath = str_replace('locations/', 'hizmet-bolgeleri/', $requestPath);
    header("Location: /" . $newPath, true, 301);
    exit;
}

// Global Session (Ensures consistency across /login, /admin, and site)
if (session_status() === PHP_SESSION_NONE) {
    // CRITICAL: Set explicit session save path for nginx/PHP-FPM
    $sessionPath = sys_get_temp_dir() . '/php_sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0777, true);
    }
    ini_set('session.save_path', $sessionPath);
    ini_set('session.gc_maxlifetime', '86400');

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

// Login Route - MUST exit before template loader
if ($requestPath === 'login') {
    if (isset($_SESSION['admin_user_id'])) {
        header('Location: /admin/');
        exit;
    }
    ob_start(); // Prevent any output before login.php
    require_once __DIR__ . '/login.php';
    ob_end_flush();
    exit; // CRITICAL: Must exit to prevent template loader
}

// Admin SPA Route - Serve React app
if ($requestPath === 'admin' || $requestPath === 'admin/' || strpos($requestPath, 'admin/') === 0) {
    // If requesting a specific file in admin directory, serve it directly
    $filePath = __DIR__ . '/' . $requestPath;
    if (file_exists($filePath) && is_file($filePath)) {
        // Determine content type
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'svg' => 'image/svg+xml',
            'html' => 'text/html'
        ];
        if (isset($contentTypes[$ext])) {
            header('Content-Type: ' . $contentTypes[$ext]);
        }
        readfile($filePath);
        exit;
    }

    // Otherwise serve index.html for SPA routing
    $indexPath = __DIR__ . '/admin/index.html';
    if (file_exists($indexPath)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($indexPath);
        exit;
    }

    // Fallback to old admin if SPA not built yet
    ob_start();
    require_once __DIR__ . '/admin-legacy/index.php';
    ob_end_flush();
    exit;
}

// Old Admin Panel Route (legacy - will be removed)
if ($requestPath === 'admin-legacy' || strpos($requestPath, 'admin-legacy/') === 0) {
    ob_start();
    require_once __DIR__ . '/admin-legacy/index.php';
    ob_end_flush();
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
$parts = explode('/', $slug);
$serviceBase = get_setting('seo_service_base', 'hizmetlerimiz');

if ($slug === '') {
    $post = Post::findBySlug('homepage', $db);
} else {
    // 1. Try to find existing post
    $post = Post::findBySlug($slug, $db);

    // 2. Enforce Service Base Prefix
    if ($post && $post->post_type === 'service') {
        // Remove prefix if it already exists in the post slug to avoid doubling
        $cleanSlug = preg_replace('/^' . preg_quote($serviceBase, '/') . '\//', '', $post->slug);

        // Correct path should be {serviceBase}/{slug_without_base}
        $expectedPath = $serviceBase . '/' . $cleanSlug;
        if ($slug !== $expectedPath) {
            header("Location: /" . $expectedPath, true, 301);
            exit;
        }
    }

    // 3. If not found, try to discover/generate it
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
        // Method 2: Fallback to Slug derivation (e.g. hizmet-bolgeleri/adana or hizmet-bolgeleri/antalya/muratpasa)
        if (preg_match('/^(locations|hizmet-bolgeleri)\/([a-z0-9-]+)(\/([a-z0-9-]+))?$/', $post->slug, $matches)) {
            $provSlug = $matches[2];
            $distSlug = $matches[4] ?? null;

            $prov = $db->select('locations_province', ['slug' => $provSlug]);
            $province = $prov[0] ?? null;

            if ($distSlug && $province) {
                $dist = $db->select('locations_district', [
                    'slug' => $distSlug,
                    'province_id' => $province['id']
                ]);
                $district = $dist[0] ?? null;
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

    // B. Check District and Town Status (if applicable)
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

        if ($post) {
            $town_id = $post->getMeta('town_id');
            if ($town_id) {
                $town = $db->select('locations_town', ['id' => $town_id]);
                if (!empty($town)) {
                    $isActive = (string) $town[0]['is_active'];
                    if ($isActive === 'false' || $isActive === '' || $isActive === '0') {
                        $post = null;
                    }
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
