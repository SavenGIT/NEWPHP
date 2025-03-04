<?php
session_start();
require_once 'config.php';

$host = 'localhost';
$dbname = 'php_project'; 
$username = 'root';      
$password = '';          

$db = new Database($host, $dbname, $username, $password);
$pdo = $db->getConnection();

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Initialize the cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add the product to the cart
        $product_id = $product['id'];
        if (isset($_SESSION['cart'][$product_id])) {
            // Increment quantity if the product is already in the cart
            $_SESSION['cart'][$product_id]['quantity'] += 1;
        } else {
            // Add the product to the cart with an initial quantity of 1
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1,
            ];
        }
        header("Location: index.php");
        exit();
    }
}
?>