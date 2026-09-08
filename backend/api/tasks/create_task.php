<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', null, 405);
}

$input = json_input();
$title = trim((string) ($input['title'] ?? ''));
$description = trim((string) ($input['description'] ?? ''));
$projectId = filter_var($input['projectId'] ?? null, FILTER_VALIDATE_INT);
$assignedTo = !empty($input['assignedTo']) ? (int) $input['assignedTo'] : null;
$status = (string) ($input['status'] ?? 'To Do');
$priority = (string) ($input['priority'] ?? 'Medium');
$startDate = validate_optional_date($input['startDate'] ?? null, 'start date');
$dueDate = validate_optional_date($input['dueDate'] ?? null, 'due date');

if (!$projectId || $title === '' || strlen($title) > 200) {
    respond(false, 'A valid project id and task title are required.', null, 422);
}
if (strlen($description) > 10000) {
    respond(false, 'Task description is too long.', null, 422);
}
$status = validate_enum($status, ['To Do', 'In Progress', 'Review', 'Completed'], 'task status');
$priority = validate_enum($priority, ['Low', 'Medium', 'High', 'Critical'], 'task priority');
validate_date_range($startDate, $dueDate);

$pdo = get_db();
require_project_manage_permission($pdo, (int) $projectId, $userId);
validate_member_for_project($pdo, (int) $projectId, $assignedTo);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO tasks (project_id, user_id, assigned_to, title, description, status, priority, start_date, due_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        (int) $projectId,
        $userId,
        $assignedTo,
        $title,
        $description,
        $status,
        $priority,
        $startDate,
        $dueDate
    ]);

    $taskId = (int) $pdo->lastInsertId();
    log_activity($pdo, $userId, "Task created: {$title}", 'task_created', (int) $projectId, $taskId);
    $pdo->commit();

    respond(true, 'Task created successfully', ['id' => $taskId], 201);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Task creation failed: ' . $e->getMessage());
    respond(false, 'Unable to create task.', null, 500);
}
