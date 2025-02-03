<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db_connect.php';

$bot_id = $_GET['id'] ?? null;

if (!$bot_id) {
    echo '<div class="alert alert-danger">Invalid bot ID.</div>';
    exit;
}

// Fetch bot details to ensure it belongs to the logged-in customer
$stmt = $pdo->prepare("SELECT * FROM bots WHERE id = ? AND customer_id = ?");
$stmt->execute([$bot_id, $_SESSION['user_id']]);
$bot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bot) {
    echo '<div class="alert alert-danger">Bot not found or you do not have permission to delete it.</div>';
    exit;
}

// Delete associated data (knowledge base, leads, etc.)
$pdo->prepare("DELETE FROM bot_knowledge WHERE bot_id = ?")->execute([$bot_id]);
$pdo->prepare("DELETE FROM leads WHERE bot_id = ?")->execute([$bot_id]);

// Delete the bot itself
$pdo->prepare("DELETE FROM bots WHERE id = ?")->execute([$bot_id]);

// Redirect to the dashboard
header('Location: index.php');
exit;
?>