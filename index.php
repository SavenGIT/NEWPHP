<?php
session_start();
require_once 'config.php';
$host = 'localhost';
$dbname = 'php_project'; 
$username = 'root';      
$password = '';          

$db = new Database($host, $dbname, $username, $password);
$pdo = $db->getConnection();

// Fetch products from the database
function fetchProducts($pdo) {
    $sql = "SELECT * FROM products";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$products = fetchProducts($pdo);
?>
<!doctype html>
<html lang="zxx">
<head>
    <?php require_once "include/head.php"; ?>
</head>
<body>
    <div class="super_container">
        <?php require_once "include/header.php"; ?>
        <?php require_once "include/slide.php"; ?>
        <?php require_once "include/service.php"?>

        <div class="new_arrivals">
            <div class="container">
                    <div class="col text-center">
                        <div class="section_title new_arrivals_title">
                            <h2>New Arrivals</h2>
                        </div>
                    </div>
                <div class="row">
                    
                    <div class="col">
                        <div class="product-grid" data-isotope='{ "itemSelector": ".product-item", "layoutMode": "fitRows" }'>
                            <!-- Loop through products -->
                            <?php foreach ($products as $product): ?>
                                <?php
                                // Construct the image path with a fallback
                                $imagePath = str_replace('uploads/', '', $product['image']);
                                ?>
                                <div class="product-item men">
                                    <div class="product product_filter">
                                        <div class="product_image">
                                            <!-- Use relative path with fallback -->
                                            <img src="admin/uploads/<?php echo $imagePath; ?>" alt="<?php echo $product['name']; ?>" />
                                        </div>
                                        <div class="favorite favorite_left"></div>
                                        <div class="product_info">
                                            <h6 class="product_name">
                                                <a href="single.html"><?php echo htmlspecialchars($product['name']); ?></a>
                                            </h6>
                                            <div class="product_price">
                                                $<?php echo number_format((float)$product['price'], 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="red_button add_to_cart_button">
                                        <a href="add_to_cart.php?id=<?php echo $product['id']; ?>">add to cart</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="best_sellers">
    <div class="container">
        <div class="row">
            <div class="col text-center">
                <div class="section_title new_arrivals_title">
                    <h2>Best Sellers</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="product_slider_container">
                    <div class="owl-carousel owl-theme product_slider">
                        <!-- Loop through best selling products -->
                        <?php foreach ($products as $product): ?>
                            <?php
                            // Construct the image path with a fallback
                            $imagePath = str_replace('uploads/', '', $product['image']);
                            ?>
                            <div class="owl-item product_slider_item">
                                <div class="product-item">
                                    <div class="product">
                                        <div class="product_image">
                                            <img src="admin/uploads/<?php echo $imagePath; ?>" alt="<?php echo $product['name']; ?>" />
                                        </div>
                                        <div class="favorite favorite_left"></div>
                                        <!-- Optional discount bubble if you have discount data -->
                                        <?php if (isset($product['discount']) && $product['discount'] > 0): ?>
                                            <div class="product_bubble product_bubble_right product_bubble_red d-flex flex-column align-items-center">
                                                <span>-$<?php echo number_format((float)$product['discount'], 2); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="product_info">
                                            <h6 class="product_name">
                                                <a href="single.html"><?php echo htmlspecialchars($product['name']); ?></a>
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Slider Navigation -->
                    <div class="product_slider_nav_left product_slider_nav d-flex align-items-center justify-content-center flex-column">
                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                    </div>
                    <div class="product_slider_nav_right product_slider_nav d-flex align-items-center justify-content-center flex-column">
                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        
        
        <?php require_once "include/footer.php"; ?>
    </div>
</body>
</html>