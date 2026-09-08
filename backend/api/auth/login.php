<?php

require_once __DIR__ . '/../../config/bootstrap.php';

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$email = trim($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

$pdo = get_db();
$stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    respond(false, 'Invalid email or password.', null, 401);
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

unset($user['password']);
respond(true, 'Login successful', $user);
