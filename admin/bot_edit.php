<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db_connect.php';

$bot_id = $_GET['id'] ?? null;

if (!$bot_id) {
    die('Invalid bot ID.');
}

// Fetch bot details
$stmt = $pdo->prepare("
    SELECT id, name, url, template_id, ai_enabled, has_inventory, greeting, visitor_recognition_days,
           company_name, location, phone_number, email, critical_rules
    FROM bots WHERE id = ?
");
$stmt->execute([$bot_id]);
$bot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bot) {
    die('Bot not found.');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // General bot details
    $name = $_POST['name'] ?? null;
    $url = $_POST['url'] ?? null;
    $template_id = $_POST['template_id'] ?? null;
    $ai_enabled = isset($_POST['ai_enabled']) ? 1 : 0;
    $has_inventory = isset($_POST['has_inventory']) ? 1 : 0;
    $greeting = $_POST['greeting'] ?? '';
    $visitor_recognition_days = (int)($_POST['visitor_recognition_days'] ?? 30);

    // Custom parameters
    $company_name = $_POST['company_name'] ?? '';
    $location = $_POST['location'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $critical_rules = $_POST['critical_rules'] ?? '';

    // Update bot details
    $stmt = $pdo->prepare("
        UPDATE bots SET
            name = ?, url = ?, template_id = ?, ai_enabled = ?, has_inventory = ?, greeting = ?, visitor_recognition_days = ?,
            company_name = ?, location = ?, phone_number = ?, email = ?, critical_rules = ?
        WHERE id = ?
    ");
    $result = $stmt->execute([
        $name, $url, $template_id, $ai_enabled, $has_inventory, $greeting, $visitor_recognition_days,
        $company_name, $location, $phone_number, $email, $critical_rules, $bot_id
    ]);

    if (!$result) {
        $errorInfo = $stmt->errorInfo();
        die('Error updating bot: ' . $errorInfo[2]);
    }

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

    // Refresh the bot data after update
    $stmt = $pdo->prepare("
        SELECT id, name, url, template_id, ai_enabled, has_inventory, greeting, visitor_recognition_days,
               company_name, location, phone_number, email, critical_rules
        FROM bots WHERE id = ?
    ");
    $stmt->execute([$bot_id]);
    $bot = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Bot updated successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bot - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container mt-4">
        <h2>Edit Bot: <?php echo htmlspecialchars($bot['name']); ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <!-- General Bot Details -->
            <div class="mb-3">
                <label for="name" class="form-label">Bot Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($bot['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="url" class="form-label">Licensed URL</label>
                <input type="url" class="form-control" id="url" name="url" value="<?php echo htmlspecialchars($bot['url']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="template_id" class="form-label">Template</label>
                <select class="form-select" id="template_id" name="template_id" required>
                    <option value="1" <?php echo ($bot['template_id'] == 1) ? 'selected' : ''; ?>>Default Template</option>
                    <option value="2" <?php echo ($bot['template_id'] == 2) ? 'selected' : ''; ?>>Modern Template</option>
                </select>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="ai_enabled" name="ai_enabled" <?php echo ($bot['ai_enabled']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ai_enabled">Enable AI</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="has_inventory" name="has_inventory" <?php echo ($bot['has_inventory']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="has_inventory">Enable Inventory</label>
            </div>
            <div class="mb-3">
                <label for="greeting" class="form-label">Greeting Message</label>
                <textarea class="form-control" id="greeting" name="greeting" rows="2" required><?php echo htmlspecialchars($bot['greeting']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="visitor_recognition_days" class="form-label">Visitor Recognition Period (Days)</label>
                <input type="number" class="form-control" id="visitor_recognition_days" name="visitor_recognition_days" value="<?php echo htmlspecialchars($bot['visitor_recognition_days']); ?>" min="1" required>
            </div>

            <!-- Custom Parameters -->
            <h3>Custom Parameters</h3>
            <div class="mb-3">
                <label for="company_name" class="form-label">Company Name</label>
                <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($bot['company_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($bot['location']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($bot['phone_number']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($bot['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="critical_rules" class="form-label">Critical Rules</label>
                <textarea class="form-control" id="critical_rules" name="critical_rules" rows="4"><?php echo htmlspecialchars($bot['critical_rules']); ?></textarea>
            </div>

            <!-- Training Data Upload -->
            <h3>Upload Training Data (CSV)</h3>
            <div class="mb-3">
                <label for="training_data" class="form-label">Upload Training Data</label>
                <input type="file" class="form-control" id="training_data" name="training_data" accept=".csv">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="index.php" class="btn btn-secondary">Back to Bot List</a>
        </form>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>