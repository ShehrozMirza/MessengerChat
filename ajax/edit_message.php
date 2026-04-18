<?php
require_once __DIR__ . '/../config.php';
require_once ROOT_DIR . '/includes/functions.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$user = $_SESSION['user'];
$id   = isset($_POST['id'])   ? (int)$_POST['id']             : 0;
$text = isset($_POST['text']) ? sanitizeString($_POST['text']) : '';

if ($id <= 0 || $text === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid input']);
    exit;
}

// Add edited column for existing installs (safe to run repeatedly)
try {
    $pdo->exec("ALTER TABLE messages ADD COLUMN IF NOT EXISTS edited TINYINT(1) NOT NULL DEFAULT 0");
} catch (\Exception $e) { /* column already exists or unsupported — ignore */ }

$stmt = queryMysql(
    "UPDATE messages SET message=?, edited=1 WHERE id=? AND auth=?",
    [$text, $id, $user]
);

if ($stmt->rowCount() === 1) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Not authorized or message not found']);
}
