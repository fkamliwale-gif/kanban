<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

$pdo = get_db();
$stmt = $pdo->prepare(
    'SELECT DISTINCT t.*
     FROM tasks t
     JOIN projects p ON p.id = t.project_id
     JOIN users u ON u.id = ?
     LEFT JOIN project_team_members ptm ON ptm.project_id = p.id
     LEFT JOIN team_members tm
       ON tm.id = ptm.team_member_id
      AND LOWER(tm.member_email) = LOWER(u.email)
     WHERE p.user_id = ? OR tm.id IS NOT NULL
     ORDER BY t.id ASC'
);
$stmt->execute([$userId, $userId]);
respond(true, 'Tasks loaded', $stmt->fetchAll());
