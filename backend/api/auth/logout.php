<?php

require_once __DIR__ . '/../../config/bootstrap.php';


// ========================================
// CHECK ACTIVE SESSION
// GET REQUEST
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (empty($_SESSION['user_id'])) {

        respond(
            false,
            'Not logged in',
            null,
            401
        );

    }


    respond(
        true,
        'Session active',
        [

            'id' => $_SESSION['user_id'],

            'name' => $_SESSION['user_name'] ?? '',

            'role' => $_SESSION['user_role'] ?? ''

        ]
    );

}


// ========================================
// LOGOUT USER
// POST REQUEST
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Remove all session values
    $_SESSION = [];


    // Remove PHP session cookie
    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );

    }


    // Destroy PHP session
    session_destroy();


    respond(
        true,
        'Logged out successfully',
        null
    );

}


// ========================================
// INVALID METHOD
// ========================================

respond(
    false,
    'Method not allowed',
    null,
    405
);