<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    respond(false, 'A valid project id is required.', null, 422);
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT project_name FROM projects WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);
$row = $stmt->fetch();

if (!$row) {
    respond(false, 'Project not found.', null, 404);
}

$pdo->prepare('DELETE FROM projects WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
$pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)')
    ->execute([$userId, "Project deleted: {$row['project_name']}"]);

respond(true, 'Project deleted');
