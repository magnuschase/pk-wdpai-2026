<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/Glaze.php';

class GlazesRepository extends Repository
{
    public function getAll(): array
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM glazes ORDER BY id ASC
        ');
        $query->execute();

        $glazes = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $glazes[] = $this->mapRowToGlaze($row);
        }

        return $glazes;
    }

    public function getById(int $id): ?Glaze
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM glazes WHERE id = :id
        ');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToGlaze($row) : null;
    }

    private function mapRowToGlaze(array $row): Glaze
    {
        return new Glaze(
            (int) $row['id'],
            $row['name'],
            $row['color_hex'] ?? null
        );
    }
}
