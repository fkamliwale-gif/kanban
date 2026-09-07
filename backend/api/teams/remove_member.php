<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? json_input() : $_GET;
$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    respond(false, 'A valid member id is required.', null, 422);
}

$pdo = get_db();
$row = $pdo->prepare('SELECT member_name FROM team_members WHERE id = ?');
$row->execute([$id]);
$member = $row->fetch();
if (!$member) {
    respond(false, 'Team member not found', null, 404);
}

$pdo->prepare('DELETE FROM team_members WHERE id = ?')->execute([$id]);

$pdo->prepare('INSERT INTO activities (user_id, action) VALUES (?, ?)')
    ->execute([$userId, "Team member removed: {$member['member_name']}"]);

respond(true, 'Team member removed');
