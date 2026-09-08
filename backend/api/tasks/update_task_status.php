<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', null, 405);
}

$input = json_input();
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
$status = (string) ($input['status'] ?? '');

if (!$id) {
    respond(false, 'A valid task id is required.', null, 422);
}
$status = validate_enum($status, ['To Do', 'In Progress', 'Review', 'Completed'], 'task status');

$pdo = get_db();
$access = require_task_access($pdo, (int) $id, $userId);
$projectId = (int) $access['project_id'];
$title = $access['title'];

try {
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE tasks SET status = ? WHERE id = ?')->execute([$status, (int) $id]);
    $actionType = $status === 'Completed' ? 'task_completed' : 'task_moved';
    log_activity($pdo, $userId, "Task status changed: {$title} → {$status}", $actionType, $projectId, (int) $id);
    $pdo->commit();
    respond(true, "Task moved to {$status}");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Task status update failed: ' . $e->getMessage());
    respond(false, 'Unable to update task status.', null, 500);
}
