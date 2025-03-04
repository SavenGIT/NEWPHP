<?php
session_start();
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
            <h1>Thank You!</h1>
            <p>Your order has been successfully processed.</p>
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
        </div>
        <?php require_once "include/footer.php"; ?>
    </div>
</body>
</html>