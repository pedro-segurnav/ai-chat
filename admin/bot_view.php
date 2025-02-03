<?php
require_once '../includes/init.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}



$bot_id = $_GET['id'] ?? null;

if (!$bot_id) {
    die('Invalid bot ID.');
}

// Fetch bot details
$stmt = $pdo->prepare("
    SELECT id, name, url, template_id, ai_enabled, has_inventory, greeting, visitor_recognition_days
    FROM bots WHERE id = ?
");
$stmt->execute([$bot_id]);
$bot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bot) {
    die('Bot not found.');
}

// Fetch knowledge base entries
$stmt = $pdo->prepare("SELECT * FROM bot_knowledge WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$knowledge_base = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch inventory fields
$stmt = $pdo->prepare("SELECT field_name, field_type FROM bot_inventory_fields WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$inventory_fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch inventory items
$inventory_table = "bot_inventory_$bot_id";
$inventory_items = [];
if ($bot['has_inventory']) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$inventory_table'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("SELECT * FROM $inventory_table");
        $inventory_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fetch training data
$stmt = $pdo->prepare("SELECT * FROM bot_training_data WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$training_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bot - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2>Bot Details: <?php echo htmlspecialchars($bot['name']); ?></h2>

        <h3>Bot Information</h3>
        <table class="table table-bordered">
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($bot['name']); ?></td>
            </tr>
            <tr>
                <th>Licensed URL</th>
                <td><?php echo htmlspecialchars($bot['url']); ?></td>
            </tr>
            <tr>
                <th>Template</th>
                <td><?php echo $bot['template_id'] == 1 ? 'Default Template' : 'Modern Template'; ?></td>
            </tr>
            <tr>
                <th>AI Enabled</th>
                <td><?php echo $bot['ai_enabled'] ? 'Yes' : 'No'; ?></td>
            </tr>
            <tr>
                <th>Inventory Enabled</th>
                <td><?php echo $bot['has_inventory'] ? 'Yes' : 'No'; ?></td>
            </tr>
            <tr>
                <th>Greeting Message</th>
                <td><?php echo htmlspecialchars($bot['greeting']); ?></td>
            </tr>
            <tr>
                <th>Visitor Recognition Period (Days)</th>
                <td><?php echo htmlspecialchars($bot['visitor_recognition_days']); ?></td>
            </tr>
        </table>

        <h3>Knowledge Base Entries</h3>
        <?php if (empty($knowledge_base)): ?>
            <p>No knowledge base entries found.</p>
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

        <h3>Inventory Fields</h3>
        <?php if (empty($inventory_fields)): ?>
            <p>No inventory fields defined.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Field Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory_fields as $field): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($field['field_name']); ?></td>
                        <td><?php echo htmlspecialchars($field['field_type']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

<?php if ($bot['has_inventory']): ?>
    <h3>Inventory Fields</h3>
    <?php if (empty($inventory_fields)): ?>
        <p>No inventory fields defined.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Field Name</th>
                    <th>Field Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory_fields as $field): ?>
                <tr>
                    <td><?php echo htmlspecialchars($field['field_name']); ?></td>
                    <td><?php echo htmlspecialchars($field['field_type']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h3>Inventory Items</h3>
    <?php if (empty($inventory_items)): ?>
        <p>No inventory items found.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <?php foreach (array_keys($inventory_items[0]) as $header): ?>
                    <th><?php echo htmlspecialchars($header); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory_items as $item): ?>
                <tr>
                    <?php foreach ($item as $value): ?>
                    <td><?php echo htmlspecialchars($value); ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>

        <h3>Training Data</h3>
        <?php if (empty($training_data)): ?>
            <p>No training data found.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Input Text</th>
                        <th>Output Text</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($training_data as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['input_text']); ?></td>
                        <td><?php echo htmlspecialchars($entry['output_text']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
            
            <h3>Bot Statistics</h3>
<?php
$stmt = $pdo->prepare("SELECT * FROM bot_statistics WHERE bot_id = ?");
$stmt->execute([$bot_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

if ($stats): ?>
    <table class="table table-bordered">
        <tr>
            <th>Total Conversations</th>
            <td><?php echo htmlspecialchars($stats['total_conversations']); ?></td>
        </tr>
        <tr>
            <th>Total Messages</th>
            <td><?php echo htmlspecialchars($stats['total_messages']); ?></td>
        </tr>
        <tr>
            <th>Last Active</th>
            <td><?php echo htmlspecialchars($stats['last_active']); ?></td>
        </tr>
    </table>
<?php else: ?>
    <p>No statistics available yet.</p>
<?php endif; ?>
    
    
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>