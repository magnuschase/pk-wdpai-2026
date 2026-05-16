<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/User.php';

class UsersRepository extends Repository
{

	public function getUsers(): array
	{
		$query = $this->database->connect()->prepare(
			"
			SELECT * FROM users;
			"
		);
		$query->execute();

		$users = [];
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$users[] = $this->mapRowToUser($row);
		}

		return $users;
	}

	public function getUserByEmail(string $email): ?User
	{
		$query = $this->database->connect()->prepare(
			"
			SELECT * FROM users WHERE email = :email
			"
		);
		$query->bindParam(':email', $email);
		$query->execute();

		$row = $query->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			return null;
		}

		return $this->mapRowToUser($row);
	}

	public function createUser(
		string $username,
		string $email,
		string $hashedPassword,
	) {
		$query = $this->database->connect()->prepare(
			"
			INSERT INTO users (username, email, password)
			VALUES (?, ?, ?);
			"
		);
		$query->execute([
			$username,
			$email,
			$hashedPassword
		]);
	}

	private function mapRowToUser(array $row): User
	{
		return new User(
			$row['username'],
			$row['email'],
			$row['password'],
			(int) $row['id'],
			in_array($row['is_active'] ?? true, [true, 1, '1', 't'], true),
			in_array($row['is_admin'] ?? false, [true, 1, '1', 't'], true)
		);
	}
}
