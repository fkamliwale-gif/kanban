<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', null, 405);
}

$input = json_input();
$name = trim((string) ($input['name'] ?? ''));
$description = trim((string) ($input['description'] ?? ''));
$status = (string) ($input['status'] ?? 'Not Started');
$priority = (string) ($input['priority'] ?? 'Medium');
$startDate = validate_optional_date($input['startDate'] ?? null, 'start date');
$dueDate = validate_optional_date($input['dueDate'] ?? null, 'due date');
$managerId = !empty($input['managerId']) ? (int) $input['managerId'] : null;
$teamIds = is_array($input['teamIds'] ?? null) ? $input['teamIds'] : [];

if ($name === '' || strlen($name) > 200) {
    respond(false, 'Project name is required and must be at most 200 characters.', null, 422);
}
if (strlen($description) > 5000) {
    respond(false, 'Project description is too long.', null, 422);
}
$status = validate_enum($status, ['Planning', 'Not Started', 'In Progress', 'On Hold', 'Completed', 'Cancelled'], 'project status');
$priority = validate_enum($priority, ['Low', 'Medium', 'High', 'Critical'], 'project priority');
validate_date_range($startDate, $dueDate);

$pdo = get_db();
validate_member_exists($pdo, $managerId);
$teamIds = validate_team_member_ids($pdo, $teamIds);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO projects (user_id, manager_id, project_name, description, status, priority, start_date, due_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $managerId, $name, $description, $status, $priority, $startDate, $dueDate]);
    $projectId = (int) $pdo->lastInsertId();

    if ($teamIds) {
        $link = $pdo->prepare('INSERT INTO project_team_members (project_id, team_member_id) VALUES (?, ?)');
        foreach ($teamIds as $memberId) {
            $link->execute([$projectId, $memberId]);
        }
    }

    log_activity($pdo, $userId, "Project created: {$name}", 'project_created', $projectId);
    $pdo->commit();

    respond(true, 'Project created successfully', ['id' => $projectId], 201);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Project creation failed: ' . $e->getMessage());
    respond(false, 'Unable to create project.', null, 500);
}
