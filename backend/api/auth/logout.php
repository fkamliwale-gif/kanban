<?php
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Used on app load to check "am I still logged in?"
    if (empty($_SESSION['user_id'])) {
        respond(false, 'Not logged in', null, 401);
    }
    respond(true, 'Session active', [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role'],
    ]);
}

$_SESSION = [];
session_destroy();
respond(true, 'Logged out');
