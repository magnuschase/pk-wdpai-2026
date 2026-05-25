<?php

require_once __DIR__ . '/EnvLoader.php';
EnvLoader::load(__DIR__ . '/.env');

class Database
{
	private static ?Database $instance = null;
	private ?PDO $conn = null;
	private string $username;
	private string $password;
	private string $host;
	private string $database;
	private int $port;

	private function __construct()
	{
		$this->username = $_ENV['DB_USERNAME'] ?? '';
		$this->password = $_ENV['DB_PASSWORD'] ?? '';
		$this->host = $_ENV['DB_HOST'] ?? 'db';
		$this->database = $_ENV['DB_DATABASE'] ?? 'db';
		$this->port = (int) ($_ENV['DB_PORT'] ?? 5432);
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
				"pgsql:host=$this->host;port=$this->port;dbname=$this->database",
				$this->username,
				$this->password,
				["sslmode" => "prefer"]
			);

			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->conn;
		} catch (PDOException $e) {
			http_response_code(500);
			include 'public/views/500.html';
			exit();
		}
	}

	public function disconnect(): void
	{
		$this->conn = null;
	}

	private function __clone(): void {}
}
