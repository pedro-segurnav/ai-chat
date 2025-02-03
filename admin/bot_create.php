<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db_connect.php';
require_once 'menu.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? null;
    $url = $_POST['url'] ?? null;
    $template_id = $_POST['template_id'] ?? null;
    $ai_enabled = isset($_POST['ai_enabled']) ? 1 : 0;
    $has_inventory = isset($_POST['has_inventory']) ? 1 : 0;
    $greeting = $_POST['greeting'] ?? 'Hello! How can I assist you today?';
    $visitor_recognition_days = (int)($_POST['visitor_recognition_days'] ?? 30);

    // Validate required fields
    if (!$name || !$url || !$template_id) {
        die('Missing required fields.');
    }

    // Insert bot details
    $stmt = $pdo->prepare("
        INSERT INTO bots (
            customer_id, name, url, template_id, ai_enabled, has_inventory, greeting, visitor_recognition_days
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $result = $stmt->execute([
        $_SESSION['user_id'], $name, $url, $template_id, $ai_enabled, $has_inventory, $greeting, $visitor_recognition_days
    ]);

    if (!$result) {
        $errorInfo = $stmt->errorInfo();
        die('Error inserting into bots table: ' . $errorInfo[2]);
    }

    $bot_id = $pdo->lastInsertId();
    error_log("Bot created with ID: $bot_id");

    // Redirect to edit page
    header("Location: bot_edit.php?id=$bot_id");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle training data upload
    if (isset($_FILES['training_data']) && $_FILES['training_data']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['training_data'];
        $data = array_map('str_getcsv', file($file['tmp_name']));
        foreach ($data as $row) {
            $input_text = $row[0];
            $output_text = $row[1];
            $stmt = $pdo->prepare("INSERT INTO bot_training_data (bot_id, input_text, output_text) VALUES (?, ?, ?)");
            $stmt->execute([$bot_id, $input_text, $output_text]);
        }
        echo "Training data uploaded successfully.";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Bot - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2>Create New Bot</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Bot Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="url" class="form-label">Licensed URL</label>
                <input type="url" class="form-control" id="url" name="url" required>
            </div>
            <div class="mb-3">
                <label for="template_id" class="form-label">Template</label>
                <select class="form-select" id="template_id" name="template_id" required>
                    <option value="1">Default Template</option>
                    <option value="2">Modern Template</option>
                </select>
            </div>
            <div class="mb-3">
    <label for="name" class="form-label">Bot Name</label>
    <input type="text" class="form-control" id="name" name="name" required>
</div>
<div class="mb-3">
    <label for="kb_text" class="form-label">Add Knowledge Base Entry</label>
    <textarea class="form-control" id="kb_text" name="kb_text" rows="4"></textarea>
</div>
<div class="mb-3">
    <label for="training_data" class="form-label">Upload Training Data (CSV)</label>
    <input type="file" class="form-control" id="training_data" name="training_data" accept=".csv">
</div>
            <div class="mb-3">
                <label for="training_data" class="form-label">Upload Training Data (CSV)</label>
                <input type="file" class="form-control" id="training_data" name="training_data" accept=".csv">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="ai_enabled" name="ai_enabled">
                <label class="form-check-label" for="ai_enabled">Enable AI</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="has_inventory" name="has_inventory">
                <label class="form-check-label" for="has_inventory">Enable Inventory</label>
            </div>
            <div class="mb-3">
                <label for="greeting" class="form-label">Greeting Message</label>
                <textarea class="form-control" id="greeting" name="greeting" rows="2" required>Hello! How can I assist you today?</textarea>
            </div>
            <div class="mb-3">
                <label for="visitor_recognition_days" class="form-label">Visitor Recognition Period (Days)</label>
                <input type="number" class="form-control" id="visitor_recognition_days" name="visitor_recognition_days" value="30" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Bot</button>
            <a href="index.php" class="btn btn-secondary">Back to Bot List</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>