<?php

class UserProfile
{
    private int $userId;
    private ?string $avatarUrl;
    private ?string $bio;
    private ?string $phone;

    public function __construct(
        int $userId,
        ?string $avatarUrl = null,
        ?string $bio = null,
        ?string $phone = null
    ) {
        $this->userId    = $userId;
        $this->avatarUrl = $avatarUrl;
        $this->bio       = $bio;
        $this->phone     = $phone;
    }

    public function getUserId(): int       { return $this->userId; }
    public function getAvatarUrl(): ?string { return $this->avatarUrl; }
    public function getBio(): ?string       { return $this->bio; }
    public function getPhone(): ?string     { return $this->phone; }
}
