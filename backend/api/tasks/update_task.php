<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$id = (int) ($input['id'] ?? 0);
$title = trim($input['title'] ?? '');
$projectId = (int) ($input['projectId'] ?? 0);

if ($id <= 0 || $title === '' || $projectId <= 0) {
    respond(false, 'A valid task id, title, and project are required.', null, 422);
}

$pdo = get_db();

$task = $pdo->prepare('SELECT id FROM tasks WHERE id = ? AND user_id = ?');
$task->execute([$id, $userId]);
if (!$task->fetch()) {
    respond(false, 'Task not found.', null, 404);
}

$project = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ?');
$project->execute([$projectId, $userId]);
if (!$project->fetch()) {
    respond(false, 'Selected project was not found.', null, 404);
}

$assignedTo = !empty($input['assignedTo']) ? (int) $input['assignedTo'] : null;
if ($assignedTo !== null) {
    $member = $pdo->prepare('SELECT id FROM team_members WHERE id = ? AND user_id = ?');
    $member->execute([$assignedTo, $userId]);
    if (!$member->fetch()) {
        respond(false, 'Selected team member is invalid.', null, 422);
    }
}

$stmt = $pdo->prepare(
    'UPDATE tasks
     SET project_id = ?, assigned_to = ?, title = ?, description = ?, status = ?, priority = ?, start_date = ?, due_date = ?
     WHERE id = ? AND user_id = ?'
);
$stmt->execute([
    $projectId, $assignedTo, $title, $input['description'] ?? '',
    $input['status'] ?? 'To Do', $input['priority'] ?? 'Medium',
    $input['startDate'] ?: null, $input['dueDate'] ?: null,
    $id, $userId
]);

$pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)')
    ->execute([$userId, "Task updated: {$title}"]);

respond(true, 'Task updated');
