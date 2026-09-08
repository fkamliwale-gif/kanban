<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', null, 405);
}

$input = json_input();
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    respond(false, 'A valid task id is required.', null, 422);
}

$pdo = get_db();
$access = require_task_manage_permission($pdo, (int) $id, $userId);
$projectId = (int) $access['project_id'];
$title = $access['title'];

try {
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM tasks WHERE id = ?')->execute([(int) $id]);
    log_activity($pdo, $userId, "Task deleted: {$title}", 'task_deleted', $projectId, null);
    $pdo->commit();
    respond(true, 'Task deleted');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Task deletion failed: ' . $e->getMessage());
    respond(false, 'Unable to delete task.', null, 500);
}
