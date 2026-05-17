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

	public function getUserById(int $id): ?User
	{
		$query = $this->database->connect()->prepare(
			"SELECT * FROM users WHERE id = :id"
		);
		$query->bindParam(':id', $id, PDO::PARAM_INT);
		$query->execute();

		$row = $query->fetch(PDO::FETCH_ASSOC);
		return $row ? $this->mapRowToUser($row) : null;
	}

	public function createUser(
		string $username,
		string $email,
		string $hashedPassword,
		bool $isAdmin = false
	) {
		$query = $this->database->connect()->prepare(
			"INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)"
		);
		$query->execute([
			$username,
			$email,
			$hashedPassword,
			$isAdmin ? 'true' : 'false'
		]);
	}

	public function updateUser(int $id, bool $isAdmin, bool $isActive): void
	{
		$query = $this->database->connect()->prepare(
			"UPDATE users SET is_admin = :is_admin, is_active = :is_active WHERE id = :id"
		);
		$query->bindParam(':id',        $id,       PDO::PARAM_INT);
		$query->bindParam(':is_admin',  $isAdmin,  PDO::PARAM_BOOL);
		$query->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
		$query->execute();
	}

	public function deleteUser(int $id): void
	{
		$query = $this->database->connect()->prepare(
			"DELETE FROM users WHERE id = :id"
		);
		$query->bindParam(':id', $id, PDO::PARAM_INT);
		$query->execute();
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
