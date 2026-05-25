<?php

use PHPUnit\Framework\TestCase;

class GalleryItemsRepositoryTest extends TestCase
{
    private function makeStmt(array $rows): PDOStatement
    {
        $calls = array_merge($rows, [false]);
        $stmt  = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('fetch')
             ->willReturnOnConsecutiveCalls(...$calls);
        return $stmt;
    }

    private function makePdo(PDOStatement $stmt): PDO
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);
        return $pdo;
    }

    public function testGetAllReturnsMappedItems(): void
    {
        $row  = ['id' => '1', 'category_id' => '2', 'name' => 'Bowl', 'material' => 'Clay',
                 'price' => '29.99', 'image_url' => 'bowl.jpg', 'description' => 'Nice', 'created_at' => '2024-01-01 00:00:00'];
        $stmt = $this->makeStmt([$row]);
        injectMockPdo($this->makePdo($stmt));

        $items = (new GalleryItemsRepository())->getAll();

        $this->assertCount(1, $items);
        $this->assertInstanceOf(GalleryItem::class, $items[0]);
        $this->assertSame('Bowl', $items[0]->getName());
        $this->assertSame(29.99, $items[0]->getPrice());
    }

    public function testGetAllReturnsEmptyArrayWhenNoRows(): void
    {
        $stmt = $this->makeStmt([]);
        injectMockPdo($this->makePdo($stmt));

        $items = (new GalleryItemsRepository())->getAll();

        $this->assertSame([], $items);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('fetch')->willReturn(false);
        injectMockPdo($this->makePdo($stmt));

        $result = (new GalleryItemsRepository())->getById(999);

        $this->assertNull($result);
    }

    public function testGetByIdReturnsMappedItem(): void
    {
        $row  = ['id' => '5', 'category_id' => null, 'name' => 'Mug', 'material' => null,
                 'price' => '15.00', 'image_url' => null, 'description' => null, 'created_at' => '2024-03-10 12:00:00'];
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('fetch')->willReturn($row);
        injectMockPdo($this->makePdo($stmt));

        $item = (new GalleryItemsRepository())->getById(5);

        $this->assertInstanceOf(GalleryItem::class, $item);
        $this->assertSame(5, $item->getId());
        $this->assertNull($item->getCategoryId());
        $this->assertSame('Mug', $item->getName());
    }
}
