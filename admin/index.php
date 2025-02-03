<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db_connect.php';

$stmt = $pdo->prepare("SELECT * FROM bots WHERE customer_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$bots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
        <p>Here are your bots:</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Bot Name</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bots as $bot): ?>
                <tr>
                    <td><?php echo htmlspecialchars($bot['name']); ?></td>
                    <td><?php echo htmlspecialchars($bot['url']); ?></td>
                    <td><?php echo $bot['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?></td>
                    <td>
                        <a href="bot_view.php?id=<?php echo $bot['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                        <a href="bot_edit.php?id=<?php echo $bot['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="bot_delete.php?id=<?php echo $bot['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>