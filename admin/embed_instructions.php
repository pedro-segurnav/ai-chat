<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db_connect.php';

$bot_id = $_GET['id'] ?? null;

if (!$bot_id) {
    echo '<div class="alert alert-danger">Invalid bot ID.</div>';
    exit;
}

// Fetch bot details
$stmt = $pdo->prepare("SELECT * FROM bots WHERE id = ? AND customer_id = ?");
$stmt->execute([$bot_id, $_SESSION['user_id']]);
$bot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bot) {
    echo '<div class="alert alert-danger">Bot not found.</div>';
    exit;
}

$bot_script_url = "https://ai.oemdrive.com/bots/bot.js?bot_id=" . $bot_id;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Embed Bot - AICHAT Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2>Embed Bot Script</h2>
                <p>To add your bot to your website, follow these steps:</p>

                <ol>
                        <li>Copy the following JavaScript code and paste it just before the closing <code></body></code> tag in your website's HTML file:</li>
<pre><code class="javascript">
<script src="<?php echo htmlspecialchars($bot_script_url); ?>"></script>
</code></pre>

                    <li>Alternatively, you can download the bot script and host it on your own server:</li>
<a href="<?php echo htmlspecialchars($bot_script_url); ?>" download="bot.js" class="btn btn-primary mb-3">Download Bot Script</a>

<li>After downloading, upload the script to your server and reference it in your HTML file like this:</li>
<pre><code class="html">
<script src="<?php echo htmlspecialchars($bot_script_url); ?>"></script>
</code></pre>
                    </code></pre>
                </ol>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>