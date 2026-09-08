<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

$pdo = get_db();

$stmt = $pdo->prepare(
    'SELECT DISTINCT p.*
     FROM projects p
     JOIN users u ON u.id = ?
     LEFT JOIN project_team_members ptm ON ptm.project_id = p.id
     LEFT JOIN team_members tm
       ON tm.id = ptm.team_member_id
      AND LOWER(tm.member_email) = LOWER(u.email)
     WHERE p.user_id = ? OR tm.id IS NOT NULL
     ORDER BY p.id ASC'
);
$stmt->execute([$userId, $userId]);
$projects = $stmt->fetchAll();

$teamStmt = $pdo->prepare('SELECT team_member_id FROM project_team_members WHERE project_id = ?');
foreach ($projects as &$p) {
    $teamStmt->execute([(int) $p['id']]);
    $p['teamIds'] = array_map('intval', array_column($teamStmt->fetchAll(), 'team_member_id'));
}
unset($p);

respond(true, 'Projects loaded', $projects);
