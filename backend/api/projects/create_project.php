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
        'INSERT INTO projects (user_id, manager_id, project_name, description, status, priority, start_date, due_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId, $managerId, $name, $input['description'] ?? '',
        $input['status'] ?? 'Not Started', $input['priority'] ?? 'Medium',
        $input['startDate'] ?: null, $dueDate
    ]);

    $projectId = (int) $pdo->lastInsertId();
    $teamIds = is_array($input['teamIds'] ?? null) ? $input['teamIds'] : [];
    $link = $pdo->prepare('INSERT INTO project_team_members (project_id, team_member_id) VALUES (?, ?)');
    $memberCheck = $pdo->prepare('SELECT id FROM team_members WHERE id = ? AND user_id = ?');

    foreach (array_unique(array_map('intval', $teamIds)) as $memberId) {
        $memberCheck->execute([$memberId, $userId]);
        if ($memberCheck->fetch()) {
            $link->execute([$projectId, $memberId]);
        }
    }

    $pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)')
        ->execute([$userId, "Project created: {$name}"]);

    $pdo->commit();
    respond(true, 'Project created successfully', ['id' => $projectId]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    respond(false, 'Unable to create project.', null, 500);
}
