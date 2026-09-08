<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();
require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed', null, 405);
}

$input = json_input();
$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$role = (string) ($input['role'] ?? 'Member');
$id = !empty($input['id']) ? (int) $input['id'] : 0;

if ($name === '' || strlen($name) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
    respond(false, 'A valid name and email are required.', null, 422);
}

$role = validate_enum($role, ['Admin', 'Project Manager', 'Developer', 'Designer', 'Tester', 'Member'], 'team member role');
$pdo = get_db();
require_team_manage_permission($pdo, $userId, $id > 0 ? $id : null);

try {
    if ($id > 0) {
        $exists = $pdo->prepare('SELECT id FROM team_members WHERE id = ?');
        $exists->execute([$id]);
        if (!$exists->fetch()) {
            respond(false, 'Team member not found.', null, 404);
        }

        $stmt = $pdo->prepare('UPDATE team_members SET member_name = ?, member_email = ?, role = ? WHERE id = ?');
        $stmt->execute([$name, $email, $role, $id]);
        $action = "Team member updated: {$name}";
        $actionType = 'member_updated';
    } else {
        $stmt = $pdo->prepare('INSERT INTO team_members (created_by, member_name, member_email, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $name, $email, $role]);
        $id = (int) $pdo->lastInsertId();
        $action = "Team member added: {$name}";
        $actionType = 'member_added';
    }

    log_activity($pdo, $userId, $action, $actionType);
    respond(true, $action, ['id' => $id]);
} catch (Throwable $e) {
    error_log('Team member save failed: ' . $e->getMessage());
    respond(false, 'Unable to save team member.', null, 500);
}
