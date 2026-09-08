<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$role = trim($input['role'] ?? 'Member');
$id = (int) ($input['id'] ?? 0);

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'A valid name and email are required.', null, 422);
}

$pdo = get_db();

if ($id > 0) {
    $stmt = $pdo->prepare(
        'UPDATE team_members SET member_name = ?, member_email = ?, role = ?
         WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([$name, $email, $role, $id, $userId]);
    if ($stmt->rowCount() === 0) {
        respond(false, 'Team member not found.', null, 404);
    }
    $message = "Team member updated: {$name}";
} else {
    $stmt = $pdo->prepare(
        'INSERT INTO team_members (user_id, member_name, member_email, role)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $name, $email, $role]);
    $id = (int) $pdo->lastInsertId();
    $message = "Team member added: {$name}";
}

$pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)')
    ->execute([$userId, $message]);

respond(true, $message, ['id' => $id]);
