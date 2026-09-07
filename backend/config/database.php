<?php
// backend/config/database.php
// Reusable PDO connection for the Kanban Tracker backend.
// Default XAMPP MySQL settings — change if your setup differs.

$DB_HOST = 'localhost';
$DB_NAME = 'kanban_tracker';
$DB_USER = 'root';
$DB_PASS = '';

function get_db(): PDO {
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
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage(),
        ]);
        exit;
    }
}
