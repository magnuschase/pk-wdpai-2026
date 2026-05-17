<?php

class GalleryItem
{
    private int $id;
    private ?int $categoryId;
    private string $name;
    private ?string $material;
    private float $price;
    private ?string $imageUrl;
    private ?string $description;
    private string $createdAt;

    public function __construct(
        int $id,
        ?int $categoryId,
        string $name,
        ?string $material,
        float $price,
        ?string $imageUrl,
        ?string $description,
        string $createdAt
    ) {
        $this->id          = $id;
        $this->categoryId  = $categoryId;
        $this->name        = $name;
        $this->material    = $material;
        $this->price       = $price;
        $this->imageUrl    = $imageUrl;
        $this->description = $description;
        $this->createdAt   = $createdAt;
    }

    public function getId(): int           { return $this->id; }
    public function getCategoryId(): ?int  { return $this->categoryId; }
    public function getName(): string      { return $this->name; }
    public function getMaterial(): ?string  { return $this->material; }
    public function getPrice(): float      { return $this->price; }
    public function getImageUrl(): ?string  { return $this->imageUrl; }
    public function getDescription(): ?string { return $this->description; }
    public function getCreatedAt(): string { return $this->createdAt; }
}
