<?php

declare(strict_types=1);

final class UserResponse
{
    public string $id;
    public string $name;
    public string $email;
    public string $role;
    public string $status;

    public function __construct(
        string $id,
        string $name,
        string $email,
        string $role,
        string $status
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->status = $status;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
        );
    }
}
