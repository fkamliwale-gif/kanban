<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$name = trim($input['name'] ?? '');
$dueDate = $input['dueDate'] ?? '';

if ($name === '' || $dueDate === '') {
    respond(false, 'Project name and due date are required.', null, 422);
}

$pdo = get_db();
$pdo->beginTransaction();

$managerId = !empty($input['managerId']) ? (int) $input['managerId'] : null;

$stmt = $pdo->prepare(
    'INSERT INTO projects (user_id, manager_id, project_name, description, status, priority, start_date, due_date)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $userId,
    $managerId,
    $name,
    $input['description'] ?? '',
    $input['status'] ?? 'Not Started',
    $input['priority'] ?? 'Medium',
    $input['startDate'] ?? null,
    $dueDate,
]);
$projectId = (int) $pdo->lastInsertId();

$teamIds = is_array($input['teamIds'] ?? null) ? $input['teamIds'] : [];
$link = $pdo->prepare('INSERT INTO project_team_members (project_id, team_member_id) VALUES (?, ?)');
foreach ($teamIds as $memberId) {
    $link->execute([$projectId, (int) $memberId]);
}

$log = $pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)');
$log->execute([$userId, "Project created: {$name}"]);

$pdo->commit();
respond(true, 'Project created successfully', ['id' => $projectId]);
