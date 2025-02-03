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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['input_text']) && isset($_POST['output_text'])) {
        // Add new training data
        $input_text = $_POST['input_text'];
        $output_text = $_POST['output_text'];
        $stmt = $pdo->prepare("INSERT INTO bot_training_data (bot_id, input_text, output_text) VALUES (?, ?, ?)");
        $stmt->execute([$bot_id, $input_text, $output_text]);
        echo "Training data added successfully.";
    } elseif (isset($_FILES['training_csv'])) {
        // Upload CSV file
        $file = $_FILES['training_csv'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            die('File upload failed.');
        }

        $data = array_map('str_getcsv', file($file['tmp_name']));
        foreach ($data as $row) {
            $input_text = $row[0];
            $output_text = $row[1];
            $stmt = $pdo->prepare("INSERT INTO bot_training_data (bot_id, input_text, output_text) VALUES (?, ?, ?)");
            $stmt->execute([$bot_id, $input_text, $output_text]);
        }
        echo "CSV entries added successfully.";
    }
}

// Fetch existing training data
$stmt = $pdo->prepare("SELECT * FROM bot_training_data WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$training_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Data - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2>Training Data for Bot <?php echo $bot_id; ?></h2>

        <h3>Add New Training Data</h3>
        <form method="POST">
            <div class="mb-3">
                <label for="input_text" class="form-label">Input Text</label>
                <textarea class="form-control" id="input_text" name="input_text" rows="2" required></textarea>
            </div>
            <div class="mb-3">
                <label for="output_text" class="form-label">Output Text</label>
                <textarea class="form-control" id="output_text" name="output_text" rows="2" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Entry</button>
        </form>

        <h3>Upload CSV File</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="training_csv" class="form-label">Upload CSV File</label>
                <input type="file" class="form-control" id="training_csv" name="training_csv" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload CSV</button>
        </form>

        <h3>Existing Training Data</h3>
        <?php if (empty($training_data)): ?>
            <p>No training data found.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Input Text</th>
                        <th>Output Text</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($training_data as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['input_text']); ?></td>
                        <td><?php echo htmlspecialchars($entry['output_text']); ?></td>
                        <td>
                            <a href="delete_training_data.php?id=<?php echo $entry['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this entry?')">Delete</a>
                        </td>
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