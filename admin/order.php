<?php
require_once '../config.php';
class Order {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllOrders() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM checkout_tbl ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching orders: " . $e->getMessage());
        }
    }
}
class OrderAdmin {
    private $pdo;
    private $orderModel;
    public $orders = [];
    public $error = null;

    public function __construct($host, $dbname, $username, $password) {
        try {
            $db = new Database($host, $dbname, $username, $password);
            $this->pdo = $db->getConnection();
            $this->orderModel = new Order($this->pdo);
            $this->loadOrders();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    private function loadOrders() {
        $this->orders = $this->orderModel->getAllOrders();
    }

    public function hasError() {
        return $this->error !== null;
    }

    public function getError() {
        return $this->error;
    }

    public function hasOrders() {
        return !empty($this->orders);
    }
}
$orderAdmin = new OrderAdmin('localhost', 'php_project', 'root', '');
?>

<head>
    <?php require_once "include/head.php"; ?>
    <style>
        .container {
            margin-top: 50px;
            margin-bottom: 50px;
        }
        .table-responsive {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .order-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="super_container">
        <?php require_once "include/header.php"; ?>
        
        <div class="container">
            <div class="order-header text-center">
                <h3 class="mb-0">ORDER ADMIN</h3>
            </div>

            <?php if ($orderAdmin->hasError()): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($orderAdmin->getError()); ?>
                </div>
            <?php endif; ?>

            <?php if ($orderAdmin->hasOrders()): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Full Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Address</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Postcode</th>
                                <th scope="col">Grand Total</th>
                                <th scope="col">Order Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderAdmin->orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                                    <td><?php echo htmlspecialchars($order['address']); ?></td>
                                    <td><?php echo htmlspecialchars($order['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($order['postcode']); ?></td>
                                    <td>$<?php echo number_format($order['grand_total'], 2); ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    No orders found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>