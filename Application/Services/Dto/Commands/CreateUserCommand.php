<?php

declare(strict_types=1);

final class CreateUserCommand
{
    private string $id;
    private string $name;
    private string $email;
    private string $password;
    private string $role;

    public function __construct(
        string $id,
        string $name,
        string $email,
        string $password,
        string $role = 'MEMBER'
    ) {
        $this->id = trim($id);
        $this->name = trim($name);
        $this->email = trim($email);
        $this->password = trim($password);
        $this->role = trim($role) !== '' ? trim($role) : 'MEMBER';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function role(): string
    {
        return $this->role;
    }
}
