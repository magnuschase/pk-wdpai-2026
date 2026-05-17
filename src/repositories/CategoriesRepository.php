<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/Category.php';

class CategoriesRepository extends Repository
{
    public function getAll(): array
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM categories ORDER BY name ASC
        ');
        $query->execute();

        $categories = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $this->mapRowToCategory($row);
        }

        return $categories;
    }

    public function getBySlug(string $slug): ?Category
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM categories WHERE slug = :slug
        ');
        $query->bindParam(':slug', $slug);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToCategory($row) : null;
    }

    private function mapRowToCategory(array $row): Category
    {
        return new Category(
            (int) $row['id'],
            $row['name'],
            $row['slug']
        );
    }
}
