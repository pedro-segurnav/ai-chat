<?php
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $bot_id = $_POST['bot_id'];
    $url = $_POST['url'];

    // Validate token, bot ID, and URL
    $stmt = $pdo->prepare("SELECT * FROM bots WHERE id = ? AND url = ? AND is_active = 1");
    $stmt->execute([$bot_id, $url]);
    $bot = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($bot && $token === 'VALID_TOKEN') {
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    }
}
?>