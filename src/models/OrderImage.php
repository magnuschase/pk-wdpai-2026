<?php

class OrderImage
{
    private int $id;
    private int $orderId;
    private string $imageUrl;
    private string $uploadedAt;

    public function __construct(int $id, int $orderId, string $imageUrl, string $uploadedAt)
    {
        $this->id         = $id;
        $this->orderId    = $orderId;
        $this->imageUrl   = $imageUrl;
        $this->uploadedAt = $uploadedAt;
    }

    public function getId(): int          { return $this->id; }
    public function getOrderId(): int     { return $this->orderId; }
    public function getImageUrl(): string { return $this->imageUrl; }
    public function getUploadedAt(): string { return $this->uploadedAt; }
}
