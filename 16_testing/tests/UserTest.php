<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCreateUser()
    {
        $user = new User('Budi Santoso', 'budi@test.com');

        $this->assertEquals('Budi Santoso', $user->getName());
        $this->assertEquals('budi@test.com', $user->getEmail());
        $this->assertEquals('user', $user->getRole());
        $this->assertFalse($user->isAdmin());
        $this->assertIsInt($user->getId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testCreateAdminUser()
    {
        $user = new User('Ani', 'ani@test.com', 'admin');
        $this->assertTrue($user->isAdmin());
        $this->assertEquals('admin', $user->getRole());
    }

    public function testInvalidEmail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email');
        new User('Test', 'not-an-email');
    }

    public function testInvalidRole()
    {
        $this->expectException(\InvalidArgumentException::class);
        new User('Test', 'test@test.com', 'superadmin');
    }

    public function testUpdateEmail()
    {
        $user = new User('Budi', 'budi@test.com');
        $user->updateEmail('baru@test.com');
        $this->assertEquals('baru@test.com', $user->getEmail());
    }

    public function testUpdateEmailInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $user = new User('Budi', 'budi@test.com');
        $user->updateEmail('invalid');
    }

    public function testPromoteDemote()
    {
        $user = new User('Budi', 'budi@test.com');
        $this->assertFalse($user->isAdmin());

        $user->promote();
        $this->assertTrue($user->isAdmin());

        $user->demote();
        $this->assertFalse($user->isAdmin());
        $this->assertEquals('user', $user->getRole());
    }

    public function testToArray()
    {
        $user = new User('Budi', 'budi@test.com');
        $data = $user->toArray();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('role', $data);
        $this->assertEquals('Budi', $data['name']);
    }

    public function testModeratorRole()
    {
        $user = new User('Citra', 'citra@test.com', 'moderator');
        $this->assertEquals('moderator', $user->getRole());
        $this->assertFalse($user->isAdmin());
    }
}
