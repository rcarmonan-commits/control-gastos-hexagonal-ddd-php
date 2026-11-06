<?php

declare(strict_types=1);

final class UserPersistenceDto
{
    public string $id;
    public string $name;
    public string $email;
    public string $password;
    public string $role;
    public string $status;
    public int $roleId;

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
}
