<?php
// backend/config/bootstrap.php
// Included by every API endpoint. Handles CORS, sessions, and JSON helpers.

// Buffer all output. This guarantees header() below always succeeds even
// if something (a stray notice, a BOM, whitespace) writes text early —
// that text sits in the buffer instead of being sent before the headers.
ob_start();

// Don't let PHP dump HTML error pages — those have no CORS header and
// break the frontend with a confusing "CORS" error instead of the real one.
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    ob_end_clean();
    exit;
}

// If PHP hits a fatal error later (e.g. bad SQL, missing DB), still return
// valid JSON with the CORS headers already sent above, instead of a blank
// or HTML response the browser will report as a CORS failure.
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length() !== false) {
            ob_end_clean();
        }
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $error['message'],
        ]);
    } else {
        ob_end_flush();
    }
});

// Session cookie must allow cross-port requests from the Vite dev server.
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,   // set true if you serve over HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/database.php';

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

function respond(bool $ok, string $message, $data = null, int $status = 200): void {
    http_response_code($status);
    echo json_encode([
        'success' => $ok,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Call this at the top of any endpoint that requires a logged-in user.
function require_login(): int {
    if (empty($_SESSION['user_id'])) {
        respond(false, 'Not logged in', null, 401);
    }
    return (int) $_SESSION['user_id'];
}
