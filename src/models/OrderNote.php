<?php

class OrderNote
{
    private int $id;
    private int $orderId;
    private ?int $authorId;
    private string $noteType;
    private string $content;
    private string $createdAt;

    public function __construct(
        int $id,
        int $orderId,
        ?int $authorId,
        string $noteType,
        string $content,
        string $createdAt
    ) {
        $this->id        = $id;
        $this->orderId   = $orderId;
        $this->authorId  = $authorId;
        $this->noteType  = $noteType;
        $this->content   = $content;
        $this->createdAt = $createdAt;
    }

    public function getId(): int           { return $this->id; }
    public function getOrderId(): int      { return $this->orderId; }
    public function getAuthorId(): ?int    { return $this->authorId; }
    public function getNoteType(): string  { return $this->noteType; }
    public function getContent(): string   { return $this->content; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function isInternal(): bool { return $this->noteType === 'internal'; }
}
