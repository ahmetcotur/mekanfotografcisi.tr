<?php
/**
 * JWT Authentication API
 * Handles login, token generation, and validation
 */
// Session is now started globally in router.php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

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

// JWT Secret (in production, use env variable)
$jwtSecret = env('JWT_SECRET', 'mekanfotografcisi_secret_key_2026');
$jwtExpiry = 86400; // 24 hours

/**
 * Generate JWT token
 */
function generateToken($userId, $email, $name)
{
    global $jwtSecret, $jwtExpiry;

    $issuedAt = time();
    $expire = $issuedAt + $jwtExpiry;

    $payload = [
        'iat' => $issuedAt,
        'exp' => $expire,
        'user_id' => $userId,
        'email' => $email,
        'name' => $name
    ];

    if (USE_SIMPLE_TOKEN) {
        // Simple base64 token if JWT library not available
        return base64_encode(json_encode($payload));
    } else {
        return \Firebase\JWT\JWT::encode($payload, $jwtSecret, 'HS256');
    }
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
            $users = $db->select('admin_users', [
                'email.eq' => $email,
                'is_active' => true
            ]);

            if (empty($users)) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
                exit;
            }

            $user = $users[0];

            if (!password_verify($password, $user['password_hash'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
                exit;
            }

            // Generate token
            $token = generateToken($user['id'], $user['email'], $user['name']);

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
                    'name' => $user['name']
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
        $newToken = generateToken($payload['user_id'], $payload['email'], $payload['name']);

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
            'name' => $payload['name']
        ]
    ]);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
