<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

$pdo = get_db();
$stmt = $pdo->prepare('SELECT * FROM projects WHERE user_id = ? ORDER BY id ASC');
$stmt->execute([$userId]);
$projects = $stmt->fetchAll();

$teamStmt = $pdo->prepare('SELECT team_member_id FROM project_team_members WHERE project_id = ?');
foreach ($projects as &$project) {
    $teamStmt->execute([$project['id']]);
    $project['teamIds'] = array_map('intval', array_column($teamStmt->fetchAll(), 'team_member_id'));
}
unset($project);

respond(true, 'Projects loaded', $projects);
