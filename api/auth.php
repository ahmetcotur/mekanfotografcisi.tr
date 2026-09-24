<?php
/**
 * JWT Authentication API
 * Handles login, token generation, and validation
 */
// Session is now started globally in router.php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/config.php';

// Load composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Check if JWT library is available
define('USE_SIMPLE_TOKEN', !class_exists('Firebase\JWT\JWT'));

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = new DatabaseClient();
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? 'login';

// JWT Secret
$jwtSecret = mf_jwt_secret();
$jwtExpiry = 86400; // 24 hours

/**
 * Generate JWT token. $role is 'admin', 'freelancer', or 'client'.
 */
function generateToken($userId, $email, $name, $role = 'admin')
{
    global $jwtExpiry;
    return mf_generate_jwt($userId, $email, $name, $role, $jwtExpiry);
}

/**
 * Validate JWT token
 */
function validateToken($token)
{
    global $jwtSecret;

    try {
        if (USE_SIMPLE_TOKEN) {
            $payload = json_decode(base64_decode($token), true);
            if ($payload && isset($payload['exp']) && $payload['exp'] > time()) {
                return $payload;
            }
            return null;
        } else {
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($jwtSecret, 'HS256'));
            return (array) $decoded;
        }
    } catch (Exception $e) {
        return null;
    }
}

// Routes
if ($method === 'POST') {
    if ($path === 'login') {
        // Login endpoint
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email and password required']);
            exit;
        }

        try {
            // Admin accounts first (unchanged behavior/table), then the shared
            // freelancer/client users table. One login endpoint, two backing
            // tables — not three parallel auth systems.
            $admins = $db->select('admin_users', [
                'email.eq' => $email,
                'is_active' => true
            ]);

            if (!empty($admins)) {
                $user = $admins[0];

                if (!password_verify($password, $user['password_hash'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
                    exit;
                }

                $token = generateToken($user['id'], $user['email'], $user['name'], 'admin');

                // Also set session for backward compatibility
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_user_email'] = $user['email'];
                $_SESSION['admin_user_name'] = $user['name'];
                $_SESSION['user_id'] = $user['id'];

                echo json_encode([
                    'success' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['name'],
                        'role' => 'admin'
                    ]
                ]);
                exit;
            }

            $marketplaceUsers = $db->select('users', [
                'email.eq' => $email,
                'is_active' => true
            ]);

            if (empty($marketplaceUsers)) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
                exit;
            }

            $user = $marketplaceUsers[0];

            if (!password_verify($password, $user['password_hash'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
                exit;
            }

            $token = generateToken($user['id'], $user['email'], $user['name'], $user['role']);

            $db->update('users', ['last_login_at' => date('Y-m-d H:i:s')], ['id' => $user['id']]);

            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    } elseif ($path === 'refresh') {
        // Refresh token endpoint
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No token provided']);
            exit;
        }

        $payload = validateToken($token);

        if (!$payload) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid token']);
            exit;
        }

        // Generate new token
        $newToken = generateToken($payload['user_id'], $payload['email'], $payload['name'], $payload['role'] ?? 'admin');

        echo json_encode([
            'success' => true,
            'token' => $newToken
        ]);
    } elseif ($path === 'logout') {
        // Logout endpoint
        session_destroy();
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
} elseif ($method === 'GET' && $path === 'verify') {
    // Verify token endpoint
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $authHeader);

    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No token provided']);
        exit;
    }

    $payload = validateToken($token);

    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $payload['user_id'],
            'email' => $payload['email'],
            'name' => $payload['name'],
            'role' => $payload['role'] ?? 'admin'
        ]
    ]);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
