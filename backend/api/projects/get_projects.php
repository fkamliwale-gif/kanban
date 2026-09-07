<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_login();

$pdo = get_db();
$projects = $pdo->query('SELECT * FROM projects ORDER BY id ASC')->fetchAll();

$teamStmt = $pdo->prepare('SELECT team_member_id FROM project_team_members WHERE project_id = ?');
foreach ($projects as &$p) {
    $teamStmt->execute([$p['id']]);
    $p['teamIds'] = array_map('intval', array_column($teamStmt->fetchAll(), 'team_member_id'));
}

respond(true, 'Projects loaded', $projects);
