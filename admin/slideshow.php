<?php
// Database Class
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

// SlideManager Class
class SlideManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Add a new slide
    public function addSlide($title, $text, $imgPath, $link) {
        if (empty($title)) {
            throw new Exception("Title is required.");
        }

        $stmt = $this->pdo->prepare("INSERT INTO tb_slide (title, text, img, link) VALUES (:title, :text, :img, :link)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':text', $text);
        $stmt->bindParam(':img', $imgPath);
        $stmt->bindParam(':link', $link);

        if (!$stmt->execute()) {
            throw new Exception("Failed to add slide.");
        }
    }

    // Delete a slide by ID
    public function deleteSlide($sid) {
        $stmt = $this->pdo->prepare("DELETE FROM tb_slide WHERE sid = :sid");
        $stmt->bindParam(':sid', $sid);

        if (!$stmt->execute()) {
            throw new Exception("Failed to delete slide.");
        }
    }

    // Update a slide
    public function updateSlide($sid, $title, $text, $imgPath, $link) {
        if (empty($title)) {
            throw new Exception("Title is required.");
        }

        $sql = "UPDATE tb_slide SET title = :title, text = :text, link = :link";
        if ($imgPath) {
            $sql .= ", img = :img";
        }
        $sql .= " WHERE sid = :sid";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':sid', $sid);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':text', $text);
        $stmt->bindParam(':link', $link);
        if ($imgPath) {
            $stmt->bindParam(':img', $imgPath);
        }

        if (!$stmt->execute()) {
            throw new Exception("Failed to update slide.");
        }
    }

    // Fetch all slides
    public function getAllSlides() {
        $stmt = $this->pdo->query("SELECT * FROM tb_slide");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch a single slide by ID
    public function getSlideById($sid) {
        $stmt = $this->pdo->prepare("SELECT * FROM tb_slide WHERE sid = :sid");
        $stmt->bindParam(':sid', $sid);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// SlideController Class
class SlideController {
    private $slideManager;

    public function __construct($slideManager) {
        $this->slideManager = $slideManager;
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'add') {
                $this->handleAddSlide();
            } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
                $this->handleDeleteSlide();
            } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
                $this->handleUpdateSlide();
            }
        }
    }

    private function handleAddSlide() {
        try {
            $title = trim($_POST['title']);
            $text = trim($_POST['text']);
            $link = trim($_POST['link']);

            $imgPath = '';
            if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imageName = basename($_FILES['img']['name']);
                $imgPath = $uploadDir . $imageName;
                move_uploaded_file($_FILES['img']['tmp_name'], $imgPath);
            }

            $this->slideManager->addSlide($title, $text, $imgPath, $link);
            echo "<div class='alert alert-success'>Slide added successfully!</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }

    private function handleDeleteSlide() {
        try {
            $sid = $_POST['sid'];
            $this->slideManager->deleteSlide($sid);
            echo "<div class='alert alert-success'>Slide deleted successfully!</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }

    private function handleUpdateSlide() {
        try {
            $sid = $_POST['sid'];
            $title = trim($_POST['title']);
            $text = trim($_POST['text']);
            $link = trim($_POST['link']);

            $imgPath = '';
            if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imageName = basename($_FILES['img']['name']);
                $imgPath = $uploadDir . $imageName;
                move_uploaded_file($_FILES['img']['tmp_name'], $imgPath);
            }

            $this->slideManager->updateSlide($sid, $title, $text, $imgPath, $link);
            echo "<div class='alert alert-success'>Slide updated successfully!</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }

    public function fetchSlides() {
        return $this->slideManager->getAllSlides();
    }

    public function fetchSlideForEdit($sid) {
        return $this->slideManager->getSlideById($sid);
    }
}

// Initialize the application
try {
    $db = new Database('localhost', 'php_project', 'root', '');
    $pdo = $db->getConnection();

    $slideManager = new SlideManager($pdo);
    $controller = new SlideController($slideManager);

    $controller->handleRequest();

    $slides = $controller->fetchSlides();
    $editSlide = null;
    if (isset($_GET['edit'])) {
        $editSlide = $controller->fetchSlideForEdit($_GET['edit']);
    }
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>");
}
?>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">SlideShow</h1>

        <!-- Insert Slide Modal -->
        <div class="modal fade" id="insertModal" tabindex="-1" role="dialog" aria-labelledby="insertModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="insertModalLabel">Insert Slide</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add">
                            <div class="form-group">
                                <label for="title">Title:</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="text">Text:</label>
                                <textarea class="form-control" id="text" name="text" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="image">Image:</label>
                                <input type="file" class="form-control-file" id="image" name="img">
                            </div>
                            <div class="form-group">
                                <label for="link">Link:</label>
                                <input type="url" class="form-control" id="link" name="link" required>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">Add Slide</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Slide Modal -->
        <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateModalLabel">Update Slide</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" id="update_sid" name="sid">
                            <div class="form-group">
                                <label for="update_title">Title:</label>
                                <input type="text" class="form-control" id="update_title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="update_text">Text:</label>
                                <textarea class="form-control" id="update_text" name="text" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="update_image">Image:</label>
                                <input type="file" class="form-control-file" id="update_image" name="img">
                            </div>
                            <div class="form-group">
                                <label for="update_link">Link:</label>
                                <input type="url" class="form-control" id="update_link" name="link" required>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slides Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Image</th>
                        <th>Link</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($slides as $slide): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($slide['sid']); ?></td>
                        <td><?php echo htmlspecialchars($slide['title']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($slide['img']); ?>" alt="<?php echo htmlspecialchars($slide['title']); ?>" class="img-fluid" style="max-width: 100px;"></td>
                        <td><a href="<?php echo htmlspecialchars($slide['link']); ?>" target="_blank" class="btn btn-link">Visit Link</a></td>
                        <td class="actions">
                            <button class="btn btn-primary btn-sm edit_button" 
                                    data-sid="<?php echo $slide['sid']; ?>" 
                                    data-title="<?php echo htmlspecialchars($slide['title']); ?>" 
                                    data-text="<?php echo htmlspecialchars($slide['text']); ?>" 
                                    data-link="<?php echo htmlspecialchars($slide['link']); ?>">Update</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="sid" value="<?php echo $slide['sid']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button id="openInsertModal" class="btn btn-success mt-3">Add New Slide</button>
    </div>
    <script>
        $(document).ready(function () {
            $('#openInsertModal').click(function () {
                $('#insertModal').modal('show');
            });
            $('.edit_button').click(function () {
                const sid = $(this).data('sid');
                const title = $(this).data('title');
                const text = $(this).data('text');
                const link = $(this).data('link');

                $('#update_sid').val(sid);
                $('#update_title').val(title);
                $('#update_text').val(text);
                $('#update_link').val(link);

                $('#updateModal').modal('show');
            });
        });
    </script>
</body>
