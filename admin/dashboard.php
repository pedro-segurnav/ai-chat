<?php
require_once '../includes/init.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch all bots for the dropdown
$stmt = $pdo->prepare("SELECT id, name FROM bots");
$stmt->execute();
$bots = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_bot_id = $_GET['bot_id'] ?? null;
if ($selected_bot_id) {
    // Fetch visitor statistics for the selected bot
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total_visits, SUM(visits) AS total_interactions
        FROM visitors WHERE bot_id = ?
    ");
    $stmt->execute([$selected_bot_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch recent chat logs for the selected bot
    $stmt = $pdo->prepare("
        SELECT message, response, created_at
        FROM chat_logs
        WHERE bot_id = ?
        ORDER BY created_at DESC
        LIMIT 100
    ");
    $stmt->execute([$selected_bot_id]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bot Analytics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container mt-4">
        <h2>Bot Analytics</h2>

        <!-- Bot Details Section -->
        <div id="bot-details" class="mb-4">
            <h3>Bot Details</h3>
            <p><strong>WhatsApp Number:</strong> <span id="whatsapp-number">Loading...</span></p>
            <p><strong>Facebook Page:</strong> <span id="facebook-page">Loading...</span></p>
            <p><strong>Instagram Account:</strong> <span id="instagram-account">Loading...</span></p>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const botId = <?php echo json_encode($selected_bot_id); ?>;
            if (botId) {
                fetch(`/api/bots/${botId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('whatsapp-number').textContent = data.whatsapp_number || 'N/A';
                        document.getElementById('facebook-page').textContent = data.facebook_page_id || 'N/A';
                        document.getElementById('instagram-account').textContent = data.instagram_account_id || 'N/A';
                    })
                    .catch(() => {
                        document.getElementById('whatsapp-number').textContent = 'Error loading data';
                        document.getElementById('facebook-page').textContent = 'Error loading data';
                        document.getElementById('instagram-account').textContent = 'Error loading data';
                    });
            }
        });
        </script>

        <!-- Bot Selection Dropdown -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="bot_id" class="form-label">Select Bot</label>
                    <select class="form-select" id="bot_id" name="bot_id" required>
                        <option value="">-- Select a Bot --</option>
                        <?php foreach ($bots as $bot): ?>
                            <option value="<?php echo htmlspecialchars($bot['id']); ?>" 
                                <?php echo ($bot['id'] == $selected_bot_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($bot['name']); ?> (ID: <?php echo htmlspecialchars($bot['id']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 align-self-end">
                    <button type="submit" class="btn btn-primary">Load Analytics</button>
                </div>
            </div>
        </form>

        <?php if ($selected_bot_id): ?>
            <h3>Visitor Statistics</h3>
            <table class="table table-bordered">
                <tr>
                    <th>Total Visits</th>
                    <td><?php echo htmlspecialchars($stats['total_visits']); ?></td>
                </tr>
                <tr>
                    <th>Total Interactions</th>
                    <td><?php echo htmlspecialchars($stats['total_interactions']); ?></td>
                </tr>
            </table>

            <h3>Recent Chat Logs</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>User Message</th>
                        <th>Bot Response</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="bg-light text-dark"><?php echo htmlspecialchars($log['message']); ?></td>
                        <td class="bg-secondary text-white"><?php echo htmlspecialchars($log['response']); ?></td>
                        <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Please select a bot to view its analytics.</p>
        <?php endif; ?>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>