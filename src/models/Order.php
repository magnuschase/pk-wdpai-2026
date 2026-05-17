<?php

class Order
{
    private int $id;
    private int $userId;
    private ?int $objectTypeId;
    private ?int $glazeId;
    private ?string $sizeType;
    private ?string $customDimensions;
    private ?string $specialRequests;
    private ?float $budgetMin;
    private ?float $budgetMax;
    private string $status;
    private ?float $priceAdjustment;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        int $id,
        int $userId,
        ?int $objectTypeId,
        ?int $glazeId,
        ?string $sizeType,
        ?string $customDimensions,
        ?string $specialRequests,
        ?float $budgetMin,
        ?float $budgetMax,
        string $status,
        ?float $priceAdjustment,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id               = $id;
        $this->userId           = $userId;
        $this->objectTypeId     = $objectTypeId;
        $this->glazeId          = $glazeId;
        $this->sizeType         = $sizeType;
        $this->customDimensions = $customDimensions;
        $this->specialRequests  = $specialRequests;
        $this->budgetMin        = $budgetMin;
        $this->budgetMax        = $budgetMax;
        $this->status           = $status;
        $this->priceAdjustment  = $priceAdjustment;
        $this->createdAt        = $createdAt;
        $this->updatedAt        = $updatedAt;
    }

    public function getId(): int                    { return $this->id; }
    public function getUserId(): int                { return $this->userId; }
    public function getObjectTypeId(): ?int         { return $this->objectTypeId; }
    public function getGlazeId(): ?int              { return $this->glazeId; }
    public function getSizeType(): ?string          { return $this->sizeType; }
    public function getCustomDimensions(): ?string  { return $this->customDimensions; }
    public function getSpecialRequests(): ?string   { return $this->specialRequests; }
    public function getBudgetMin(): ?float          { return $this->budgetMin; }
    public function getBudgetMax(): ?float          { return $this->budgetMax; }
    public function getStatus(): string             { return $this->status; }
    public function getPriceAdjustment(): ?float    { return $this->priceAdjustment; }
    public function getCreatedAt(): string          { return $this->createdAt; }
    public function getUpdatedAt(): string          { return $this->updatedAt; }
}
