<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

$pdo = get_db();

$projectStmt = $pdo->prepare(
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
$projectStmt->execute([$userId, $userId]);
$projects = $projectStmt->fetchAll();

$projectIds = array_map('intval', array_column($projects, 'id'));

$teamStmt = $pdo->prepare('SELECT team_member_id FROM project_team_members WHERE project_id = ?');
foreach ($projects as &$project) {
    $teamStmt->execute([(int) $project['id']]);
    $project['teamIds'] = array_map('intval', array_column($teamStmt->fetchAll(), 'team_member_id'));
}
unset($project);

if ($projectIds) {
    $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
    $taskStmt = $pdo->prepare("SELECT DISTINCT t.* FROM tasks t WHERE t.project_id IN ({$placeholders}) ORDER BY t.id ASC");
    $taskStmt->execute($projectIds);
    $tasks = $taskStmt->fetchAll();

    $activityStmt = $pdo->prepare(
        "SELECT a.*, u.name AS user_name
         FROM activities a
         LEFT JOIN users u ON u.id = a.user_id
         WHERE a.user_id = ? OR a.project_id IN ({$placeholders})
         ORDER BY a.id DESC LIMIT 100"
    );
    $activityStmt->execute(array_merge([$userId], $projectIds));
    $activities = $activityStmt->fetchAll();
} else {
    $tasks = [];
    $activityStmt = $pdo->prepare(
        'SELECT a.*, u.name AS user_name
         FROM activities a
         LEFT JOIN users u ON u.id = a.user_id
         WHERE a.user_id = ?
         ORDER BY a.id DESC LIMIT 100'
    );
    $activityStmt->execute([$userId]);
    $activities = $activityStmt->fetchAll();
}

// Team members remain a shared directory in the existing application model.
$members = $pdo->query('SELECT * FROM team_members ORDER BY id ASC')->fetchAll();

respond(true, 'Dashboard data loaded', [
    'projects' => $projects,
    'tasks' => $tasks,
    'members' => $members,
    'activities' => $activities,
]);
