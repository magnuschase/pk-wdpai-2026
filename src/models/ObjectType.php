<?php

class ObjectType
{
    private int $id;
    private string $name;
    private ?string $icon;

    public function __construct(int $id, string $name, ?string $icon = null)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->icon = $icon;
    }

    public function getId(): int      { return $this->id; }
    public function getName(): string  { return $this->name; }
    public function getIcon(): ?string { return $this->icon; }
}
