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
    respond(false, 'A valid project id is required.', null, 422);
}

$pdo = get_db();
require_project_manage_permission($pdo, (int) $id, $userId);

$nameStmt = $pdo->prepare('SELECT project_name FROM projects WHERE id = ?');
$nameStmt->execute([(int) $id]);
$row = $nameStmt->fetch();
if (!$row) {
    respond(false, 'Project not found', null, 404);
}

try {
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([(int) $id]);
    // The project no longer exists, so leave project_id NULL for this audit event.
    log_activity($pdo, $userId, "Project deleted: {$row['project_name']}", 'project_deleted', null, null);
    $pdo->commit();
    respond(true, 'Project deleted');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Project deletion failed: ' . $e->getMessage());
    respond(false, 'Unable to delete project.', null, 500);
}
