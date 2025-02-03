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
    if (isset($_POST['kb_text'])) {
        // Add plain text entry
        $text = $_POST['kb_text'];
        $stmt = $pdo->prepare("INSERT INTO bot_knowledge (bot_id, key_phrase, response) VALUES (?, ?, ?)");
        $stmt->execute([$bot_id, 'custom', $text]);
        echo "Text entry added successfully.";
    } elseif (isset($_FILES['kb_csv'])) {
        // Upload CSV file
        $file = $_FILES['kb_csv'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            die('File upload failed.');
        }

        $data = array_map('str_getcsv', file($file['tmp_name']));
        foreach ($data as $row) {
            $key_phrase = $row[0];
            $response = $row[1];
            $stmt = $pdo->prepare("INSERT INTO bot_knowledge (bot_id, key_phrase, response) VALUES (?, ?, ?)");
            $stmt->execute([$bot_id, $key_phrase, $response]);
        }
        echo "CSV entries added successfully.";
    } elseif (isset($_POST['webservice_url'])) {
        // Connect to web service
        $webservice_url = $_POST['webservice_url'];
        $stmt = $pdo->prepare("INSERT INTO bot_webservices (bot_id, webservice_url) VALUES (?, ?)");
        $stmt->execute([$bot_id, $webservice_url]);
        echo "Web service connected successfully.";
    }
}

// Fetch existing knowledge base entries
$stmt = $pdo->prepare("SELECT * FROM bot_knowledge WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$knowledge_base = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch connected web services
$stmt = $pdo->prepare("SELECT * FROM bot_webservices WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$webservices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Base - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2>Knowledge Base for Bot <?php echo $bot_id; ?></h2>

        <div class="mb-4">
            <h4>Bot Actions</h4>
            <ul>
                <li><a href="bot_edit.php?id=<?php echo $bot_id; ?>">Edit Bot</a></li>
                <li><a href="define_inventory_fields.php?id=<?php echo $bot_id; ?>">Define Inventory Fields</a></li>
                <li><a href="inventory_upload.php?id=<?php echo $bot_id; ?>">Upload Inventory</a></li>
                <li><a href="view_inventory.php?id=<?php echo $bot_id; ?>">View Inventory</a></li>
            </ul>
        </div>

        <h3>Add Knowledge Base Entry</h3>
        <form method="POST">
            <div class="mb-3">
                <label for="kb_text" class="form-label">Add Text Entry</label>
                <textarea class="form-control" id="kb_text" name="kb_text" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Entry</button>
        </form>

        <h3>Upload CSV File</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="kb_csv" class="form-label">Upload CSV File</label>
                <input type="file" class="form-control" id="kb_csv" name="kb_csv" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload CSV</button>
        </form>

        <h3>Connect to Web Service</h3>
        <form method="POST">
            <div class="mb-3">
                <label for="webservice_url" class="form-label">Web Service URL</label>
                <input type="url" class="form-control" id="webservice_url" name="webservice_url" required>
            </div>
            <button type="submit" class="btn btn-primary">Connect</button>
        </form>

        <h3>Existing Knowledge Base Entries</h3>
        <?php if (empty($knowledge_base)): ?>
            <p>No entries found.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Key Phrase</th>
                        <th>Response</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($knowledge_base as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['key_phrase']); ?></td>
                        <td><?php echo htmlspecialchars($entry['response']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h3>Connected Web Services</h3>
        <?php if (empty($webservices)): ?>
            <p>No web services connected.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($webservices as $service): ?>
                <li><?php echo htmlspecialchars($service['webservice_url']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>