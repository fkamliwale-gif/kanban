<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

$pdo = get_db();
$stmt = $pdo->prepare('SELECT * FROM team_members WHERE user_id = ? ORDER BY id ASC');
$stmt->execute([$userId]);

respond(true, 'Team loaded', $stmt->fetchAll());
