<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db_connect.php';
require_once 'menu.php';

$bot_id = $_GET['id'] ?? null;

if (!$bot_id) {
    die('Invalid bot ID.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field_name = $_POST['field_name'];
    $field_type = $_POST['field_type'];

    if ($field_name && $field_type) {
        // Insert the field into the database
        $stmt = $pdo->prepare("INSERT INTO bot_inventory_fields (bot_id, field_name, field_type) VALUES (?, ?, ?)");
        $stmt->execute([$bot_id, $field_name, $field_type]);

        echo "Field added successfully.";
    } else {
        echo "Please provide both field name and type.";
    }
}

// Fetch existing fields for the bot
$stmt = $pdo->prepare("SELECT * FROM bot_inventory_fields WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Define Inventory Fields - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2>Define Inventory Fields for Bot <?php echo $bot_id; ?></h2>
        <p>Specify the fields (columns) for your bot's inventory.</p>

        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="field_name" placeholder="Field Name (e.g., mileage)" required>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="field_type" required>
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                        <option value="decimal">Decimal</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Add Field</button>
                </div>
            </div>
        </form>

        <h3>Existing Fields</h3>
        <?php if (empty($fields)): ?>
            <p>No fields defined yet.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Field Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fields as $field): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($field['field_name']); ?></td>
                        <td><?php echo htmlspecialchars($field['field_type']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>