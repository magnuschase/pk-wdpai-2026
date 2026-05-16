<?php

class User
{
	private ?int $id;
	private string $username;
	private string $email;
	private string $password;
	private bool $isActive;
	private bool $isAdmin;

	public function __construct(
		string $username,
		string $email,
		string $password,
		?int $id = null,
		bool $isActive = true,
		bool $isAdmin = false
	) {
		$this->username = $username;
		$this->email = $email;
		$this->password = $password;
		$this->id = $id;
		$this->isActive = $isActive;
		$this->isAdmin = $isAdmin;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUsername(): string
	{
		return $this->username;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	public function isActive(): bool
	{
		return $this->isActive;
	}

	public function isAdmin(): bool
	{
		return $this->isAdmin;
	}
}
