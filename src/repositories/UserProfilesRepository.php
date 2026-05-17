<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/UserProfile.php';

class UserProfilesRepository extends Repository
{
    public function getByUserId(int $userId): ?UserProfile
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM user_profiles WHERE user_id = :user_id
        ');
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToUserProfile($row) : null;
    }

    public function create(int $userId, ?string $avatarUrl = null, ?string $bio = null, ?string $phone = null): void
    {
        $query = $this->database->connect()->prepare('
            INSERT INTO user_profiles (user_id, avatar_url, bio, phone)
            VALUES (:user_id, :avatar_url, :bio, :phone)
        ');
        $query->bindParam(':user_id',    $userId,    PDO::PARAM_INT);
        $query->bindParam(':avatar_url', $avatarUrl);
        $query->bindParam(':bio',        $bio);
        $query->bindParam(':phone',      $phone);
        $query->execute();
    }

    public function update(int $userId, ?string $avatarUrl, ?string $bio, ?string $phone): void
    {
        $query = $this->database->connect()->prepare('
            UPDATE user_profiles
            SET avatar_url = :avatar_url, bio = :bio, phone = :phone
            WHERE user_id = :user_id
        ');
        $query->bindParam(':user_id',    $userId,    PDO::PARAM_INT);
        $query->bindParam(':avatar_url', $avatarUrl);
        $query->bindParam(':bio',        $bio);
        $query->bindParam(':phone',      $phone);
        $query->execute();
    }

    private function mapRowToUserProfile(array $row): UserProfile
    {
        return new UserProfile(
            (int) $row['user_id'],
            $row['avatar_url'] ?? null,
            $row['bio']        ?? null,
            $row['phone']      ?? null
        );
    }
}
