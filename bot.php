<?php

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch bot details
    $bot_id = $_GET['bot_id'];
    $stmt = $pdo->prepare("SELECT * FROM bots WHERE id = :bot_id");
    $stmt->execute(['bot_id' => $bot_id]);
    $bot = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch bot script
    $stmt = $pdo->prepare("SELECT script FROM bot_scripts WHERE bot_id = :bot_id");
    $stmt->execute(['bot_id' => $bot_id]);
    $script = $stmt->fetchColumn();

    echo "<h1>" . htmlspecialchars($bot['name']) . "</h1>";
    echo "<p>" . htmlspecialchars($script) . "</p>";

} catch (PDOException $e) {
    echo "Database error: " . htmlspecialchars($e->getMessage());
}
?>