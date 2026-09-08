<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', null, 405);
}

$input = json_input();
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
$title = trim((string) ($input['title'] ?? ''));
$description = trim((string) ($input['description'] ?? ''));
$projectId = filter_var($input['projectId'] ?? null, FILTER_VALIDATE_INT);
$assignedTo = !empty($input['assignedTo']) ? (int) $input['assignedTo'] : null;
$status = (string) ($input['status'] ?? 'To Do');
$priority = (string) ($input['priority'] ?? 'Medium');
$startDate = validate_optional_date($input['startDate'] ?? null, 'start date');
$dueDate = validate_optional_date($input['dueDate'] ?? null, 'due date');

if (!$id || !$projectId || $title === '' || strlen($title) > 200) {
    respond(false, 'Valid task id, project id, and title are required.', null, 422);
}
if (strlen($description) > 10000) {
    respond(false, 'Task description is too long.', null, 422);
}
$status = validate_enum($status, ['To Do', 'In Progress', 'Review', 'Completed'], 'task status');
$priority = validate_enum($priority, ['Low', 'Medium', 'High', 'Critical'], 'task priority');
validate_date_range($startDate, $dueDate);

$pdo = get_db();
require_task_manage_permission($pdo, (int) $id, $userId);
require_project_manage_permission($pdo, (int) $projectId, $userId);
validate_member_for_project($pdo, (int) $projectId, $assignedTo);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'UPDATE tasks
         SET project_id = ?, assigned_to = ?, title = ?, description = ?, status = ?, priority = ?, start_date = ?, due_date = ?
         WHERE id = ?'
    );
    $stmt->execute([
        (int) $projectId,
        $assignedTo,
        $title,
        $description,
        $status,
        $priority,
        $startDate,
        $dueDate,
        (int) $id
    ]);

    log_activity($pdo, $userId, "Task updated: {$title}", 'task_updated', (int) $projectId, (int) $id);
    $pdo->commit();

    respond(true, 'Task updated');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Task update failed: ' . $e->getMessage());
    respond(false, 'Unable to update task.', null, 500);
}
