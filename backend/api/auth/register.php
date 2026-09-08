<?php
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', null, 405);
}

$input = json_input();
$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$password = (string) ($input['password'] ?? '');

if ($name === '' || strlen($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 || strlen($password) < 8 || strlen($password) > 200) {
    respond(false, 'Please provide a valid name, email, and a password between 8 and 200 characters.', null, 422);
}

$pdo = get_db();

$check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    respond(false, 'An account with this email already exists.', null, 409);
}

// Public registration must never grant a privileged role.
$role = 'Member';
$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
$insert->execute([$name, $email, $hash, $role]);

respond(true, 'Account created. Please log in.', ['id' => (int) $pdo->lastInsertId()]);
