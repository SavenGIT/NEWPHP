<?php
session_start();
require_once 'config.php';

// Initialize the Database object
$host = 'localhost';
$dbname = 'php_project';
$username = 'root';
$password = '';

$db = new Database($host, $dbname, $username, $password);
$pdo = $db->getConnection();

// Calculate grand total
$grand_total = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $grand_total += (float)$item['price'] * $item['quantity'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_submit'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $grand_total = $_POST['grand_total'];

    try {
        $stmt = $pdo->prepare("INSERT INTO checkout_tbl (full_name, email, grand_total, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$full_name, $email, $grand_total]);
        
        unset($_SESSION['cart']);
        header("Location: thank_you.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error processing checkout: " . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="zxx">
<head>
    <?php require_once "include/head.php"; ?>
</head>
<body>
    <div class="super_container">
        <?php require_once "include/header.php"; ?>
        <br><br><br><br>
        <div class="container">
            <h1>Checkout</h1>
            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="grand_total">Total Amount</label>
                        <input type="text" class="form-control" id="grand_total" name="grand_total" value="<?php echo number_format($grand_total, 2); ?>" readonly>
                    </div>
                    <button type="submit" name="checkout_submit" class="btn btn-primary">Confirm Checkout</button>
                </form>
            <?php else: ?>
                <p>Your cart is empty. <a href="index.php">Continue shopping</a></p>
            <?php endif; ?>
        </div>
        <?php require_once "include/footer.php"; ?>
    </div>
</body>
</html>