<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Use POST', null, 405);
}

$input = json_input();
$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    respond(false, 'A valid member id is required.', null, 422);
}

$pdo = get_db();
$row = $pdo->prepare('SELECT member_name FROM team_members WHERE id = ? AND user_id = ?');
$row->execute([$id, $userId]);
$member = $row->fetch();

if (!$member) {
    respond(false, 'Team member not found.', null, 404);
}

$pdo->prepare('DELETE FROM team_members WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
$pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)')
    ->execute([$userId, "Team member removed: {$member['member_name']}"]);

respond(true, 'Team member removed');
