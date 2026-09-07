<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_login();

$pdo = get_db();
$tasks = $pdo->query('SELECT * FROM tasks ORDER BY id ASC')->fetchAll();
respond(true, 'Tasks loaded', $tasks);
