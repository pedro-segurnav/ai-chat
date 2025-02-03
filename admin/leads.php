<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db_connect.php';
require_once 'menu.php';

$stmt = $pdo->prepare("SELECT * FROM leads WHERE bot_id IN (SELECT id FROM bots WHERE customer_id = ?)");
$stmt->execute([$_SESSION['user_id']]);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$content = '
<div class="row">
    <div class="col-md-12">
        <h2>Your Leads</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>';

foreach ($leads as $lead) {
    $content .= '
                <tr>
                    <td>' . htmlspecialchars($lead['name']) . '</td>
                    <td>' . htmlspecialchars($lead['email']) . '</td>
                    <td>' . htmlspecialchars($lead['phone']) . '</td>
                    <td>' . htmlspecialchars($lead['message']) . '</td>
                    <td>' . htmlspecialchars($lead['created_at']) . '</td>
                </tr>';
}

$content .= '
            </tbody>
        </table>
    </div>
</div>';

echo str_replace('<?php echo $content ?? ""; ?>', $content, file_get_contents('menu.php'));
?>