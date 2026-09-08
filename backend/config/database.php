<?php
// Reusable PDO connection for the Kanban Tracker backend.
// Local XAMPP defaults remain available for development; production can
// provide these values through the PHP process environment.

$DB_HOST = getenv('KANBAN_DB_HOST') ?: 'localhost';
$DB_NAME = getenv('KANBAN_DB_NAME') ?: 'kanban_tracker';
$DB_USER = getenv('KANBAN_DB_USER') ?: 'root';
$DB_PASS = getenv('KANBAN_DB_PASS') ?: '';

function get_db(): PDO
{
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
            $DB_USER,
            $DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log('Kanban database connection failed: ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => 'Database temporarily unavailable.',
            'data' => null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
