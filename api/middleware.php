<?php
/**
 * API Middleware
 * Common functions for API authentication and validation
 */

// Load composer autoloader if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * Add CORS headers to response
 */
function addCorsHeaders()
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Handle preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Validate JWT token from Authorization header
 * Returns user data if valid, null if invalid
 */
function validateAuthToken()
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

    if (empty($authHeader)) {
        return null;
    }

    $token = str_replace('Bearer ', '', $authHeader);

    if (empty($token)) {
        return null;
    }

    // JWT Secret
    $jwtSecret = env('JWT_SECRET', 'mekanfotografcisi_secret_key_2026');

    try {
        if (class_exists('Firebase\JWT\JWT')) {
            $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($jwtSecret, 'HS256'));
            return (array) $decoded;
        } else {
            // Fallback: simple base64 token
            $payload = json_decode(base64_decode($token), true);
            if ($payload && isset($payload['exp']) && $payload['exp'] > time()) {
                return $payload;
            }
            return null;
        }
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Require authentication
 * Validates token and returns user data or exits with 401
 */
function requireAuth()
{
    $user = validateAuthToken();

    if (!$user) {
        // Fallback to session-based auth for backward compatibility
        if (isset($_SESSION['admin_user_id'])) {
            return [
                'user_id' => $_SESSION['admin_user_id'],
                'email' => $_SESSION['admin_user_email'] ?? '',
                'name' => $_SESSION['admin_user_name'] ?? 'Admin'
            ];
        }

        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    return $user;
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 */
function jsonError($message, $statusCode = 400)
{
    jsonResponse(['success' => false, 'error' => $message], $statusCode);
}

/**
 * Send success response
 */
function jsonSuccess($data = [])
{
    jsonResponse(array_merge(['success' => true], $data));
}
