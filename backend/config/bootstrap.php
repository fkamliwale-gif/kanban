<?php

// ========================================
// Kanban Tracker - Bootstrap Configuration
// Handles:
// - CORS
// - Sessions
// - Database
// - JSON Responses
// ========================================


// Prevent output before headers
ob_start();


// ========================================
// ERROR SETTINGS
// ========================================

// Do not display PHP errors as HTML
ini_set('display_errors', '0');

error_reporting(E_ALL);


// ========================================
// CORS SETTINGS
// ========================================

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173'
];

if (in_array($origin, $allowedOrigins, true)) {

    header("Access-Control-Allow-Origin: $origin");

}

header('Access-Control-Allow-Credentials: true');

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

header('Access-Control-Allow-Headers: Content-Type, Authorization');

header('Content-Type: application/json; charset=UTF-8');


// ========================================
// HANDLE PREFLIGHT OPTIONS REQUEST
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

    http_response_code(204);

    exit;

}


// ========================================
// PHP SESSION SETTINGS
// ========================================

session_set_cookie_params([

    'lifetime' => 0,

    'path' => '/',

    'secure' => false,

    'httponly' => true,

    'samesite' => 'Lax'

]);

session_start();


// ========================================
// DATABASE CONNECTION
// ========================================

require_once __DIR__ . '/database.php';


// ========================================
// GET JSON INPUT
// ========================================

function json_input(): array
{

    $raw = file_get_contents('php://input');

    $data = json_decode($raw ?: '{}', true);

    return is_array($data) ? $data : [];

}


// ========================================
// JSON RESPONSE FUNCTION
// ========================================

function respond(
    bool $ok,
    string $message,
    $data = null,
    int $status = 200
): void
{

    http_response_code($status);

    echo json_encode([

        'success' => $ok,

        'message' => $message,

        'data' => $data

    ], JSON_UNESCAPED_UNICODE);

    exit;

}


// ========================================
// CHECK LOGIN
// ========================================

function require_login(): int
{

    if (empty($_SESSION['user_id'])) {

        respond(
            false,
            'Not logged in',
            null,
            401
        );

    }

    return (int) $_SESSION['user_id'];

}