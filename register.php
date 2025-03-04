<?php
session_start();

// Include the Database class
require_once 'config.php';

class UserRegistration {
    private $conn;

    public function __construct() {
        $db = new Database("localhost", "php_project", "root", ""); // Adjust credentials
        $this->conn = $db->getConnection();
    }

    public function register($user_name, $password) {
        if (empty($user_name) || empty($password)) {
            return "All fields are required.";
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if user_name already exists
            $query = "SELECT user_name FROM users WHERE user_name = :user_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return "Username already exists. Please choose a different user_name.";
            }

            // Insert new user
            $query = "INSERT INTO users (user_name, password) VALUES (:user_name, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_name', $user_name);
            $stmt->bindParam(':password', $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['user_name'] = $user_name;
                return true;
            } else {
                return "Registration failed.";
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name']; // Match the form field name
    $password = $_POST['password'];

    $user = new UserRegistration();
    $result = $user->register($user_name, $password);

    if ($result === true) {
        header("Location: index.php");
        exit();
    } else {
        echo $result;
    }
} else {
    echo "Invalid request.";
}
?>