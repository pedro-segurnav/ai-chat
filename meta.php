<?php
$botId = $_POST['bot_id'];
$pdo = new PDO("mysql:host=localhost;dbname=ai;charset=utf8mb4", "root", "");
$stmt = $pdo->prepare("SELECT facebook_page_id, instagram_account_id FROM bots WHERE id = :bot_id");
$stmt->execute(['bot_id' => $botId]);
$bot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bot || (!$bot['facebook_page_id'] && !$bot['instagram_account_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Bot not found or platform credentials not configured']);
    exit;
}

$accessToken = $bot['facebook_page_id'] ? 'FACEBOOK_ACCESS_TOKEN' : 'INSTAGRAM_ACCESS_TOKEN';
$url = "https://graph.facebook.com/v18.0/me/messages?access_token=$accessToken";

$data = [
    'recipient' => ['id' => $_POST['sender_id']],
    'message' => ['text' => processBotMessage($_POST['message'], $_POST['sender_id'], $botId)],
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_exec($ch);
curl_close($ch);

function processBotMessage($message, $user, $botId) {
    return "Hello! This is bot #$botId. How can I assist you?";
}
?>