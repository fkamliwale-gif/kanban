<?php
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', null, 405);
}

$input = json_input();
$email = trim((string) ($input['email'] ?? ''));
$password = (string) ($input['password'] ?? '');

if ($email === '' || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    respond(false, 'Please provide a valid email and password.', null, 422);
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    respond(false, 'Invalid email or password.', null, 401);
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

unset($user['password']);
$user['csrfToken'] = $_SESSION['csrf_token'];
respond(true, 'Login successful', $user);
