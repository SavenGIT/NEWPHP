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

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $product_id = $_POST['product_id'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        $current_quantity = $_SESSION['cart'][$product_id]['quantity'];
        
        switch($_POST['action']) {
            case 'increase':
                $_SESSION['cart'][$product_id]['quantity'] = $current_quantity + 1;
                break;
            case 'decrease':
                $new_quantity = $current_quantity - 1;
                if ($new_quantity >= 1) {
                    $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                }
                break;
        }
    }
    header("Location: cart.php");
    exit;
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_submit'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $postcode = $_POST['postcode'];
    $grand_total = $_POST['grand_total'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO checkout_tbl (full_name, email, address, phone, postcode, grand_total, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$full_name, $email, $address, $phone, $postcode, $grand_total]);
        
        // Clear cart after successful checkout
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
    <style>
        .quantity-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-btn {
            padding: 2px 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="super_container">
        <?php require_once "include/header.php"; ?>
        <br><br><br><br>
        <div class="container">
            <h1>Your Cart</h1>
            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        foreach ($_SESSION['cart'] as $id => $item): 
                            $imagePath = str_replace('uploads/', '', $item['image']);
                            $item_total = (float)$item['price'] * $item['quantity'];
                            $grand_total += $item_total;
                        ?>
                        <tr>
                            <td>
                                <img src="admin/uploads/<?php echo htmlspecialchars($imagePath); ?>" width="50" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td>$<?php echo number_format((float)$item['price'], 2); ?></td>
                            <td>
                                <form method="post" class="quantity-form">
                                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                    <button type="submit" name="action" value="decrease" class="btn btn-sm btn-secondary quantity-btn">-</button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" style="width: 50px; text-align: center;" readonly>
                                    <button type="submit" name="action" value="increase" class="btn btn-sm btn-secondary quantity-btn">+</button>
                                </form>
                            </td>
                            <td>$<?php echo number_format($item_total, 2); ?></td>
                            <td>
                                <a href="remove_from_cart.php?id=<?php echo $id; ?>" class="btn btn-danger">Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                            <td><strong>$<?php echo number_format($grand_total, 2); ?></strong></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary btn-lg" data-toggle="modal" data-target="#checkoutModal">
                         Checkout
                    </button>
                </div>

                <!-- Checkout Modal -->
                <div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-labelledby="checkoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="checkoutModalLabel">Checkout Information</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <form method="post">
                                <div class="modal-body">
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label for="full_name">Full Name</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number">
                                    </div>
                                    <div class="form-group">
                                        <label for="postcode">Postcode</label>
                                        <input type="text" class="form-control" id="postcode" name="postcode" required pattern="[0-9]{5}" title="Please enter a valid 5-digit postcode">
                                    </div>
                                    <div class="form-group">
                                        <label for="grand_total">Total Amount</label>
                                        <input type="text" class="form-control" id="grand_total" name="grand_total" value="<?php echo number_format($grand_total, 2); ?>" readonly>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" name="checkout_submit" class="btn btn-primary">Confirm Checkout</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <?php require_once "include/footer.php"; ?>
    </div>
    <script>
        document.querySelectorAll('.quantity-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (this.classList.contains('submitting')) {
                    e.preventDefault();
                    return;
                }
                this.classList.add('submitting');
            });
        });
    </script>
</body>
</html>