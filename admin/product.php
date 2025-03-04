<?php
class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $pdo;

    public function __construct($host, $dbname, $username, $password) {
        try {
            $this->host = $host;
            $this->dbname = $dbname;
            $this->username = $username;
            $this->password = $password;

            $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=utf8";
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}

class ProductManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    public function addProduct($name, $price, $description, $imagePath = null) {
        if (empty($name)) {
            throw new Exception("Product name is required.");
        }
        if (!is_numeric($price) || $price <= 0) {
            throw new Exception("Price must be a positive number.");
        }

        $stmt = $this->pdo->prepare("INSERT INTO products (name, price, description, image) VALUES (:name, :price, :description, :image)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image', $imagePath);

        if (!$stmt->execute()) {
            throw new Exception("Failed to add product.");
        }
    }


    public function deleteProduct($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE Product_id = :id");
        $stmt->bindParam(':id', $id);

        if (!$stmt->execute()) {
            throw new Exception("Failed to delete product.");
        }
    }

    // Update a product
    public function updateProduct($id, $name, $price, $description, $imagePath = null) {
        if (empty($name)) {
            throw new Exception("Product name is required.");
        }
        if (!is_numeric($price) || $price <= 0) {
            throw new Exception("Price must be a positive number.");
        }

        $sql = "UPDATE products SET name = :name, price = :price, description = :description";
        if ($imagePath) {
            $sql .= ", image = :image";
        }
        $sql .= " WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':description', $description);
        if ($imagePath) {
            $stmt->bindParam(':image', $imagePath);
        }

        if (!$stmt->execute()) {
            throw new Exception("Failed to update product.");
        }
    }

    // Fetch all products
    public function getAllProducts() {
        $stmt = $this->pdo->query("SELECT * FROM products");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// ProductController Class
class ProductController {
    private $productManager;

    public function __construct($productManager) {
        $this->productManager = $productManager;
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'add') {
                $this->handleAddProduct();
            } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
                $this->handleDeleteProduct();
            } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
                $this->handleUpdateProduct();
            }
        }
    }

    private function handleAddProduct() {
        try {
            $name = trim($_POST['name']);
            $price = trim($_POST['price']);
            $description = trim($_POST['description']);
    
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imageName = basename($_FILES['image']['name']);
                $imagePath = $uploadDir . $imageName;
                move_uploaded_file($_FILES['image']['tmp_name'], $imagePath); // image path for take image from it /upload
            }
    
            $this->productManager->addProduct($name, $price, $description, $imagePath);
            echo "<div class='alert alert-success'>Product added successfully!</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }

    private function handleDeleteProduct() {
        try {
            $id = $_POST['id'];
            $this->productManager->deleteProduct($id);
            echo "<div class='alert alert-success'>Product deleted successfully!</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }

    private function handleUpdateProduct() {
        try {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            $price = trim($_POST['price']);
            $description = trim($_POST['description']);

            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imageName = basename($_FILES['image']['name']);
                $imagePath = $uploadDir . $imageName;
                move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
            }

            $this->productManager->updateProduct($id, $name, $price, $description, $imagePath);
            echo "<div class='alert alert-success'>Product updated successfully!</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }

    public function fetchProducts() {
        return $this->productManager->getAllProducts();
    }

    public function fetchProductForEdit($id) {
        return $this->productManager->getProductById($id);
    }
}

try {
    $db = new Database('localhost', 'php_project', 'root', '');
    $pdo = $db->getConnection();

    $productManager = new ProductManager($pdo);
    $controller = new ProductController($productManager);

    $controller->handleRequest();

    $products = $controller->fetchProducts();
    $editProduct = null;
    if (isset($_GET['edit'])) {
        $editProduct = $controller->fetchProductForEdit($_GET['edit']);
    }
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>");
}
?>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Product Management</h1>
        <button id="openInsertModal" class="btn btn-success mb-3">Add New Product</button>

        <!-- Insert Product Modal -->
        <div class="modal fade" id="insertModal" tabindex="-1" role="dialog" aria-labelledby="insertModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="insertModalLabel">Insert Product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add">
                            <div class="form-group">
                                <label for="name">Name:</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Price:</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <textarea class="form-control" id="description" name="description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="image">Image:</label>
                                <input type="file" class="form-control-file" id="image" name="image">
                            </div>
                            <button type="submit" class="btn btn-success btn-block">Add Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Update Product Modal -->
        <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateModalLabel">Update Product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" id="update_id" name="id">
                            <div class="form-group">
                                <label for="update_name">Name:</label>
                                <input type="text" class="form-control" id="update_name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="update_price">Price:</label>
                                <input type="number" step="0.01" class="form-control" id="update_price" name="price" required>
                            </div>
                            <div class="form-group">
                                <label for="update_description">Description:</label>
                                <textarea class="form-control" id="update_description" name="description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="update_image">Image:</label>
                                <input type="file" class="form-control-file" id="update_image" name="image">
                            </div>
                            <button type="submit" class="btn btn-success btn-block">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo ($product['id']); ?></td>
                        <td><?php echo ($product['name']); ?></td>
                        <td><img src="<?php echo ($product['image']); ?>" alt="<?php echo ($product['name']); ?>" class="img-fluid" style="max-width: 100px;"></td>
                        <td>$<?php echo number_format((float)$product['price'], 2); ?></td>
                        <td><?php echo ($product['description']); ?></td>
                        <td class="actions">
                            <button class="btn btn-primary btn-sm edit_button" 
                                    data-id="<?php echo $product['id']; ?>" 
                                    data-name="<?php echo ($product['name']); ?>" 
                                    data-price="<?php echo ($product['price']); ?>" 
                                    data-description="<?php echo ($product['description']); ?>" 
                                    data-image="<?php echo ($product['image']); ?>">Update</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            // Open Insert Modal
            $('#openInsertModal').click(function () {
                $('#insertModal').modal('show');
            });

            // Populate Update Modal
            $('.edit_button').click(function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const price = $(this).data('price');
                const description = $(this).data('description');
                const image = $(this).data('image');

                $('#update_id').val(id);
                $('#update_name').val(name);
                $('#update_price').val(price);
                $('#update_description').val(description);

                $('#updateModal').modal('show');
            });
        });
    </script>
</body>