<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$id = (int) ($input['id'] ?? 0);
$name = trim($input['name'] ?? '');

if ($id <= 0 || $name === '') {
    respond(false, 'A valid project id and name are required.', null, 422);
}

$pdo = get_db();
$pdo->beginTransaction();

$managerId = !empty($input['managerId']) ? (int) $input['managerId'] : null;

$stmt = $pdo->prepare(
    'UPDATE projects SET manager_id = ?, project_name = ?, description = ?, status = ?, priority = ?, start_date = ?, due_date = ? WHERE id = ?'
);
$stmt->execute([
    $managerId,
    $name,
    $input['description'] ?? '',
    $input['status'] ?? 'Not Started',
    $input['priority'] ?? 'Medium',
    $input['startDate'] ?? null,
    $input['dueDate'] ?? null,
    $id,
]);

$pdo->prepare('DELETE FROM project_team_members WHERE project_id = ?')->execute([$id]);
$teamIds = is_array($input['teamIds'] ?? null) ? $input['teamIds'] : [];
$link = $pdo->prepare('INSERT INTO project_team_members (project_id, team_member_id) VALUES (?, ?)');
foreach ($teamIds as $memberId) {
    $link->execute([$id, (int) $memberId]);
}

$log = $pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)');
$log->execute([$userId, "Project updated: {$name}"]);

$pdo->commit();
respond(true, 'Project updated');
