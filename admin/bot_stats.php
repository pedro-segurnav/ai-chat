<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db_connect.php';

$bot_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM bots WHERE id = ? AND customer_id = ?");
$stmt->execute([$bot_id, $_SESSION['user_id']]);
$bot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bot) {
    die('Bot not found.');
}

// Fetch stats
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_messages FROM leads WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bot Statistics</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Statistics for <?php echo $bot['name']; ?></h1>
        <p>Total Messages: <?php echo $stats['total_messages']; ?></p>
        <!-- Add more stats here -->
    </div>
</body>
</html>