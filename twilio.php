<?php
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;

$sid = 'YOUR_TWILIO_SID';
$token = 'YOUR_TWILIO_TOKEN';
$client = new Client($sid, $token);

// Fetch the bot's WhatsApp number from the database
$botId = $_POST['bot_id']; // Assuming the bot ID is passed in the request
$pdo = new PDO("mysql:host=localhost;dbname=ai;charset=utf8mb4", "root", "");
$stmt = $pdo->prepare("SELECT whatsapp_number FROM bots WHERE id = :bot_id");
$stmt->execute(['bot_id' => $botId]);
$bot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bot || !$bot['whatsapp_number']) {
    http_response_code(400);
    echo json_encode(['error' => 'Bot not found or WhatsApp number not configured']);
    exit;
}

$fromNumber = 'whatsapp:' . $bot['whatsapp_number'];
$to = $_POST['From']; // Sender's WhatsApp number
$messageText = $_POST['Body'];

// Process the message
$response = processBotMessage($messageText, $to, $botId);

// Send the response back to the user
$client->messages->create($to, [
    'from' => $fromNumber,
    'body' => $response,
]);

function processBotMessage($message, $user, $botId) {
    // Logic to determine the bot's response
    return "Hello! This is bot #$botId. How can I assist you?";
}
?>