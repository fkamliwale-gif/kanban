<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();
$pdo = get_db();

$projectsStmt = $pdo->prepare('SELECT * FROM projects WHERE user_id = ? ORDER BY id ASC');
$projectsStmt->execute([$userId]);
$projects = $projectsStmt->fetchAll();

$teamStmt = $pdo->prepare('SELECT team_member_id FROM project_team_members WHERE project_id = ?');
foreach ($projects as &$project) {
    $teamStmt->execute([$project['id']]);
    $project['teamIds'] = array_map('intval', array_column($teamStmt->fetchAll(), 'team_member_id'));
}
unset($project);

$tasksStmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY id ASC');
$tasksStmt->execute([$userId]);
$tasks = $tasksStmt->fetchAll();

$membersStmt = $pdo->prepare('SELECT * FROM team_members WHERE user_id = ? ORDER BY id ASC');
$membersStmt->execute([$userId]);
$members = $membersStmt->fetchAll();

$activitiesStmt = $pdo->prepare(
    'SELECT activities.*, users.name AS user_name
     FROM activities
     LEFT JOIN users ON users.id = activities.user_id
     WHERE activities.user_id = ?
     ORDER BY activities.id DESC
     LIMIT 100'
);
$activitiesStmt->execute([$userId]);
$activities = $activitiesStmt->fetchAll();

respond(true, 'Dashboard data loaded', [
    'projects' => $projects,
    'tasks' => $tasks,
    'members' => $members,
    'activities' => $activities,
]);
