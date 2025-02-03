<?php
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $bot_id = $_POST['bot_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];

    // Insert data into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO leads (bot_id, name, email, phone, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$bot_id, $name, $email, $phone, $message]);

        // Send email notification
        $to = 'sales@example.com'; // Replace with the bot owner's email
        $subject = 'New Lead Generated';
        $body = "Name: $name\nEmail: $email\nPhone: $phone\nMessage: $message";
        mail($to, $subject, $body);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method']);
}
?>