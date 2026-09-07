<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_input() : $_GET;
$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    respond(false, 'A valid task id is required.', null, 422);
}

$pdo = get_db();
$titleRow = $pdo->prepare('SELECT title FROM tasks WHERE id = ?');
$titleRow->execute([$id]);
$row = $titleRow->fetch();
if (!$row) {
    respond(false, 'Task not found', null, 404);
}

$pdo->prepare('DELETE FROM tasks WHERE id = ?')->execute([$id]);

$log = $pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)');
$log->execute([$userId, "Task deleted: {$row['title']}"]);

respond(true, 'Task deleted');
