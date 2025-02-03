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

// Check if the inventory table exists
$inventory_table = "bot_inventory_$bot_id";
$stmt = $pdo->query("SHOW TABLES LIKE '$inventory_table'");
$table_exists = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$table_exists) {
    // Create the inventory table if it doesn't exist
    $sql = "
        CREATE TABLE $inventory_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_name VARCHAR(255),
            quantity INT,
            price DECIMAL(10, 2),
            description TEXT
        )
    ";
    $pdo->exec($sql);
}

// Fetch the bot's inventory
$stmt = $pdo->prepare("SELECT * FROM $inventory_table");
$stmt->execute();
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Inventory - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2>Inventory for Bot <?php echo $bot_id; ?></h2>
        <p>Below is the list of items in your bot's inventory.</p>

        <?php if (empty($inventory)): ?>
            <p>No inventory found. <a href="inventory_upload.php?id=<?php echo $bot_id; ?>">Upload inventory now</a>.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>$<?php echo htmlspecialchars($item['price']); ?></td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
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