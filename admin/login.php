<?php
session_start();
require_once '../includes/db_connect.php';

error_log("Input email: " . $email);
error_log("Input password: " . $password);
error_log("Stored hash: " . $customer['password']);

// Initialize variables
$customer = null;
$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Debugging: Log input data
    error_log("Input email: " . $email);
    error_log("Input password: " . $password);

    // Fetch user from the database
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging: Log customer data
    error_log("Customer data: " . print_r($customer, true));

    if ($customer) {
        // Debugging: Log stored hash
        error_log("Stored hash: " . $customer['password']);

        // Verify the password
        if (password_verify($password, $customer['password'])) {
            $_SESSION['user_id'] = $customer['id'];
            $_SESSION['name'] = $customer['name'];
            header('Location: index.php');
            exit;
        } else {
            echo "Invalid credentials.";
        }
    } else {
        echo "User not found.";
    }
    
    if (!$customer) {
    echo "No user found with email: " . htmlspecialchars($email);
} elseif (!password_verify($password, $customer['password'])) {
    echo "Password does not match for email: " . htmlspecialchars($email);
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
     <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <div class="container mt-5">
        <h1>Login</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>