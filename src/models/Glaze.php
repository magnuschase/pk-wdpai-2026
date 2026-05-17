<?php

class Glaze
{
    private int $id;
    private string $name;
    private ?string $colorHex;

    public function __construct(int $id, string $name, ?string $colorHex = null)
    {
        $this->id       = $id;
        $this->name     = $name;
        $this->colorHex = $colorHex;
    }

    public function getId(): int          { return $this->id; }
    public function getName(): string      { return $this->name; }
    public function getColorHex(): ?string { return $this->colorHex; }
}
