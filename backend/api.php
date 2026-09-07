<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require __DIR__ . '/config.php';

function body(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

function response(bool $ok, string $message, $data = null): void {
    echo json_encode([
        'success' => $ok,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function rows(PDO $pdo, string $table): array {
    $items = $pdo
        ->query("SELECT payload FROM \`$table\` ORDER BY id_num ASC")
        ->fetchAll(PDO::FETCH_COLUMN);

    return array_values(array_filter(
        array_map(fn($item) => json_decode($item, true), $items),
        fn($item) => is_array($item)
    ));
}

try {
    $action = $_GET['action'] ?? '';

    if ($action === 'health') {
        $pdo->query('SELECT 1');
        response(true, 'TaskFlow API is connected to MySQL');
    }

    if ($action === 'get_state') {
        $state = [];

        foreach (['users', 'members', 'projects', 'tasks', 'activities'] as $table) {
            $state[$table] = rows($pdo, $table);
        }

        $state['settings'] = [];

        foreach ($pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $setting) {
            $state['settings'][$setting['setting_key']] =
                json_decode($setting['setting_value'], true);
        }

        response(true, 'State loaded from MySQL', $state);
    }

    if ($action === 'save_state') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            response(false, 'Use POST for save_state');
        }

        $data = body();
        $pdo->beginTransaction();

        foreach (['users', 'members', 'projects', 'tasks', 'activities'] as $table) {
            $rowsToSave = isset($data[$table]) && is_array($data[$table])
                ? $data[$table]
                : [];

            $pdo->exec("DELETE FROM \`$table\`");

            $stmt = $pdo->prepare(
                "INSERT INTO \`$table\` (external_id, payload) VALUES (?, ?)"
            );

            foreach ($rowsToSave as $row) {
                if (!is_array($row)) continue;

                $id = $row['id'] ?? uniqid($table . '-', true);

                $stmt->execute([
                    (string)$id,
                    json_encode($row, JSON_UNESCAPED_UNICODE)
                ]);
            }
        }

        $pdo->exec('DELETE FROM settings');

        $settings = isset($data['settings']) && is_array($data['settings'])
            ? $data['settings']
            : [];

        $settingStmt = $pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)'
        );

        foreach ($settings as $key => $value) {
            $settingStmt->execute([
                (string)$key,
                json_encode($value, JSON_UNESCAPED_UNICODE)
            ]);
        }

        $pdo->commit();

        response(true, 'All TaskFlow data saved to MySQL');
    }

    http_response_code(400);
    response(false, 'Unknown action');
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    response(false, $e->getMessage());
}
