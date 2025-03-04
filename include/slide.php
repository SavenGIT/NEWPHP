<?php
// Database Class
require_once "config.php";
class SlideManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch all slides
    public function getAllSlides() {
        $stmt = $this->pdo->query("SELECT * FROM tb_slide");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Initialize the application
try {
    $db = new Database('localhost', 'php_project', 'root', '');
    $pdo = $db->getConnection();
    $slideManager = new SlideManager($pdo);

    // Fetch all slides for display
    $slides = $slideManager->getAllSlides();
} catch (Exception $e) {
    die("<div class='error'>Error: " . $e->getMessage() . "</div>");
}
?>

<?php if (!empty($slides)): ?>
    <?php foreach ($slides as $index => $slide): ?>
        <?php
        // Construct the full image path
        $imagePath = str_replace('uploads/','',$slide['img']);
        ?>
        <div class="main_slider" style="background-image:url(../admin/uploads/<?php echo ($imagePath); ?>)">
            <div class="container fill_height">
                <div class="row align-items-center fill_height
                ">
                    <div class="col">
                        <div class="main_slider_content">
                            <img src="" alt="">
                            <h6><?php echo htmlspecialchars($slide['title']); ?></h6>
                            <h1><?php echo htmlspecialchars($slide['text']); ?></h1>
                            <div class="red_button shop_now_button">
                                <a href="<?php echo htmlspecialchars($slide['link']); ?>" target="_blank">Shop Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No slides available.</p>
<?php endif; ?>