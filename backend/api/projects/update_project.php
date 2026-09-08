<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', null, 405);
}

$input = json_input();
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
$name = trim((string) ($input['name'] ?? ''));
$description = trim((string) ($input['description'] ?? ''));
$status = (string) ($input['status'] ?? 'Not Started');
$priority = (string) ($input['priority'] ?? 'Medium');
$startDate = validate_optional_date($input['startDate'] ?? null, 'start date');
$dueDate = validate_optional_date($input['dueDate'] ?? null, 'due date');
$managerId = !empty($input['managerId']) ? (int) $input['managerId'] : null;
$teamIds = is_array($input['teamIds'] ?? null) ? $input['teamIds'] : [];

if (!$id || $name === '' || strlen($name) > 200) {
    respond(false, 'A valid project id and project name are required.', null, 422);
}
if (strlen($description) > 5000) {
    respond(false, 'Project description is too long.', null, 422);
}
$status = validate_enum($status, ['Planning', 'Not Started', 'In Progress', 'On Hold', 'Completed', 'Cancelled'], 'project status');
$priority = validate_enum($priority, ['Low', 'Medium', 'High', 'Critical'], 'project priority');
validate_date_range($startDate, $dueDate);

$pdo = get_db();
require_project_manage_permission($pdo, (int) $id, $userId);
validate_member_exists($pdo, $managerId);
$teamIds = validate_team_member_ids($pdo, $teamIds);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'UPDATE projects
         SET manager_id = ?, project_name = ?, description = ?, status = ?, priority = ?, start_date = ?, due_date = ?
         WHERE id = ?'
    );
    $stmt->execute([$managerId, $name, $description, $status, $priority, $startDate, $dueDate, (int) $id]);

    $pdo->prepare('DELETE FROM project_team_members WHERE project_id = ?')->execute([(int) $id]);
    if ($teamIds) {
        $link = $pdo->prepare('INSERT INTO project_team_members (project_id, team_member_id) VALUES (?, ?)');
        foreach ($teamIds as $memberId) {
            $link->execute([(int) $id, $memberId]);
        }
    }

    log_activity($pdo, $userId, "Project updated: {$name}", 'project_updated', (int) $id);
    $pdo->commit();

    respond(true, 'Project updated');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Project update failed: ' . $e->getMessage());
    respond(false, 'Unable to update project.', null, 500);
}
