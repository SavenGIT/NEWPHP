<?php
require_once "./config.php";

// Instantiate the Database class
$host = 'localhost'; // Replace with your database host
$dbname = 'php_project'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $database = new Database($host, $dbname, $username, $password);
    $pdo = $database->getConnection();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user from the database
    $sql = "SELECT * FROM users WHERE user_name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        echo "<script>alert('Login successful!'); window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('Invalid username or password.'); window.location.href = 'index.php';</script>";
    }
}
?>