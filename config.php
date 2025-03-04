<?php
class Database {
    private $pdo;
    private $host;
    private $dbname;
    private $username;
    private $password;

    /**
     * Constructor to initialize the database connection
     *
     * @param string 
     * @param string 
     * @param string 
     * @param string 
     */
    public function __construct($host, $dbname, $username, $password) {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;

        $this->connect();
    }

    /**
     * Establishes the PDO connection to the database
     *
     * @return void
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Returns the PDO connection instance
     *
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Closes the PDO connection (optional, PDO closes automatically at script end)
     *
     * @return void
     */
    public function closeConnection() {
        $this->pdo = null;
    }
}
?>