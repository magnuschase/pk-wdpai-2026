<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderImage.php';
require_once __DIR__ . '/../models/OrderNote.php';

class OrdersRepository extends Repository
{
    // -------------------------------------------------------------------------
    // Orders
    // -------------------------------------------------------------------------

    public function getAll(): array
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM orders ORDER BY created_at DESC
        ');
        $query->execute();

        $orders = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $this->mapRowToOrder($row);
        }

        return $orders;
    }

    public function getById(int $id): ?Order
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM orders WHERE id = :id
        ');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToOrder($row) : null;
    }

    public function getByUserId(int $userId): array
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC
        ');
        $query->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $query->execute();

        $orders = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $this->mapRowToOrder($row);
        }

        return $orders;
    }

    public function create(
        int $userId,
        ?int $objectTypeId,
        ?int $glazeId,
        ?string $sizeType,
        ?string $customDimensions,
        ?string $specialRequests,
        ?float $budgetMin,
        ?float $budgetMax
    ): int {
        $pdo = $this->database->connect();

        $pdo->beginTransaction();
        try {
            $query = $pdo->prepare('
                INSERT INTO orders
                    (user_id, object_type_id, glaze_id, size_type,
                     custom_dimensions, special_requests, budget_min, budget_max)
                VALUES
                    (:user_id, :object_type_id, :glaze_id, :size_type,
                     :custom_dimensions, :special_requests, :budget_min, :budget_max)
                RETURNING id
            ');

            $query->bindParam(':user_id',           $userId,           PDO::PARAM_INT);
            $query->bindParam(':object_type_id',    $objectTypeId,     PDO::PARAM_INT);
            $query->bindParam(':glaze_id',          $glazeId,          PDO::PARAM_INT);
            $query->bindParam(':size_type',         $sizeType);
            $query->bindParam(':custom_dimensions', $customDimensions);
            $query->bindParam(':special_requests',  $specialRequests);
            $query->bindParam(':budget_min',        $budgetMin);
            $query->bindParam(':budget_max',        $budgetMax);
            $query->execute();

            $newId = (int) $query->fetchColumn();
            $pdo->commit();

            return $newId;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status, ?float $priceAdjustment = null): void
    {
        $query = $this->database->connect()->prepare('
            UPDATE orders
            SET status = :status, price_adjustment = :price_adjustment
            WHERE id = :id
        ');
        $query->bindParam(':id',               $id,              PDO::PARAM_INT);
        $query->bindParam(':status',           $status);
        $query->bindParam(':price_adjustment', $priceAdjustment);
        $query->execute();
    }

    // -------------------------------------------------------------------------
    // Order images
    // -------------------------------------------------------------------------

    public function addImage(int $orderId, string $imageUrl): void
    {
        $query = $this->database->connect()->prepare('
            INSERT INTO order_images (order_id, image_url) VALUES (:order_id, :image_url)
        ');
        $query->bindParam(':order_id',  $orderId,  PDO::PARAM_INT);
        $query->bindParam(':image_url', $imageUrl);
        $query->execute();
    }

    public function getImagesForOrder(int $orderId): array
    {
        $query = $this->database->connect()->prepare('
            SELECT * FROM order_images WHERE order_id = :order_id ORDER BY uploaded_at ASC
        ');
        $query->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $query->execute();

        $images = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $images[] = new OrderImage(
                (int) $row['id'],
                (int) $row['order_id'],
                $row['image_url'],
                $row['uploaded_at']
            );
        }

        return $images;
    }

    // -------------------------------------------------------------------------
    // Order notes
    // -------------------------------------------------------------------------

    public function addNote(int $orderId, int $authorId, string $noteType, string $content): void
    {
        $query = $this->database->connect()->prepare('
            INSERT INTO order_notes (order_id, author_id, note_type, content)
            VALUES (:order_id, :author_id, :note_type, :content)
        ');
        $query->bindParam(':order_id',  $orderId,  PDO::PARAM_INT);
        $query->bindParam(':author_id', $authorId, PDO::PARAM_INT);
        $query->bindParam(':note_type', $noteType);
        $query->bindParam(':content',   $content);
        $query->execute();
    }

    public function getNotesForOrder(int $orderId, ?string $noteType = null): array
    {
        if ($noteType !== null) {
            $query = $this->database->connect()->prepare('
                SELECT * FROM order_notes
                WHERE order_id = :order_id AND note_type = :note_type
                ORDER BY created_at ASC
            ');
            $query->bindParam(':order_id',  $orderId,  PDO::PARAM_INT);
            $query->bindParam(':note_type', $noteType);
        } else {
            $query = $this->database->connect()->prepare('
                SELECT * FROM order_notes
                WHERE order_id = :order_id
                ORDER BY created_at ASC
            ');
            $query->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        }

        $query->execute();

        $notes = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $notes[] = new OrderNote(
                (int) $row['id'],
                (int) $row['order_id'],
                isset($row['author_id']) ? (int) $row['author_id'] : null,
                $row['note_type'],
                $row['content'],
                $row['created_at']
            );
        }

        return $notes;
    }

    // -------------------------------------------------------------------------
    // Mapping
    // -------------------------------------------------------------------------

    private function mapRowToOrder(array $row): Order
    {
        return new Order(
            (int) $row['id'],
            (int) $row['user_id'],
            isset($row['object_type_id']) ? (int) $row['object_type_id'] : null,
            isset($row['glaze_id'])       ? (int) $row['glaze_id']       : null,
            $row['size_type']         ?? null,
            $row['custom_dimensions'] ?? null,
            $row['special_requests']  ?? null,
            isset($row['budget_min'])  ? (float) $row['budget_min']  : null,
            isset($row['budget_max'])  ? (float) $row['budget_max']  : null,
            $row['status'],
            isset($row['price_adjustment']) ? (float) $row['price_adjustment'] : null,
            $row['created_at'],
            $row['updated_at']
        );
    }
}
