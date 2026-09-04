<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }
require __DIR__ . '/config.php';

function body(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}
function response(bool $ok, string $message, $data = null): void {
    echo json_encode(['success'=>$ok, 'message'=>$message, 'data'=>$data]);
    exit;
}

try {
    $action = $_GET['action'] ?? '';

    if ($action === 'health') {
        $pdo->query('SELECT 1');
        response(true, 'TaskFlow API is connected to MySQL');
    }

    if ($action === 'get_state') {
        $state = [];
        foreach (['users','members','projects','tasks','activities'] as $table) {
            $state[$table] = $pdo->query("SELECT * FROM `$table` ORDER BY id_num ASC")->fetchAll();
        }
        $settings = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        foreach ($settings as $setting) { $state['settings'][$setting['setting_key']] = json_decode($setting['setting_value'], true); }
        response(true, 'State loaded', $state);
    }

    if ($action === 'save_state') {
        $data = body();
        $pdo->beginTransaction();
        foreach (['users','members','projects','tasks','activities'] as $table) {
            if (!isset($data[$table]) || !is_array($data[$table])) continue;
            $pdo->exec("DELETE FROM `$table`");
            foreach ($data[$table] as $row) {
                if (!is_array($row)) continue;
                $json = json_encode($row, JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("INSERT INTO `$table` (external_id, payload) VALUES (?, ?)");
                $stmt->execute([$row['id'] ?? uniqid(), $json]);
            }
        }
        if (isset($data['settings']) && is_array($data['settings'])) {
            $pdo->exec('DELETE FROM settings');
            $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');
            foreach ($data['settings'] as $key => $value) { $stmt->execute([$key, json_encode($value)]); }
        }
        $pdo->commit();
        response(true, 'All TaskFlow data saved to MySQL');
    }

    response(false, 'Unknown action');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    response(false, $e->getMessage());
}
