<?php
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Method not allowed', null, 405);
}

if (empty($_SESSION['user_id'])) {
    respond(true, 'No active session', null);
}

respond(true, 'Session active', [
    'id' => (int) $_SESSION['user_id'],
    'name' => $_SESSION['user_name'] ?? '',
    'email' => $_SESSION['user_email'] ?? '',
    'role' => $_SESSION['user_role'] ?? 'Member',
]);
