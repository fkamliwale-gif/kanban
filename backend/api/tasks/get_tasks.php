<?php
require_once __DIR__ . '/../../config/bootstrap.php';
$userId = require_login();

$pdo = get_db();
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY id ASC');
$stmt->execute([$userId]);

respond(true, 'Tasks loaded', $stmt->fetchAll());
