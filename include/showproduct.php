<?php
// Database Connection Class
class Database {
    private $host = 'localhost';
    private $dbname = 'php_project'; // Replace with your database name
    private $username = 'root';      // Replace with your MySQL username
    private $password = '';          // Replace with your MySQL password
    private $conn;

    public function __construct() {
        try {
            $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function fetchProducts() {
        $sql = "SELECT * FROM products";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Initialize Database Object
$db = new Database();
$products = $db->fetchProducts();
?>
        <div class="new_arrivals">
            <div class="container">
                <h1>OUR PRODUCTS</h1>
                <div class="row">
                    <div class="col">
                        <div class="product-grid" data-isotope='{ "itemSelector": ".product-item", "layoutMode": "fitRows" }'>
                            <!-- Loop through products -->
                            <?php foreach ($products as $product): ?>
                            <div class="product-item men">
                                <div class="product product_filter">
                                    <div class="product_image">
                                        <!-- Use relative path with fallback -->
                                        <img src="../admin/uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                                    </div>
                                    <div class="favorite favorite_left"></div>
                                    <div class="product_info">
                                        <h6 class="product_name">
                                            <a href="single.html"><?php echo $product['name']; ?></a>
                                        </h6>
                                        <div class="product_price">
                                            $<?php echo number_format((float)$product['price'], 2); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="red_button add_to_cart_button">
                                    <a href="#">add to cart</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>