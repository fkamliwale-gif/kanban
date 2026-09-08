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
    respond(false, 'A valid member id is required.', null, 422);
}

$pdo = get_db();
require_team_manage_permission($pdo, $userId, (int) $id);

$row = $pdo->prepare('SELECT member_name FROM team_members WHERE id = ?');
$row->execute([(int) $id]);
$member = $row->fetch();
if (!$member) {
    respond(false, 'Team member not found.', null, 404);
}

try {
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM team_members WHERE id = ?')->execute([(int) $id]);
    log_activity($pdo, $userId, "Team member removed: {$member['member_name']}", 'member_removed');
    $pdo->commit();
    respond(true, 'Team member removed');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Team member removal failed: ' . $e->getMessage());
    respond(false, 'Unable to remove team member.', null, 500);
}
