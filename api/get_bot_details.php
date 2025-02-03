<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

$bot_id = $_GET['bot_id'] ?? null;

if (!$bot_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing bot_id']);
    exit;
}

// Fetch bot details
$stmt = $pdo->prepare("
    SELECT name, greeting FROM bots WHERE id = ?
");
$stmt->execute([$bot_id]);
$bot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bot) {
    http_response_code(404);
    echo json_encode(['error' => 'Bot not found']);
    exit;
}

echo json_encode([
    'name' => $bot['name'],
    'greeting' => $bot['greeting']
]);
?>