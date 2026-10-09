<?php

declare(strict_types=1);

final class UpdateUserCommand
{
    private string $id;
    private string $name;
    private string $email;
    private ?string $password;
    private string $role;
    private string $status;

    public function __construct(
        string $id,
        string $name,
        string $email,
        ?string $password,
        string $role,
        string $status
    ) {
        $this->id = trim($id);
        $this->name = trim($name);
        $this->email = trim($email);
        $this->password = $password !== null && trim($password) !== '' ? trim($password) : null;
        $this->role = trim($role);
        $this->status = trim($status);
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatus(): string
    {
        return $this->status;
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

    public function password(): ?string
    {
        return $this->password;
    }

    public function role(): string
    {
        return $this->role;
    }

    public function status(): string
    {
        return $this->status;
    }
}
