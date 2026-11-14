<?php

declare(strict_types=1);

final class UpdateUserRequest
{
    public string $id;
    public string $name;
    public string $email;
    public ?string $password;
    public string $role;
    public string $status;

    /**
     * @param array<string, mixed> $input
     */
    public function __construct(array $input)
    {
        $this->id = isset($input['id']) ? trim((string) $input['id']) : '';
        $this->name = isset($input['name']) ? trim((string) $input['name']) : '';
        $this->email = isset($input['email']) ? trim((string) $input['email']) : '';
        $rawPassword = isset($input['password']) ? trim((string) $input['password']) : '';
        $this->password = $rawPassword !== '' ? $rawPassword : null;
        $this->role = isset($input['role']) && trim((string) $input['role']) !== ''
            ? trim((string) $input['role'])
            : 'MEMBER';
        $this->status = isset($input['status']) && trim((string) $input['status']) !== ''
            ? trim((string) $input['status'])
            : 'ACTIVE';
    }
}
