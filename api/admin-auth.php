<?php
/**
 * Admin Authentication API
 * Handles login, logout, and session management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    // Initialize database connection
    try {
        $db = new DatabaseClient();
    } catch (Exception $dbError) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed: ' . $dbError->getMessage()
        ]);
        exit;
    }
    
    // Default action is 'check' if not provided
    if (empty($action)) {
        $action = 'check';
    }
    
    switch ($action) {
        case 'login':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }
            
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                throw new Exception('Email and password are required');
            }
            
            // Find user
            $users = $db->select('admin_users', [
                'email' => $email,
                'is_active' => true
            ]);
            
            if (empty($users)) {
                error_log("Login failed: User not found - $email");
                throw new Exception('Invalid email or password');
            }
            
            $user = $users[0];
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                error_log("Login failed: Password mismatch for $email");
                throw new Exception('Invalid email or password');
            }
            
            // Update last login
            $db->update('admin_users', [
                'last_login_at' => date('Y-m-d H:i:s')
            ], ['id' => $user['id']]);
            
            // Set session
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_user_email'] = $user['email'];
            $_SESSION['admin_user_name'] = $user['name'];
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name']
                ]
            ]);
            break;
            
        case 'logout':
            session_destroy();
            echo json_encode(['success' => true]);
            break;
            
        case 'check':
            if (isset($_SESSION['admin_user_id'])) {
                $users = $db->select('admin_users', [
                    'id' => $_SESSION['admin_user_id'],
                    'is_active' => true
                ]);
                
                if (!empty($users)) {
                    $user = $users[0];
                    echo json_encode([
                        'authenticated' => true,
                        'user' => [
                            'id' => $user['id'],
                            'email' => $user['email'],
                            'name' => $user['name']
                        ]
                    ]);
                } else {
                    session_destroy();
                    echo json_encode(['authenticated' => false]);
                }
            } else {
                echo json_encode(['authenticated' => false]);
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

