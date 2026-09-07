<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_input() : $_GET;
$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    respond(false, 'A valid project id is required.', null, 422);
}

$pdo = get_db();
$name = $pdo->prepare('SELECT project_name FROM projects WHERE id = ?');
$name->execute([$id]);
$row = $name->fetch();
if (!$row) {
    respond(false, 'Project not found', null, 404);
}

// ON DELETE CASCADE in the schema removes its tasks and team links too.
$pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);

$log = $pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)');
$log->execute([$userId, "Project deleted: {$row['project_name']}"]);

respond(true, 'Project deleted');
