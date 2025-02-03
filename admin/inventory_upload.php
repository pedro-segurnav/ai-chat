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

// Fetch the custom fields for the bot
$stmt = $pdo->prepare("SELECT field_name, field_type FROM bot_inventory_fields WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($fields)) {
    die('No fields defined for this bot. Please define fields first.');
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['inventory_file'])) {
    $file = $_FILES['inventory_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die('File upload failed.');
    }

    // Determine file type
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($file_extension), ['csv', 'xlsx'])) {
        die('Unsupported file format. Please upload a CSV or Excel file.');
    }

    // Parse the file
    if ($file_extension === 'csv') {
        $data = array_map('str_getcsv', file($file['tmp_name']));
    } elseif ($file_extension === 'xlsx') {
        require_once '../vendor/autoload.php'; // Ensure you have PHPExcel or PhpSpreadsheet installed
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
    }

    // Extract headers
    $headers = array_shift($data);

    // Validate headers against defined fields
    foreach ($headers as $header) {
        if (!in_array($header, array_column($fields, 'field_name'))) {
            die("Invalid column: $header. Please ensure all columns match the defined fields.");
        }
    }

    // Insert data into the database
    $inventory_table = "bot_inventory_$bot_id";
    $columns = implode(', ', array_column($fields, 'field_name'));
    $placeholders = rtrim(str_repeat('?,', count($fields)), ',');
    $stmt = $pdo->prepare("INSERT INTO $inventory_table ($columns) VALUES ($placeholders)");

    foreach ($data as $row) {
        $stmt->execute($row);
    }

    echo "Inventory uploaded successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Inventory - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2>Upload Inventory for Bot <?php echo $bot_id; ?></h2>
        <p>Upload a CSV or Excel file containing your inventory data.</p>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="inventory_file" class="form-label">Inventory File</label>
                <input type="file" class="form-control" id="inventory_file" name="inventory_file" accept=".csv,.xlsx" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>