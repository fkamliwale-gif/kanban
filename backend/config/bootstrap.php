<?php

// ========================================
// Kanban Tracker - Bootstrap Configuration
// Handles CORS, sessions, JSON responses,
// validation, CSRF and authorization helpers.
// ========================================

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = array_values(array_filter([
    env_value('FRONTEND_URL'),
    'http://localhost:5173',
    'http://127.0.0.1:5173'
]));

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Vary: Origin');
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require_once __DIR__ . '/database.php';

function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

function respond(bool $ok, string $message, $data = null, int $status = 200): void
{
    http_response_code($status);
    echo json_encode([
        'success' => $ok,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        respond(false, 'Not logged in', null, 401);
    }
    return (int) $_SESSION['user_id'];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function require_csrf(): void
{
    if (empty($_SESSION['user_id'])) {
        return;
    }

    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';

    if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
        respond(false, 'Invalid CSRF token.', null, 403);
    }
}

function validate_enum(string $value, array $allowed, string $field): string
{
    if (!in_array($value, $allowed, true)) {
        respond(false, "Invalid {$field}.", null, 422);
    }
    return $value;
}

function validate_optional_date($value, string $field): ?string
{
    if ($value === null || $value === '') {
        return null;
    }

    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        respond(false, "Invalid {$field}.", null, 422);
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        respond(false, "Invalid {$field}.", null, 422);
    }

    return $value;
}

function validate_date_range(?string $startDate, ?string $dueDate): void
{
    if ($startDate !== null && $dueDate !== null && $startDate > $dueDate) {
        respond(false, 'Start date must not be after due date.', null, 422);
    }
}

function get_project_access(PDO $pdo, int $projectId, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT p.id, p.user_id AS owner_id,
                u.role AS user_role,
                tm.id AS matched_team_member_id,
                tm.role AS member_role
         FROM projects p
         JOIN users u ON u.id = ?
         LEFT JOIN project_team_members ptm ON ptm.project_id = p.id
         LEFT JOIN team_members tm
           ON tm.id = ptm.team_member_id
          AND LOWER(tm.member_email) = LOWER(u.email)
         WHERE p.id = ?
           AND (p.user_id = ? OR tm.id IS NOT NULL)
         LIMIT 1'
    );
    $stmt->execute([$userId, $projectId, $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function require_project_access(PDO $pdo, int $projectId, int $userId): array
{
    $access = get_project_access($pdo, $projectId, $userId);
    if (!$access) {
        respond(false, 'Project not found or access denied.', null, 404);
    }
    return $access;
}

function require_project_manage_permission(PDO $pdo, int $projectId, int $userId): array
{
    $access = require_project_access($pdo, $projectId, $userId);
    $isOwner = (int) $access['owner_id'] === $userId;
    $globalRoleAllowed = in_array($access['user_role'], ['Admin', 'Project Manager'], true);
    $memberRoleAllowed = in_array((string) ($access['member_role'] ?? ''), ['Admin', 'Project Manager'], true);

    if (!$isOwner && !$globalRoleAllowed && !$memberRoleAllowed) {
        respond(false, 'You do not have permission to manage this project.', null, 403);
    }

    return $access;
}

function get_task_access(PDO $pdo, int $taskId, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT t.id, t.project_id, t.title,
                p.user_id AS owner_id,
                u.role AS user_role,
                tm.id AS matched_team_member_id,
                tm.role AS member_role
         FROM tasks t
         JOIN projects p ON p.id = t.project_id
         JOIN users u ON u.id = ?
         LEFT JOIN project_team_members ptm ON ptm.project_id = p.id
         LEFT JOIN team_members tm
           ON tm.id = ptm.team_member_id
          AND LOWER(tm.member_email) = LOWER(u.email)
         WHERE t.id = ?
           AND (p.user_id = ? OR tm.id IS NOT NULL)
         LIMIT 1'
    );
    $stmt->execute([$userId, $taskId, $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function require_task_access(PDO $pdo, int $taskId, int $userId): array
{
    $access = get_task_access($pdo, $taskId, $userId);
    if (!$access) {
        respond(false, 'Task not found or access denied.', null, 404);
    }
    return $access;
}

function require_task_manage_permission(PDO $pdo, int $taskId, int $userId): array
{
    $access = require_task_access($pdo, $taskId, $userId);
    $isOwner = (int) $access['owner_id'] === $userId;
    $globalRoleAllowed = in_array($access['user_role'], ['Admin', 'Project Manager'], true);
    $memberRoleAllowed = in_array((string) ($access['member_role'] ?? ''), ['Admin', 'Project Manager'], true);

    if (!$isOwner && !$globalRoleAllowed && !$memberRoleAllowed) {
        respond(false, 'You do not have permission to modify this task.', null, 403);
    }

    return $access;
}

function require_team_manage_permission(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $role = $stmt->fetchColumn();

    if (in_array($role, ['Admin', 'Project Manager'], true)) {
        return;
    }

    $ownerCheck = $pdo->prepare('SELECT 1 FROM projects WHERE user_id = ? LIMIT 1');
    $ownerCheck->execute([$userId]);
    if ($ownerCheck->fetchColumn()) {
        return;
    }

    respond(false, 'You do not have permission to manage team members.', null, 403);
}

function validate_member_exists(PDO $pdo, ?int $memberId): void
{
    if ($memberId === null) {
        return;
    }

    if ($memberId <= 0) {
        respond(false, 'Invalid member id.', null, 422);
    }

    $stmt = $pdo->prepare('SELECT 1 FROM team_members WHERE id = ?');
    $stmt->execute([$memberId]);
    if (!$stmt->fetchColumn()) {
        respond(false, 'Referenced team member does not exist.', null, 422);
    }
}

function validate_team_member_ids(PDO $pdo, array $memberIds): array
{
    $clean = [];

    foreach ($memberIds as $memberId) {
        if (!is_scalar($memberId) || !filter_var($memberId, FILTER_VALIDATE_INT)) {
            respond(false, 'Invalid team member id.', null, 422);
        }
        $clean[] = (int) $memberId;
    }

    $clean = array_values(array_unique($clean));

    if ($clean) {
        $placeholders = implode(',', array_fill(0, count($clean), '?'));
        $stmt = $pdo->prepare("SELECT id FROM team_members WHERE id IN ({$placeholders})");
        $stmt->execute($clean);
        $existing = array_map('intval', array_column($stmt->fetchAll(), 'id'));
        if (count($existing) !== count($clean)) {
            respond(false, 'One or more team member IDs are invalid.', null, 422);
        }
    }

    return $clean;
}

function validate_member_for_project(PDO $pdo, int $projectId, ?int $memberId): void
{
    if ($memberId === null) {
        return;
    }

    validate_member_exists($pdo, $memberId);

    $stmt = $pdo->prepare(
        'SELECT 1
         FROM project_team_members
         WHERE project_id = ? AND team_member_id = ?'
    );
    $stmt->execute([$projectId, $memberId]);

    if (!$stmt->fetchColumn()) {
        respond(false, 'Assigned member must belong to the selected project.', null, 422);
    }
}

function log_activity(PDO $pdo, int $userId, string $action, string $actionType, ?int $projectId = null, ?int $taskId = null): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO activities (user_id, project_id, task_id, action_type, action)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $projectId, $taskId, $actionType, $action]);
}
