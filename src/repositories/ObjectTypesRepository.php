<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/ObjectType.php';

class ObjectTypesRepository extends Repository
{
    public function getAll(): array
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM object_types ORDER BY id ASC
        ');
        $query->execute();

        $types = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $types[] = $this->mapRowToObjectType($row);
        }

        return $types;
    }

    public function getById(int $id): ?ObjectType
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM object_types WHERE id = :id
        ');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToObjectType($row) : null;
    }

    private function mapRowToObjectType(array $row): ObjectType
    {
        return new ObjectType(
            (int) $row['id'],
            $row['name'],
            $row['icon'] ?? null
        );
    }
}
