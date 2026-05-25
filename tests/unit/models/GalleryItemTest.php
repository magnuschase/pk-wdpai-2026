<?php

use PHPUnit\Framework\TestCase;

class GalleryItemTest extends TestCase
{
    public function testGettersReturnConstructedValues(): void
    {
        $item = new GalleryItem(1, 3, 'Bowl', 'Stoneware', 49.99, 'bowl.jpg', 'A nice bowl', '2024-01-15 10:00:00');

        $this->assertSame(1, $item->getId());
        $this->assertSame(3, $item->getCategoryId());
        $this->assertSame('Bowl', $item->getName());
        $this->assertSame('Stoneware', $item->getMaterial());
        $this->assertSame(49.99, $item->getPrice());
        $this->assertSame('bowl.jpg', $item->getImageUrl());
        $this->assertSame('A nice bowl', $item->getDescription());
        $this->assertSame('2024-01-15 10:00:00', $item->getCreatedAt());
    }

    public function testNullableFieldsAcceptNull(): void
    {
        $item = new GalleryItem(2, null, 'Vase', null, 0.0, null, null, '2024-06-01 00:00:00');

        $this->assertNull($item->getCategoryId());
        $this->assertNull($item->getMaterial());
        $this->assertNull($item->getImageUrl());
        $this->assertNull($item->getDescription());
    }
}
