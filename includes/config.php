<?php
/**
 * Configuration Loader
 * Loads environment variables from .env file if available
 * Falls back to system environment variables
 */

function loadEnvFile($path = null) {
    if ($path === null) {
        $path = dirname(__DIR__) . '/.env';
    }
    
    if (!file_exists($path)) {
        return; // .env file doesn't exist, use system env vars
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Only set if not already in environment
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Load .env file if it exists
loadEnvFile();

/**
 * Get environment variable with fallback
 */
function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Convert string boolean values
    if ($value === 'true') {
        return true;
    }
    if ($value === 'false') {
        return false;
    }
    
    return $value;
}

/**
 * Get the JWT signing secret.
 *
 * There is no insecure default in production: if JWT_SECRET is unset, every
 * authenticated request fails loudly (500) instead of silently accepting
 * tokens signed with a secret that has been sitting in the repo's history.
 * Local/dev environments (APP_ENV unset or 'local') get a clearly-marked
 * fallback so `php -S` development keeps working without a .env file.
 */
function mf_jwt_secret() {
    $secret = env('JWT_SECRET');

    if (!empty($secret)) {
        return $secret;
    }

    $appEnv = env('APP_ENV', 'local');
    if ($appEnv !== 'local') {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Server misconfigured: JWT_SECRET is not set']);
        exit;
    }

    return 'dev-only-insecure-secret-set-JWT_SECRET-in-.env';
}

/**
 * Issue a JWT for any of the three roles (admin/freelancer/client). Shared by
 * api/auth.php and the self-service registration endpoints so token shape
 * (claims, expiry) stays in exactly one place.
 */
function mf_generate_jwt($userId, $email, $name, $role = 'admin', $expirySeconds = 86400) {
    $jwtSecret = mf_jwt_secret();
    $issuedAt = time();
    $payload = [
        'iat' => $issuedAt,
        'exp' => $issuedAt + $expirySeconds,
        'user_id' => $userId,
        'email' => $email,
        'name' => $name,
        'role' => $role,
    ];

    if (class_exists('Firebase\JWT\JWT')) {
        return \Firebase\JWT\JWT::encode($payload, $jwtSecret, 'HS256');
    }
    return base64_encode(json_encode($payload));
}



