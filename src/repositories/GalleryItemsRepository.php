<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/GalleryItem.php';

class GalleryItemsRepository extends Repository
{
    public function getAll(?int $categoryId = null): array
    {
        if ($categoryId !== null) {
            $query = $this->database->connect()->prepare('
                SELECT * FROM gallery_items
                WHERE category_id = :category_id
                ORDER BY created_at DESC
            ');
            $query->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        } else {
            $query = $this->database->connect()->prepare('
                SELECT * FROM gallery_items ORDER BY created_at DESC
            ');
        }

        $query->execute();

        $items = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->mapRowToGalleryItem($row);
        }

        return $items;
    }

    public function getById(int $id): ?GalleryItem
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM gallery_items WHERE id = :id
        ');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToGalleryItem($row) : null;
    }

    public function getFavouritesByUserId(int $userId): array
    {
        $query = $this->database->connect()->prepare('
            SELECT gi.*
            FROM gallery_items gi
            JOIN user_favorites uf ON gi.id = uf.item_id
            WHERE uf.user_id = :user_id
            ORDER BY uf.created_at DESC
        ');
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        $items = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $this->mapRowToGalleryItem($row);
        }

        return $items;
    }

    public function isFavouritedBy(int $userId, int $itemId): bool
    {
        $query = $this->database->connect()->prepare('
            SELECT 1 FROM user_favorites
            WHERE user_id = :user_id AND item_id = :item_id
        ');
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $query->execute();

        return (bool) $query->fetch();
    }

    public function addFavourite(int $userId, int $itemId): void
    {
        $query = $this->database->connect()->prepare('
            INSERT INTO user_favorites (user_id, item_id)
            VALUES (:user_id, :item_id)
            ON CONFLICT DO NOTHING
        ');
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $query->execute();
    }

    public function removeFavourite(int $userId, int $itemId): void
    {
        $query = $this->database->connect()->prepare('
            DELETE FROM user_favorites
            WHERE user_id = :user_id AND item_id = :item_id
        ');
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $query->execute();
    }

    private function mapRowToGalleryItem(array $row): GalleryItem
    {
        return new GalleryItem(
            (int) $row['id'],
            isset($row['category_id']) ? (int) $row['category_id'] : null,
            $row['name'],
            $row['material'] ?? null,
            (float) $row['price'],
            $row['image_url'] ?? null,
            $row['description'] ?? null,
            $row['created_at']
        );
    }
}
