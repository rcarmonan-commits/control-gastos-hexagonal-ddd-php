<?php

declare(strict_types=1);

final class UserEntity
{
    private string $id;
    private string $name;
    private string $email;
    private string $password;
    private string $role;
    private string $status;
    private int $roleId;

    public function __construct(
        string $id,
        string $name,
        string $email,
        string $password,
        string $role,
        string $status,
        int $roleId
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->status = $status;
        $this->roleId = $roleId;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            (string) ($row['id'] ?? ''),
            (string) ($row['name'] ?? ''),
            (string) ($row['email'] ?? ''),
            (string) ($row['password'] ?? ''),
            (string) ($row['role'] ?? 'MEMBER'),
            (string) ($row['status'] ?? 'ACTIVE'),
            isset($row['role_id']) ? (int) $row['role_id'] : 2
        );
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }
}
