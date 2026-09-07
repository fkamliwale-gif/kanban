<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$title = trim($input['title'] ?? '');
$projectId = (int) ($input['projectId'] ?? 0);
$dueDate = $input['dueDate'] ?? '';

if ($title === '' || $projectId <= 0 || $dueDate === '') {
    respond(false, 'Task title, project, and due date are required.', null, 422);
}

$pdo = get_db();
$stmt = $pdo->prepare(
    'INSERT INTO tasks (project_id, user_id, assigned_to, title, description, status, priority, start_date, due_date)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$assignedTo = !empty($input['assignedTo']) ? (int) $input['assignedTo'] : null;
$stmt->execute([
    $projectId,
    $userId,
    $assignedTo,
    $title,
    $input['description'] ?? '',
    $input['status'] ?? 'To Do',
    $input['priority'] ?? 'Medium',
    $input['startDate'] ?? null,
    $dueDate,
]);
$taskId = (int) $pdo->lastInsertId();

$log = $pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)');
$log->execute([$userId, "Task created: {$title}"]);

respond(true, 'Task created successfully', ['id' => $taskId]);
