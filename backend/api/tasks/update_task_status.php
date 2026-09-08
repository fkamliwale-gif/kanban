<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$id = (int) ($input['id'] ?? 0);
$status = $input['status'] ?? '';
$allowed = ['To Do', 'In Progress', 'Review', 'Completed'];

if ($id <= 0 || !in_array($status, $allowed, true)) {
    respond(false, 'A valid task id and status are required.', null, 422);
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT title FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);
$row = $stmt->fetch();

if (!$row) {
    respond(false, 'Task not found.', null, 404);
}

$pdo->prepare('UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?')
    ->execute([$status, $id, $userId]);

$pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)')
    ->execute([$userId, "Task status changed: {$row['title']} → {$status}"]);

respond(true, "Task moved to {$status}");
