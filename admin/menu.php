<?php
require_once '../includes/init.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch projects for the current customer
    $customer_id = 1; // Replace with actual customer ID
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE customer_id = :customer_id");
    $stmt->execute(['customer_id' => $customer_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Projects</h1>";
    echo "<ul>";
    foreach ($projects as $project) {
        echo "<li><strong>" . htmlspecialchars($project['name']) . "</strong>: " . htmlspecialchars($project['description']) . "</li>";

        // Fetch bots for the project
        $stmt = $pdo->prepare("SELECT * FROM bots WHERE project_id = :project_id");
        $stmt->execute(['project_id' => $project['id']]);
        $bots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<ul>";
        foreach ($bots as $bot) {
            echo "<li><a href='bot.php?bot_id=" . $bot['id'] . "'>" . htmlspecialchars($bot['name']) . "</a></li>";
        }
        echo "</ul>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "Database error: " . htmlspecialchars($e->getMessage());
}
?>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">AICHAT Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Bot List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bot_create.php">Create Bot</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>