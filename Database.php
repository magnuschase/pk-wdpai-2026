<?php
// TODO: load .env instead of config.php
require_once "config.php";

class Database
{
	private static ?Database $instance = null;
	private ?PDO $conn = null;
	private string $username;
	private string $password;
	private string $host;
	private string $database;

	private function __construct()
	{
		$this->username = USERNAME;
		$this->password = PASSWORD;
		$this->host = HOST;
		$this->database = DATABASE;
	}

	public static function getInstance(): Database
	{
		if (self::$instance === null) {
			self::$instance = new self();
			register_shutdown_function([self::$instance, 'disconnect']);
		}
		return self::$instance;
	}

	public function connect(): PDO
	{
		if ($this->conn !== null) {
			return $this->conn;
		}

		try {
			$this->conn = new PDO(
				"pgsql:host=$this->host;port=5432;dbname=$this->database",
				$this->username,
				$this->password,
				["sslmode" => "prefer"]
			);

			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->conn;
		} catch (PDOException $e) {
			// TODO: change to error page e.g. 404 not found etc.
			die("Connection failed: " . $e->getMessage());
		}
	}

	public function disconnect(): void
	{
		$this->conn = null;
	}

	private function __clone(): void {}
}
