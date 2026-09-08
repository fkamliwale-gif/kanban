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
$owner = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ?');
$owner->execute([$id, $userId]);
if (!$owner->fetch()) {
    respond(false, 'Project not found.', null, 404);
}

$pdo->beginTransaction();

try {
    $managerId = !empty($input['managerId']) ? (int) $input['managerId'] : null;

    if ($managerId !== null) {
        $check = $pdo->prepare('SELECT id FROM team_members WHERE id = ? AND user_id = ?');
        $check->execute([$managerId, $userId]);
        if (!$check->fetch()) {
            $pdo->rollBack();
            respond(false, 'Selected manager is invalid.', null, 422);
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE projects
         SET manager_id = ?, project_name = ?, description = ?, status = ?, priority = ?, start_date = ?, due_date = ?
         WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([
        $managerId, $name, $input['description'] ?? '',
        $input['status'] ?? 'Not Started', $input['priority'] ?? 'Medium',
        $input['startDate'] ?: null, $input['dueDate'] ?: null,
        $id, $userId
    ]);

    $pdo->prepare('DELETE FROM project_team_members WHERE project_id = ?')->execute([$id]);

    $teamIds = is_array($input['teamIds'] ?? null) ? $input['teamIds'] : [];
    $link = $pdo->prepare('INSERT INTO project_team_members (project_id, team_member_id) VALUES (?, ?)');
    $memberCheck = $pdo->prepare('SELECT id FROM team_members WHERE id = ? AND user_id = ?');

    foreach (array_unique(array_map('intval', $teamIds)) as $memberId) {
        $memberCheck->execute([$memberId, $userId]);
        if ($memberCheck->fetch()) {
            $link->execute([$id, $memberId]);
        }
    }

    $pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)')
        ->execute([$userId, "Project updated: {$name}"]);

    $pdo->commit();
    respond(true, 'Project updated');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    respond(false, 'Unable to update project.', null, 500);
}
