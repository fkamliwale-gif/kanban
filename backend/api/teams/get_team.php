<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_login();

$pdo = get_db();
$members = $pdo->query('SELECT * FROM team_members ORDER BY id ASC')->fetchAll();
respond(true, 'Team loaded', $members);
