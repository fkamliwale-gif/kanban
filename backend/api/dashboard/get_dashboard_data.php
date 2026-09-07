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
unset($p);

$tasks = $pdo->query('SELECT * FROM tasks ORDER BY id ASC')->fetchAll();
$members = $pdo->query('SELECT * FROM team_members ORDER BY id ASC')->fetchAll();
$activities = $pdo->query(
    'SELECT activities.*, users.name AS user_name
     FROM activities LEFT JOIN users ON users.id = activities.user_id
     ORDER BY activities.id DESC LIMIT 100'
)->fetchAll();

respond(true, 'Dashboard data loaded', [
    'projects' => $projects,
    'tasks' => $tasks,
    'members' => $members,
    'activities' => $activities,
]);
