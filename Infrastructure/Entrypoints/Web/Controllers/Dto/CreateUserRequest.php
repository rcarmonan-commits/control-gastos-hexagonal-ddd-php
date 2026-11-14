<?php

declare(strict_types=1);

final class CreateUserRequest
{
    public string $id;
    public string $name;
    public string $email;
    public string $password;
    public string $role;

    /**
     * @param array<string, mixed> $input
     */
    public function __construct(array $input)
    {
        $this->id = isset($input['id']) ? trim((string) $input['id']) : '';
        $this->name = isset($input['name']) ? trim((string) $input['name']) : '';
        $this->email = isset($input['email']) ? trim((string) $input['email']) : '';
        $this->password = isset($input['password']) ? trim((string) $input['password']) : '';
        $this->role = isset($input['role']) && trim((string) $input['role']) !== ''
            ? trim((string) $input['role'])
            : 'MEMBER';
    }
}
