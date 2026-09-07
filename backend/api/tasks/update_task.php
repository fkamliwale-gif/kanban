<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$id = (int) ($input['id'] ?? 0);
$title = trim($input['title'] ?? '');

if ($id <= 0 || $title === '') {
    respond(false, 'A valid task id and title are required.', null, 422);
}

$pdo = get_db();
$assignedTo = !empty($input['assignedTo']) ? (int) $input['assignedTo'] : null;

$stmt = $pdo->prepare(
    'UPDATE tasks SET project_id = ?, assigned_to = ?, title = ?, description = ?, status = ?, priority = ?, start_date = ?, due_date = ? WHERE id = ?'
);
$stmt->execute([
    (int) ($input['projectId'] ?? 0),
    $assignedTo,
    $title,
    $input['description'] ?? '',
    $input['status'] ?? 'To Do',
    $input['priority'] ?? 'Medium',
    $input['startDate'] ?? null,
    $input['dueDate'] ?? null,
    $id,
]);

$log = $pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)');
$log->execute([$userId, "Task updated: {$title}"]);

respond(true, 'Task updated');
