<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGettersReturnConstructedValues(): void
    {
        $user = new User('alice', 'alice@example.com', 'hashed_pw', 7, true, false);

        $this->assertSame(7, $user->getId());
        $this->assertSame('alice', $user->getUsername());
        $this->assertSame('alice@example.com', $user->getEmail());
        $this->assertSame('hashed_pw', $user->getPassword());
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isAdmin());
    }

    public function testDefaultsForOptionalParameters(): void
    {
        $user = new User('bob', 'bob@example.com', 'pw');

        $this->assertNull($user->getId());
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isAdmin());
    }

    public function testAdminFlag(): void
    {
        $admin = new User('admin', 'admin@example.com', 'pw', 1, true, true);

        $this->assertTrue($admin->isAdmin());
    }
}
