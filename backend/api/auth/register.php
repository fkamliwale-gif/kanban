<?php
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');
$role = trim($input['role'] ?? 'Member');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    respond(false, 'Please provide a valid name, email, and a password of at least 6 characters.', null, 422);
}

$pdo = get_db();

$check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    respond(false, 'An account with this email already exists.', null, 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
$insert->execute([$name, $email, $hash, $role]);

respond(true, 'Account created. Please log in.', ['id' => (int) $pdo->lastInsertId()]);
